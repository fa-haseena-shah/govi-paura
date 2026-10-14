<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Enums\BusinessType;
use App\Enums\DeliveryCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $this->profileFor($user);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        $data = [
            'full_name' => $user->full_name,
            'role'      => $user->role->value ?? $user->role,
        ];

        if ($this->isRole($user, 'buyer')) {
            $data += [
                'business_name' => $profile->business_name,
                'business_type' => $profile->business_type instanceof BusinessType
                    ? $profile->business_type->value
                    : $profile->business_type,
                'address' => $profile->address,
                'region'  => $profile->region,
            ];
        } elseif ($this->isRole($user, 'farmer')) {
            $data += [
                'address' => $profile->address,
                'region'  => $profile->region,
            ];
        } elseif ($this->isRole($user, 'rider')) {
            $data += [
                'category' => $profile->category instanceof DeliveryCategory
                    ? $profile->category->value
                    : $profile->category,
                'region'   => $profile->region,
            ];
        }

        return response()->json(['data' => $data]);
    }

    public function update(Request $request): JsonResponse
    {
        $user    = $request->user();
        $profile = $this->profileFor($user);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found.',
            ], 404);
        }

        if ($this->isRole($user, 'buyer')) {
            $validated = $request->validate([
                'full_name'     => ['required', 'string', 'max:191'],
                'business_name' => ['required', 'string', 'max:191'],
                'business_type' => ['required', 'string', Rule::enum(BusinessType::class)],
                'address'       => ['required', 'string', 'max:500'],
                'region'        => ['required', 'string', 'max:191'],
            ]);

            $user->full_name = $validated['full_name'];

            $profile->business_name = $validated['business_name'];
            $profile->business_type = $validated['business_type'];
            $profile->address       = $validated['address'];
            $profile->region        = $validated['region'];

        } elseif ($this->isRole($user, 'farmer')) {
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:191'],
                'address'   => ['required', 'string', 'max:500'],
                'region'    => ['required', 'string', 'max:191'],
            ]);

            $user->full_name = $validated['full_name'];

            $profile->address = $validated['address'];
            $profile->region  = $validated['region'];

        } elseif ($this->isRole($user, 'rider')) {
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:191'],
                'category'  => ['required', 'string', Rule::enum(DeliveryCategory::class)],
                'region'    => ['required', 'string', 'max:191'],
            ]);

            $user->full_name = $validated['full_name'];

            $profile->category = $validated['category'];
            $profile->region   = $validated['region'];

        } else {
            return response()->json([
                'message' => 'Your role cannot update this profile.',
            ], 403);
        }

        $user->save();
        $profile->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
        ]);
    }

    private function profileFor($user)
    {
        if ($this->isRole($user, 'buyer')) {
            return $user->buyerProfile;
        }

        if ($this->isRole($user, 'farmer')) {
            return $user->farmerProfile;
        }

        if ($this->isRole($user, 'rider')) {
            return $user->riderProfile;
        }

        return null;
    }

    private function isRole($user, string $role): bool
    {
        $value = $user->role->value ?? $user->role;

        return strtolower((string) $value) === $role;
    }
}