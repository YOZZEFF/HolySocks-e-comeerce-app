<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');

        return response()->json([
            'status'  => true,
            'message' => 'Settings retrieved successfully',
            'data'    => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'shipping_free_threshold' => 'sometimes|numeric|min:0',
            'shipping_fee'            => 'sometimes|numeric|min:0',
        ]);

        foreach (['shipping_free_threshold', 'shipping_fee'] as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $request->$key]
                );
            }
        }
        Cache::forget('settings');

        $settings = Setting::pluck('value', 'key');

        return response()->json([
            'status'  => true,
            'message' => 'Settings updated successfully',
            'data'    => $settings,
        ]);
    }
}
