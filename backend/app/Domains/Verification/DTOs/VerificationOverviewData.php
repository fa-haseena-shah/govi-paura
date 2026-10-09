<?php

namespace App\Domains\Verification\DTOs;

/** What a farmer or rider sees about their own verification. */
final readonly class VerificationOverviewData
{
    public function __construct(
        public string $accountStatus,          // pending_verification | rejected | active
        public bool $canUpload,
        /** @var array<int, array{type: string, label: string}> */
        public array $required,
        /** @var array<int, array{type: string, label: string}> */
        public array $optional,
        /** @var VerificationDocumentData[] */
        public array $documents,
        /** @var string[] required document types still to upload (or to upload again) */
        public array $missing,
        public ?string $rejectionReason,
    ) {}
}
