<?php

namespace App\Domains\Verification\Http\Controllers;

use App\Domains\Verification\Http\Requests\RejectVerificationRequest;
use App\Domains\Verification\Http\Requests\UploadVerificationDocumentRequest;
use App\Domains\Verification\Models\VerificationDocument;
use App\Domains\Verification\Services\VerificationServiceInterface;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    public function __construct(
        private readonly VerificationServiceInterface $verificationService,
    ) {}

    /* ---------- farmer / rider ---------- */

    // GET /verification
    public function show(Request $request): JsonResponse
    {
        if (!$this->isApplicant($request)) {
            return $this->error('Only farmers and riders need verification.', 403);
        }

        return response()->json(['status' => 'success', 'data' => $this->verificationService->overview($request->user())]);
    }

    // POST /verification/documents   (multipart: type, file)
    public function upload(UploadVerificationDocumentRequest $request): JsonResponse
    {
        if (!$this->isApplicant($request)) {
            return $this->error('Only farmers and riders need verification.', 403);
        }

        try {
            $document = $this->verificationService->submitDocument(
                $request->user(),
                DocumentType::from($request->validated('type')),
                $request->file('file'),
            );

            return response()->json(['status' => 'success', 'data' => $document], 201);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 409);
        }
    }

    // GET /verification/documents/{document}/file   (the owner or an admin)
    public function file(Request $request, VerificationDocument $document): StreamedResponse|JsonResponse
    {
        try {
            $path = $this->verificationService->documentPath($request->user(), $document);

            return Storage::disk('local')->response($path, $document->original_name);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 403);
        }
    }

    /* ---------- admin ---------- */

    // GET /admin/verifications?status=pending|rejected|approved&page=1&per_page=10
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Only admins can review verification requests.', 403);
        }

        $filter = in_array($request->query('status'), ['pending', 'rejected', 'approved'], true) ? $request->query('status') : 'pending';
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $result = $this->verificationService->reviewQueue($filter, $page, $perPage);

        return response()->json(['status' => 'success', ...$result]);
    }

    // GET /admin/verifications/{user}
    public function detail(Request $request, int $user): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Only admins can review verification requests.', 403);
        }

        try {
            return response()->json(['status' => 'success', 'data' => $this->verificationService->reviewDetail($user)]);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 404);
        }
    }

    // POST /admin/verifications/{user}/approve
    public function approve(Request $request, int $user): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Only admins can approve verification requests.', 403);
        }

        try {
            return response()->json(['status' => 'success', 'data' => $this->verificationService->approve($request->user(), $user)]);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 409);
        }
    }

    // POST /admin/verifications/{user}/reject   { "reason": "..." }
    public function reject(RejectVerificationRequest $request, int $user): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Only admins can reject verification requests.', 403);
        }

        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->verificationService->reject($request->user(), $user, $request->validated('reason')),
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 409);
        }
    }

    private function isApplicant(Request $request): bool
    {
        return $request->user()->isFarmer() || $request->user()->isRider();
    }

    private function error(string $message, int $code): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], $code);
    }
}
