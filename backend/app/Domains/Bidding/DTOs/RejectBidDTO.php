<?php

namespace App\Domains\Bidding\DTOs;

final readonly class RejectBidDTO
{
    public function __construct(
        public int $bidId,
        public int $farmerId,
        public ?string $reason,
    ) {}

    public static function fromArray(array $data, int $bidId, int $farmerId): self
    {
        $reason = isset($data['reason']) ? trim((string) $data['reason']) : null;

        return new self(
            bidId: $bidId,
            farmerId: $farmerId,
            reason: $reason === '' ? null : $reason,
        );
    }
}
