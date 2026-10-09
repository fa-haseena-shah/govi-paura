<?php

namespace App\Domains\Verification\DTOs;

use App\Domains\Verification\Models\VerificationDocument;

final readonly class VerificationDocumentData
{
    public function __construct(
        public int $id,
        public string $type,
        public string $typeLabel,
        public string $status,
        public string $originalName,
        public ?string $rejectionReason,
        public string $submittedAt,
        public ?string $reviewedAt,
    ) {}

    public static function fromModel(VerificationDocument $doc): self
    {
        return new self(
            id: $doc->id,
            type: $doc->type->value,
            typeLabel: $doc->type->label(),
            status: $doc->status->value,
            originalName: $doc->original_name,
            rejectionReason: $doc->rejection_reason,
            submittedAt: $doc->created_at->toIso8601String(),
            reviewedAt: $doc->reviewed_at?->toIso8601String(),
        );
    }
}
