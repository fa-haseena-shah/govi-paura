<?php

namespace App\Console\Commands;

use App\Domains\Delivery\Services\DeliveryServiceInterface;
use Illuminate\Console\Command;

class EnforceDeliverySla extends Command
{
    protected $signature = 'deliveries:enforce-sla';

    protected $description = 'Create missing deliveries, release unpicked claims, and flag deliveries at risk of missing the 12-hour deadline';

    public function handle(DeliveryServiceInterface $deliveries): int
    {
        $counts = $deliveries->enforceSla();

        $this->info(sprintf(
            'Created %d, released %d, at risk %d, breached %d.',
            $counts['created'], $counts['released'], $counts['at_risk'], $counts['breached'],
        ));

        return self::SUCCESS;
    }
}
