<?php

namespace App\Domains\Reports\Services;

use App\Domains\Listing\Models\Listing;
use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReportService
{
    /**
     * Build a report payload for the authenticated user.
     *
     * @return array{title:string, subtitle:string, orientation:string, summary:array<string, mixed>, rows:array<int, array<string, mixed>>}
     */
    public function build(User $user, string $reportType, ?string $startDate, ?string $endDate): array
    {
        $reportType = strtolower(trim($reportType));
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        return match ($user->role->value) {
            'farmer' => $this->farmerReport($user, $reportType, $start, $end),
            'buyer' => $this->buyerReport($user, $reportType, $start, $end),
            'rider' => $this->riderReport($user, $reportType, $start, $end),
            default => throw new InvalidArgumentException('Unsupported report role.'),
        };
    }

    public function renderHtml(array $report): string
    {
        $title = htmlspecialchars($report['title'], ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars($report['subtitle'], ENT_QUOTES, 'UTF-8');
        $generatedAt = now()->format('Y-m-d H:i');
        $startDate = $report['range']['start_date'] ?? '—';
        $endDate = $report['range']['end_date'] ?? '—';

        $summaryHtml = '';
        foreach (($report['summary'] ?? []) as $label => $value) {
            $summaryHtml .= sprintf(
                '<div class="summary-box"><span>%s</span><strong>%s</strong></div>',
                htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
            );
        }

        $rowsHtml = '';
        if (empty($report['rows'])) {
            $rowsHtml = '<tr><td colspan="100%" class="empty">No records found for the selected period.</td></tr>';
        } else {
            $headers = array_keys($report['rows'][0]);
            $rowsHtml .= '<tr>' . collect($headers)->map(fn ($header) => '<th>' . htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8') . '</th>')->implode('') . '</tr>';
            foreach ($report['rows'] as $row) {
                $rowsHtml .= '<tr>' . collect($row)->map(fn ($cell) => '<td>' . htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') . '</td>')->implode('') . '</tr>';
            }
        }

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; font-size: 11px; margin: 24px; }
        .header { border-bottom: 2px solid #1f5f3d; padding-bottom: 12px; margin-bottom: 18px; }
        .brand { font-size: 18px; font-weight: 700; color: #1f5f3d; }
        .meta { margin-top: 8px; display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; color: #475569; }
        .title { font-size: 24px; font-weight: 700; margin-top: 16px; }
        .summary-grid { display: flex; flex-wrap: wrap; gap: 12px; margin: 18px 0; }
        .summary-box { flex: 1 1 160px; background: #f6f8f5; border: 1px solid #dfe7df; border-radius: 8px; padding: 10px 12px; }
        .summary-box span { display: block; font-size: 10px; color: #64748b; margin-bottom: 6px; }
        .summary-box strong { font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #dbe2ea; padding: 7px 8px; vertical-align: top; text-align: left; }
        th { background: #eef6f0; font-size: 10px; text-transform: uppercase; }
        tr:nth-child(even) td { background: #fafafa; }
        .footer { margin-top: 24px; font-size: 10px; color: #64748b; display: flex; justify-content: space-between; }
        .empty { text-align: center; padding: 16px; color: #64748b; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Govi Paura Marketplace</div>
        <div class="meta">
            <div><strong>Report:</strong> {$title}</div>
            <div><strong>Generated:</strong> {$generatedAt}</div>
        </div>
        <div class="meta">
            <div><strong>User:</strong> {$report['user_name']}</div>
            <div><strong>Role:</strong> {$report['user_role']}</div>
        </div>
        <div class="meta">
            <div><strong>Period:</strong> {$startDate} to {$endDate}</div>
        </div>
    </div>

    <div class="title">{$subtitle}</div>

    <div class="summary-grid">{$summaryHtml}</div>

    <table>
        {$rowsHtml}
    </table>

    <div class="footer">
        <div>Report ID: {$report['report_id']}</div>
        <div>Page 1</div>
    </div>
</body>
</html>
HTML;
    }

    private function farmerReport(User $user, string $reportType, ?Carbon $start, ?Carbon $end): array
    {
        $allowed = ['sales', 'products', 'orders', 'income'];
        if (!in_array($reportType, $allowed, true)) {
            throw new InvalidArgumentException('Invalid farmer report type.');
        }

        $baseOrders = Order::query()->where('farmer_id', $user->id)
            ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end->toDateString()));

        if ($reportType === 'sales') {
            $rows = [];
            foreach ($baseOrders->with('items.listing')->orderByDesc('created_at')->get() as $order) {
                foreach ($order->items as $item) {
                    $rows[] = [
                        'Order ID' => $order->id,
                        'Date' => $order->created_at?->toDateString() ?? '—',
                        'Product' => $item->listing?->crop_type ?? 'Product #' . $item->listing_id,
                        'Qty' => (string) $item->qty,
                        'Unit Price' => $this->money($item->unit_price),
                        'Total' => $this->money($item->sub_total),
                        'Status' => (string) $order->status->value,
                    ];
                }
            }

            return $this->finalizeReport($user, 'Farmer Sales Report', 'Sales report for products sold', 'sales', $rows, [
                'Total Sales' => $this->money($baseOrders->where('status', 'completed')->sum('total_amount')),
                'Orders' => (string) $baseOrders->count(),
                'Products' => (string) array_sum(array_map(fn ($row) => (int) $row['Qty'], $rows)),
            ], $start, $end);
        }

        if ($reportType === 'products') {
            $rows = Listing::query()->where('farmer_id', $user->id)
                ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start->toDateString()))
                ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end->toDateString()))
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($listing) => [
                    'Product' => $listing->crop_type,
                    'Category' => $listing->selling_method?->value ?? '—',
                    'Available Qty' => (string) $listing->qty,
                    'Unit Price' => $this->money($listing->price_per_unit),
                    'Status' => (string) ($listing->status?->value ?? 'unknown'),
                    'Listed' => $listing->created_at?->toDateString() ?? '—',
                ])
                ->all();

            $avgPrice = count($rows) ? array_reduce($rows, fn ($carry, $row) => $carry + (float) preg_replace('/[^\d.]/', '', $row['Unit Price']), 0) / count($rows) : 0;

            return $this->finalizeReport($user, 'Farmer Product Report', 'Inventory and listing status', 'products', $rows, [
                'Total Listings' => (string) count($rows),
                'Available Stock' => (string) array_sum(array_map(fn ($row) => (int) $row['Available Qty'], $rows)),
                'Avg. Price' => $this->money($avgPrice),
            ], $start, $end);
        }

        if ($reportType === 'orders') {
            $rows = [];
            foreach ($baseOrders->with('items.listing')->orderByDesc('created_at')->get() as $order) {
                $items = $order->items->map(fn ($item) => ($item->listing?->crop_type ?? 'Product #' . $item->listing_id) . ' x' . $item->qty)->implode(', ');
                $rows[] = [
                    'Order ID' => (string) $order->id,
                    'Products' => $items,
                    'Date' => $order->created_at?->toDateString() ?? '—',
                    'Status' => (string) $order->status->value,
                    'Total' => $this->money($order->total_amount),
                    'Payment' => (string) ($order->payment_status?->value ?? 'unpaid'),
                ];
            }

            return $this->finalizeReport($user, 'Farmer Order Report', 'Self-managed orders and order totals', 'orders', $rows, [
                'Orders' => (string) count($rows),
                'Total Value' => $this->money($baseOrders->sum('total_amount')),
                'Paid Orders' => (string) $baseOrders->where('payment_status', 'paid')->count(),
            ], $start, $end);
        }

        $incomeOrders = (clone $baseOrders)->where('status', 'completed')->where('payment_status', 'paid');
        $rows = [];
        foreach ($incomeOrders->orderByDesc('created_at')->get() as $order) {
            $rows[] = [
                'Order ID' => (string) $order->id,
                'Date' => $order->created_at?->toDateString() ?? '—',
                'Paid' => $this->money($order->total_amount),
                'Status' => 'completed',
            ];
        }

        return $this->finalizeReport($user, 'Farmer Income Summary', 'Completed and paid sales only', 'income', $rows, [
            'Total Sales' => $this->money($incomeOrders->sum('total_amount')),
            'Paid Orders' => (string) $incomeOrders->count(),
            'Exclusions' => 'Cancelled / unpaid / incomplete order lines excluded',
        ], $start, $end);
    }

    private function buyerReport(User $user, string $reportType, ?Carbon $start, ?Carbon $end): array
    {
        $allowed = ['purchases', 'status', 'payments', 'spending'];
        if (!in_array($reportType, $allowed, true)) {
            throw new InvalidArgumentException('Invalid buyer report type.');
        }

        $ordersQuery = Order::query()->where('buyer_id', $user->id)
            ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end->toDateString()))
            ->with('items.listing')
            ->orderByDesc('created_at');

        if ($reportType === 'purchases') {
            $rows = [];
            foreach ($ordersQuery->get() as $order) {
                $rows[] = [
                    'Order ID' => (string) $order->id,
                    'Date' => $order->created_at?->toDateString() ?? '—',
                    'Products' => $order->items->map(fn ($item) => ($item->listing?->crop_type ?? 'Product #' . $item->listing_id) . ' x' . $item->qty)->implode(', '),
                    'Total' => $this->money($order->total_amount),
                    'Status' => (string) $order->status->value,
                    'Payment' => (string) ($order->payment_status?->value ?? 'unpaid'),
                ];
            }

            return $this->finalizeReport($user, 'Buyer Purchase History Report', 'Your purchase history', 'purchases', $rows, [
                'Orders'      => (string) (clone $ordersQuery)->count(),
                'Total Spend' => $this->money((clone $ordersQuery)->sum('total_amount')),
                'Paid Orders' => (string) (clone $ordersQuery)->where('payment_status', 'paid')->count(),
            ], $start, $end);
        }

        if ($reportType === 'status') {
            $rows = [];
            foreach ($ordersQuery->get() as $order) {
                $rows[] = [
                    'Order ID' => (string) $order->id,
                    'Date' => $order->created_at?->toDateString() ?? '—',
                    'Status' => (string) $order->status->value,
                    'Payment' => (string) ($order->payment_status?->value ?? 'unpaid'),
                    'Amount' => $this->money($order->total_amount),
                ];
            }

            return $this->finalizeReport($user, 'Buyer Order Status Report', 'Orders by current status', 'status', $rows, [
                'Pending'   => (string) (clone $ordersQuery)->where('status', 'pending')->count(),
                'Confirmed' => (string) (clone $ordersQuery)->where('status', 'confirmed')->count(),
                'Completed' => (string) (clone $ordersQuery)->where('status', 'completed')->count(),
                'Cancelled' => (string) (clone $ordersQuery)->where('status', 'cancelled')->count(),
            ], $start, $end);
        }

        if ($reportType === 'payments') {
            $orderIds = $ordersQuery->pluck('id');
            $rows = Payment::query()->where('payable_type', Order::class)
                ->whereIn('payable_id', $orderIds)
                ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start->toDateString()))
                ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end->toDateString()))
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($payment) => [
                    'Order ID' => (string) $payment->payable_id,
                    'Payment Date' => $payment->created_at?->toDateString() ?? '—',
                    'Method' => (string) ($payment->method?->value ?? $payment->method),
                    'Amount' => $this->money($payment->amount),
                    'Status' => (string) ($payment->status?->value ?? $payment->status),
                    'Reference' => (string) ($payment->reference ?? '—'),
                ])->all();

            $amounts = array_map(fn ($row) => (float) preg_replace('/[^\d.]/', '', $row['Amount']), $rows);
            $paidTotal = array_sum($amounts);
            $outstanding = max(0, $ordersQuery->sum('total_amount') - $paidTotal);

            return $this->finalizeReport($user, 'Buyer Payment Report', 'Payments recorded for your orders', 'payments', $rows, [
                'Payment Count' => (string) count($rows),
                'Payments Received' => $this->money($paidTotal),
                'Outstanding' => $this->money($outstanding),
            ], $start, $end);
        }

        $paidTotal = (clone $ordersQuery)->where('payment_status', 'paid')->sum('total_amount');
        $outstanding = (clone $ordersQuery)->where('payment_status', 'unpaid')->sum('total_amount');
        $rows = [];
        foreach ($ordersQuery->get() as $order) {
            $rows[] = [
                'Order ID' => (string) $order->id,
                'Date' => $order->created_at?->toDateString() ?? '—',
                'Status' => (string) $order->status->value,
                'Amount' => $this->money($order->total_amount),
                'Paid' => (string) ($order->payment_status === 'paid' ? 'Yes' : 'No'),
            ];
        }

        return $this->finalizeReport($user, 'Buyer Spending Summary', 'Purchase totals for this period', 'spending', $rows, [
            'Total Value'  => $this->money((clone $ordersQuery)->sum('total_amount')),
            'Total Paid'   => $this->money($paidTotal),
            'Outstanding'  => $this->money($outstanding),
            'Order Count'  => (string) (clone $ordersQuery)->count(),
        ], $start, $end);
    }

    private function riderReport(User $user, string $reportType, ?Carbon $start, ?Carbon $end): array
    {
        $allowed = ['deliveries', 'status', 'earnings', 'performance'];
        if (!in_array($reportType, $allowed, true)) {
            throw new InvalidArgumentException('Invalid rider report type.');
        }

        $deliveriesQuery = DB::table('deliveries')->where('rider_id', $user->id)
            ->when($start, fn ($query) => $query->whereDate('created_at', '>=', $start->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('created_at', '<=', $end->toDateString()))
            ->orderByDesc('created_at');

        if ($reportType === 'deliveries') {
            $rows = (clone $deliveriesQuery)->get()->map(fn ($delivery) => [
                'Delivery ID' => (string) $delivery->id,
                'Order ID'    => (string) $delivery->order_id,
                'Assigned'    => $delivery->assigned_at ? Carbon::parse($delivery->assigned_at)->toDateString() : '—',
                'Delivered'   => $delivery->delivered_at ? Carbon::parse($delivery->delivered_at)->toDateString() : '—',
                'Status'      => (string) $delivery->status,
                'Pickup'      => (string) $delivery->pickup,
                'Dropoff'     => (string) $delivery->dropoff,
            ])->all();

            return $this->finalizeReport($user, 'Rider Delivery History Report', 'Assigned deliveries for this rider', 'deliveries', $rows, [
                'Assigned'  => (string) (clone $deliveriesQuery)->count(),
                'Delivered' => (string) (clone $deliveriesQuery)->where('status', 'delivered')->count(),
                'Pending'   => (string) (clone $deliveriesQuery)->where('status', 'pending')->count(),
            ], $start, $end);
        }

        if ($reportType === 'status') {
            $rows = (clone $deliveriesQuery)->get()->map(fn ($delivery) => [
                'Delivery ID' => (string) $delivery->id,
                'Order ID'    => (string) $delivery->order_id,
                'Status'      => (string) $delivery->status,
                'Assigned'    => $delivery->assigned_at ? Carbon::parse($delivery->assigned_at)->toDateString() : '—',
                'Delivered'   => $delivery->delivered_at ? Carbon::parse($delivery->delivered_at)->toDateString() : '—',
            ])->all();

            return $this->finalizeReport($user, 'Rider Delivery Status Report', 'Delivery status breakdown', 'status', $rows, [
                'Assigned'   => (string) (clone $deliveriesQuery)->count(),
                'In Transit' => (string) (clone $deliveriesQuery)->whereIn('status', ['assigned', 'picked_up', 'in_transit'])->count(),
                'Delivered'  => (string) (clone $deliveriesQuery)->where('status', 'delivered')->count(),
                'Pending'    => (string) (clone $deliveriesQuery)->where('status', 'pending')->count(),
            ], $start, $end);
        }

        if ($reportType === 'earnings') {
            $rows = (clone $deliveriesQuery)->get()->map(fn ($delivery) => [
                'Delivery ID'  => (string) $delivery->id,
                'Order ID'     => (string) $delivery->order_id,
                'Status'       => (string) $delivery->status,
                'Fee (LKR)'    => $delivery->at_risk_fee_lkr !== null
                    ? $this->money($delivery->at_risk_fee_lkr)
                    : ($delivery->fee_lkr !== null ? $this->money($delivery->fee_lkr) : '—'),
                'At-Risk Rate' => $delivery->at_risk_fee_lkr !== null ? 'Yes' : 'No',
                'Completed'    => $delivery->delivered_at ? Carbon::parse($delivery->delivered_at)->toDateString() : '—',
            ])->all();

            $deliveredRows = array_filter($rows, fn ($r) => $r['Status'] === 'delivered');
            $totalEarnings = array_reduce(
                array_values($deliveredRows),
                fn ($carry, $row) => $carry + (float) preg_replace('/[^\d.]/', '', $row['Fee (LKR)']),
                0.0
            );

            return $this->finalizeReport($user, 'Rider Earnings Report', 'Completed deliveries and fee details', 'earnings', $rows, [
                'Delivered'      => (string) (clone $deliveriesQuery)->where('status', 'delivered')->count(),
                'Total Earnings' => $this->money($totalEarnings),
            ], $start, $end);
        }

        $rows = (clone $deliveriesQuery)->get()->map(fn ($delivery) => [
            'Delivery ID' => (string) $delivery->id,
            'Order ID'    => (string) $delivery->order_id,
            'Status'      => (string) $delivery->status,
            'Assigned'    => $delivery->assigned_at ? Carbon::parse($delivery->assigned_at)->toDateString() : '—',
            'Completed'   => $delivery->delivered_at ? Carbon::parse($delivery->delivered_at)->toDateString() : '—',
        ])->all();

        $delivered = (clone $deliveriesQuery)->where('status', 'delivered')->count();
        $total     = (clone $deliveriesQuery)->count();
        $completionRate = $total ? round(($delivered / $total) * 100, 2) . '%' : '0%';

        return $this->finalizeReport($user, 'Rider Performance Summary', 'Delivery completion and assigned workload', 'performance', $rows, [
            'Assigned'        => (string) $total,
            'Delivered'       => (string) $delivered,
            'Pending'         => (string) (clone $deliveriesQuery)->where('status', 'pending')->count(),
            'In Transit'      => (string) (clone $deliveriesQuery)->whereIn('status', ['assigned', 'picked_up', 'in_transit'])->count(),
            'Completion Rate' => $completionRate,
        ], $start, $end);
    }

    private function finalizeReport(User $user, string $title, string $subtitle, string $reportType, array $rows, array $summary, ?Carbon $start, ?Carbon $end): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'report_id' => strtoupper($user->role->value) . '-' . $reportType . '-' . now()->format('YmdHis'),
            'user_name' => $user->full_name,
            'user_role' => ucfirst($user->role->value),
            'orientation' => count($rows) > 5 ? 'landscape' : 'portrait',
            'range' => [
                'start_date' => $start ? $start->toDateString() : '—',
                'end_date' => $end ? $end->toDateString() : '—',
            ],
            'summary' => $summary,
            'rows' => $rows,
        ];
    }

    private function money(mixed $amount): string
    {
        return 'LKR ' . number_format((float) $amount, 2, '.', ',');
    }
}
