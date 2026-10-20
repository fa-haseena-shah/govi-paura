<?php

namespace App\Domains\Reports\Http\Controllers;

use App\Domains\Reports\Services\ReportService;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function download(Request $request, string $role)
    {
        $user = $request->user();
        abort_unless($user, 401, 'Authentication required.');
        abort_unless(in_array($role, ['farmer', 'buyer', 'rider'], true), 404);
        abort_unless($user->role->value === $role, 403, 'You cannot access another role report.');

        $validated = $request->validate([
            'type' => ['required', 'string'],
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        try {
            $report = $this->reportService->build($user, $validated['type'], $validated['start_date'] ?? null, $validated['end_date'] ?? null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        $html = $this->reportService->renderHtml($report);
        $pdf = Pdf::loadHTML($html)
            ->setPaper($report['orientation'] === 'landscape' ? 'landscape' : 'portrait');

        return $pdf->download(sprintf('%s-%s-report.pdf', $role, $validated['type']));
    }
}
