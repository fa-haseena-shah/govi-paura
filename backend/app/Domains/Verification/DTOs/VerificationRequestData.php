<?php

namespace App\Domains\Verification\DTOs;

/** One applicant as the admin sees them in the review queue. */
final readonly class VerificationRequestData
{
    public function __construct(
        public int $userId,
        public string $fullName,
        public string $role,
        public string $phone,
        public ?string $email,
        public string $accountStatus,
        public ?string $nic,                 // farmers: the NIC number given at registration
        public ?string $address,
        public ?string $region,
        public ?string $riderCategory,
        /** @var array<int, array{type: string, number: string}> */
        public array $vehicles,
        /** @var VerificationDocumentData[] */
        public array $documents,
        /** @var string[] required document types that have not been submitted */
        public array $missing,
        public ?string $submittedAt,
        public ?string $reviewedAt,
    ) {}
}
