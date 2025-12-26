<?php

namespace Modules\Cms\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Entities\Setting;

class SettingController extends Controller
{
    /**
     * Get all public settings.
     */
    public function index(): JsonResponse
    {
        $settings = Setting::public()->get();

        // Transform to key-value pairs
        $data = $settings->pluck('casted_value', 'key');

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Get a specific public setting by key.
     */
    public function show(string $key): JsonResponse
    {
        $setting = Setting::public()
            ->where('key', $key)
            ->first();

        if (! $setting) {
            return response()->json([
                'message' => 'Paramètre non trouvé.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'key' => $setting->key,
                'value' => $setting->casted_value,
            ],
        ]);
    }

    /**
     * Get multiple settings by keys.
     */
    public function multiple(Request $request): JsonResponse
    {
        $request->validate([
            'keys' => ['required', 'array'],
            'keys.*' => ['string'],
        ]);

        $settings = Setting::public()
            ->whereIn('key', $request->input('keys'))
            ->get();

        $data = $settings->pluck('casted_value', 'key');

        return response()->json([
            'data' => $data,
        ]);
    }
}
