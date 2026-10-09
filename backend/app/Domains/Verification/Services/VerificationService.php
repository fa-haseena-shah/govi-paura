<?php

namespace App\Domains\Verification\Services;

use App\Domains\Verification\DTOs\VerificationDocumentData;
use App\Domains\Verification\DTOs\VerificationOverviewData;
use App\Domains\Verification\DTOs\VerificationRequestData;
use App\Domains\Verification\Models\VerificationDocument;
use App\Enums\DocumentType;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Enums\VerificationStatus;
use App\Models\User;
use BackedEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class VerificationService implements VerificationServiceInterface
{
    // documents sit on the private disk (storage/app/private), never behind a public URL
    private const DISK = 'local';

    /* ---------------- 2.2 track status (farmer / rider) ---------------- */

    public function overview(User $user): VerificationOverviewData
    {
        $this->assertApplicant($user);

        $documents = VerificationDocument::where('user_id', $user->id)->orderBy('id')->get();
        $rejected = $documents->first(fn (VerificationDocument $d) => $d->status === VerificationStatus::Rejected);

        return new VerificationOverviewData(
            accountStatus: $user->status->value,
            canUpload: $this->canUpload($user),
            required: $this->describe($this->requiredTypes($user)),
            optional: $this->describe($this->optionalTypes($user)),
            documents: $documents->map(fn (VerificationDocument $d) => VerificationDocumentData::fromModel($d))->all(),
            missing: $this->missingTypes($user, $documents),
            rejectionReason: $rejected?->rejection_reason,
        );
    }

    /* ---------------- 2.1 submit documents ---------------- */

    public function submitDocument(User $user, DocumentType $type, UploadedFile $file): VerificationDocumentData
    {
        $this->assertApplicant($user);

        if (!in_array($type, [...$this->requiredTypes($user), ...$this->optionalTypes($user)], true)) {
            throw new InvalidArgumentException('This document is not needed for your account type.');
        }

        if (!$this->canUpload($user)) {
            throw new RuntimeException('Documents can no longer be changed for this account.');
        }

        $path = $file->store("verification/{$user->id}", self::DISK);

        if ($path === false) {
            throw new RuntimeException('The file could not be saved. Please try again.');
        }

        try {
            [$document, $replacedPaths] = DB::transaction(function () use ($user, $type, $file, $path) {
                // one document per type: uploading again replaces the earlier one
                $previous = VerificationDocument::where('user_id', $user->id)->where('type', $type->value)->get();
                VerificationDocument::whereIn('id', $previous->pluck('id'))->delete();

                $document = VerificationDocument::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'url' => $path,
                    'original_name' => Str::limit($file->getClientOriginalName(), 200, ''),
                    'status' => VerificationStatus::Pending,
                ]);

                // a rejected applicant goes back into the review queue once every required document is replaced
                if ($user->status === UserStatus::Rejected) {
                    $documents = VerificationDocument::where('user_id', $user->id)->get();

                    if ($this->missingTypes($user, $documents) === []) {
                        $user->update(['status' => UserStatus::PendingVerification]);
                    }
                }

                return [$document, $previous->pluck('url')->all()];
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete($path);
            throw $e;
        }

        if ($replacedPaths !== []) {
            Storage::disk(self::DISK)->delete($replacedPaths);
        }

        return VerificationDocumentData::fromModel($document);
    }

    public function documentPath(User $viewer, VerificationDocument $document): string
    {
        if (!$viewer->isAdmin() && $document->user_id !== $viewer->id) {
            throw new RuntimeException('You do not have permission to view this document.');
        }

        if (!Storage::disk(self::DISK)->exists($document->url)) {
            throw new InvalidArgumentException('The file is no longer available.');
        }

        return $document->url;
    }

    /* ---------------- 2.3 administrator review ---------------- */

    public function reviewQueue(string $filter, int $page, int $perPage): array
    {
        $query = $this->applyFilter($this->applicants(), $filter);
        $total = (clone $query)->count();

        $users = $query
            ->with(['farmerProfile', 'riderProfile.vehicles'])
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get();

        $documents = VerificationDocument::whereIn('user_id', $users->pluck('id'))
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');

        $counts = [];
        foreach (['pending', 'rejected', 'approved'] as $name) {
            $counts[$name] = $this->applyFilter($this->applicants(), $name)->count();
        }

        return [
            'data' => $users->map(fn (User $u) => $this->toRequestData($u, $documents->get($u->id, collect())))->all(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'counts' => $counts,
        ];
    }

    public function reviewDetail(int $userId): VerificationRequestData
    {
        return $this->loadRequest($this->findApplicant($userId));
    }

    public function approve(User $admin, int $userId): VerificationRequestData
    {
        $applicant = $this->findApplicant($userId);
        $this->assertAwaitingReview($applicant);

        $documents = VerificationDocument::where('user_id', $applicant->id)->get();
        $missing = $this->missingTypes($applicant, $documents);

        if ($missing !== []) {
            $labels = array_map(fn (string $type) => DocumentType::from($type)->label(), $missing);
            throw new RuntimeException('Cannot approve yet. Not submitted: ' . implode(', ', $labels) . '.');
        }

        DB::transaction(function () use ($admin, $applicant) {
            VerificationDocument::where('user_id', $applicant->id)
                ->where('status', VerificationStatus::Pending->value)
                ->update([
                    'status' => VerificationStatus::Approved->value,
                    'admin_id' => $admin->id,
                    'reviewed_at' => now(),
                    'rejection_reason' => null,
                ]);

            $applicant->update(['status' => UserStatus::Active]);
        });

        return $this->loadRequest($applicant->fresh());
    }

    public function reject(User $admin, int $userId, string $reason): VerificationRequestData
    {
        $applicant = $this->findApplicant($userId);
        $this->assertAwaitingReview($applicant);

        DB::transaction(function () use ($admin, $applicant, $reason) {
            VerificationDocument::where('user_id', $applicant->id)
                ->where('status', VerificationStatus::Pending->value)
                ->update([
                    'status' => VerificationStatus::Rejected->value,
                    'admin_id' => $admin->id,
                    'reviewed_at' => now(),
                    'rejection_reason' => trim($reason),
                ]);

            $applicant->update(['status' => UserStatus::Rejected]);
        });

        return $this->loadRequest($applicant->fresh());
    }

    /* ---------------- rules ---------------- */

    /** @return DocumentType[] */
    private function requiredTypes(User $user): array
    {
        return match ($user->role) {
            UserType::Farmer => [DocumentType::Nic],
            UserType::Rider => [DocumentType::Nic, DocumentType::VehicleRegistration],
            default => [],
        };
    }

    /** @return DocumentType[] */
    private function optionalTypes(User $user): array
    {
        return $user->role === UserType::Rider ? [DocumentType::DrivingLicense] : [];
    }

    private function canUpload(User $user): bool
    {
        return in_array($user->status, [UserStatus::PendingVerification, UserStatus::Rejected], true);
    }

    /** Required types with no document that is still pending or approved. @return string[] */
    private function missingTypes(User $user, Collection $documents): array
    {
        $have = $documents
            ->filter(fn (VerificationDocument $d) => $d->status !== VerificationStatus::Rejected)
            ->map(fn (VerificationDocument $d) => $d->type->value)
            ->all();

        return collect($this->requiredTypes($user))
            ->map(fn (DocumentType $t) => $t->value)
            ->reject(fn (string $value) => in_array($value, $have, true))
            ->values()
            ->all();
    }

    private function assertApplicant(User $user): void
    {
        if (!in_array($user->role, [UserType::Farmer, UserType::Rider], true)) {
            throw new InvalidArgumentException('Only farmers and riders need verification.');
        }
    }

    private function assertAwaitingReview(User $applicant): void
    {
        if ($applicant->status !== UserStatus::PendingVerification) {
            throw new RuntimeException('This account is not waiting for review.');
        }
    }

    /* ---------------- helpers ---------------- */

    private function applicants()
    {
        return User::query()->whereIn('role', [UserType::Farmer->value, UserType::Rider->value]);
    }

    private function applyFilter($query, string $filter)
    {
        return match ($filter) {
            'rejected' => $query->where('status', UserStatus::Rejected->value),
            'approved' => $query->whereIn('id', VerificationDocument::query()->select('user_id')->where('status', VerificationStatus::Approved->value)),
            default => $query
                ->where('status', UserStatus::PendingVerification->value)
                ->whereIn('id', VerificationDocument::query()->select('user_id')->where('status', VerificationStatus::Pending->value)),
        };
    }

    private function findApplicant(int $userId): User
    {
        $user = User::with(['farmerProfile', 'riderProfile.vehicles'])->find($userId);

        if ($user === null || !in_array($user->role, [UserType::Farmer, UserType::Rider], true)) {
            throw new InvalidArgumentException('Applicant not found.');
        }

        return $user;
    }

    private function loadRequest(User $user): VerificationRequestData
    {
        $user->loadMissing(['farmerProfile', 'riderProfile.vehicles']);

        return $this->toRequestData($user, VerificationDocument::where('user_id', $user->id)->orderBy('id')->get());
    }

    /** @param DocumentType[] $types */
    private function describe(array $types): array
    {
        return array_map(fn (DocumentType $t) => ['type' => $t->value, 'label' => $t->label()], $types);
    }

    private function toRequestData(User $user, Collection $documents): VerificationRequestData
    {
        $farmer = $user->farmerProfile;
        $rider = $user->riderProfile;
        $documents = $documents->sortBy('id')->values();
        $reviewed = $documents->whereNotNull('reviewed_at')->sortByDesc('reviewed_at')->first();

        return new VerificationRequestData(
            userId: $user->id,
            fullName: $user->full_name,
            role: $user->role->value,
            phone: $user->phone,
            email: $user->email,
            accountStatus: $user->status->value,
            nic: $farmer?->nic,
            address: $farmer?->address,
            region: $farmer?->region ?? $rider?->region,
            riderCategory: $rider?->category?->value,
            vehicles: $rider
                ? $rider->vehicles->map(fn ($v) => [
                    'type' => $v->vehicle_type instanceof BackedEnum ? $v->vehicle_type->value : (string) $v->vehicle_type,
                    'number' => $v->vehicle_no,
                ])->all()
                : [],
            documents: $documents->map(fn (VerificationDocument $d) => VerificationDocumentData::fromModel($d))->all(),
            missing: $this->missingTypes($user, $documents),
            submittedAt: $documents->first()?->created_at->toIso8601String(),
            reviewedAt: $reviewed?->reviewed_at->toIso8601String(),
        );
    }
}
