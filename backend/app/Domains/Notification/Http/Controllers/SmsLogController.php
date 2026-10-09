<?php

namespace App\Domains\Notification\Http\Controllers;

use App\Domains\Notification\Models\SmsLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $logs = SmsLog::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['status' => 'success', 'data' => $logs]);
    }
}