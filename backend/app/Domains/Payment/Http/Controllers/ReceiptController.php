<?php

namespace App\Domains\Payment\Http\Controllers;

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Models\Receipt;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class ReceiptController extends Controller
{
    /**
     * Return the receipt for a paid order.
     *
     * Accessible by the buyer and the farmer who both have a stake in the order.
     */
    public function forOrder(Request $request, int $order): JsonResponse
    {
        $o = Order::with([
            'items.listing',
            'buyer.buyerProfile',
            'farmer',
            'payments.receipt',
        ])->find($order);

        if ($o === null) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        $userId = $request->user()->id;
        if ($o->buyer_id !== $userId && $o->farmer_id !== $userId) {
            return response()->json(['status' => 'error', 'message' => 'You do not have permission to view this receipt.'], 403);
        }

        // find the successful payment that has a receipt attached
        /** @var Payment|null $payment */
        $payment = $o->payments
            ->sortByDesc('id')
            ->first(fn ($p) => $p->receipt !== null);

        if ($payment === null || $payment->receipt === null) {
            return response()->json(['status' => 'error', 'message' => 'No receipt found for this order. Payment may still be pending.'], 404);
        }

        /** @var Receipt $receipt */
        $receipt = $payment->receipt;

        $items = $o->items->map(fn ($item) => [
            'product'    => $item->listing?->crop_type ?? 'Product #' . $item->listing_id,
            'qty'        => $item->qty,
            'unit_price' => (float) $item->unit_price,
            'sub_total'  => (float) $item->sub_total,
        ])->values()->all();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'receipt_number'  => $receipt->receipt_number,
                'issued_at'       => $receipt->issued_at?->toIso8601String(),
                'amount'          => (float) $receipt->amount,
                'currency'        => $payment->currency,
                'payment_method'  => $payment->method->value,
                'reference'       => $payment->reference,
                'order_id'        => $o->id,
                'order_date'      => $o->created_at->toIso8601String(),
                'buyer_name'      => $o->buyer?->full_name,
                'buyer_business'  => $o->buyer?->buyerProfile?->business_name,
                'farmer_name'     => $o->farmer?->full_name,
                'items'           => $items,
            ],
        ]);
    }
}

