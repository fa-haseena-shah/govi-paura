<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Farmers and riders can sign in before they are verified (to upload documents and
 * track their status), but every other route needs an active account.
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && !$user->isActive()) {
            return response()->json([
                'status' => 'error',
                'message' => match ($user->status) {
                    UserStatus::PendingVerification => 'Your account is awaiting verification.',
                    UserStatus::Rejected => 'Your verification was rejected. Upload your documents again.',
                    UserStatus::Suspended => 'Your account has been suspended. Contact support.',
                    default => 'This account is not active.',
                },
                'account_status' => $user->status->value,
            ], 403);
        }

        return $next($request);
    }
}
