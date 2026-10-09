<?php

namespace App\Domains\Verification\Services;

use App\Domains\Verification\DTOs\VerificationDocumentData;
use App\Domains\Verification\DTOs\VerificationOverviewData;
use App\Domains\Verification\DTOs\VerificationRequestData;
use App\Domains\Verification\Models\VerificationDocument;
use App\Enums\DocumentType;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface VerificationServiceInterface
{
    /** 2.2: a farmer or rider's own documents and verification status. */
    public function overview(User $user): VerificationOverviewData;

    /** 2.1: upload (or replace) one identification document. */
    public function submitDocument(User $user, DocumentType $type, UploadedFile $file): VerificationDocumentData;

    /** Path of a document on the private disk. Only the owner or an admin may read it. */
    public function documentPath(User $viewer, VerificationDocument $document): string;

    /**
     * 2.3: the review queue for an admin.
     * @param 'pending'|'rejected'|'approved' $filter
     * @return array{data: VerificationRequestData[], total: int, page: int, perPage: int, counts: array<string, int>}
     */
    public function reviewQueue(string $filter, int $page, int $perPage): array;

    public function reviewDetail(int $userId): VerificationRequestData;

    /** 2.3: approve a request. The account becomes active. */
    public function approve(User $admin, int $userId): VerificationRequestData;

    /** 2.3: reject a request with a reason the applicant will see. */
    public function reject(User $admin, int $userId, string $reason): VerificationRequestData;
}
