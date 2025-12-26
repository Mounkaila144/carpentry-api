<?php

namespace Modules\Cms\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Entities\Setting;
use Modules\Cms\Http\Requests\StoreSettingRequest;
use Modules\Cms\Http\Resources\SettingResource;

class SettingController extends Controller
{
    /**
     * Display a listing of the settings.
     */
    public function index(Request $request): JsonResponse
    {
        $group = $request->input('group');

        $query = Setting::query();

        if ($group) {
            $query->group($group);
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        // Group settings by group
        $grouped = $settings->groupBy('group')->map(function ($items) {
            return SettingResource::collection($items);
        });

        return response()->json([
            'data' => $grouped,
        ]);
    }

    /**
     * Store a new setting.
     */
    public function store(StoreSettingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $value = $data['value'] ?? '';
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $setting = Setting::create([
            'key' => $data['key'],
            'value' => $value,
            'group' => $data['group'] ?? 'general',
            'type' => $data['type'] ?? 'string',
            'options' => $data['options'] ?? null,
            'is_public' => $data['is_public'] ?? false,
        ]);

        return response()->json([
            'message' => 'Paramètre créé avec succès.',
            'data' => new SettingResource($setting),
        ], 201);
    }

    /**
     * Get a specific setting by id.
     */
    public function show(string $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);

        return response()->json([
            'data' => new SettingResource($setting),
        ]);
    }

    /**
     * Update a setting.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);

        $request->validate([
            'key' => ['sometimes', 'string', 'max:255'],
            'value' => ['nullable'],
            'group' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'in:string,boolean,integer,float,json,array'],
            'options' => ['nullable', 'array'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        $value = $request->input('value', $setting->value);
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $setting->update([
            'key' => $request->input('key', $setting->key),
            'value' => $value,
            'group' => $request->input('group', $setting->group),
            'type' => $request->input('type', $setting->type),
            'options' => $request->input('options', $setting->options),
            'is_public' => $request->input('is_public', $setting->is_public),
        ]);

        return response()->json([
            'message' => 'Paramètre mis à jour avec succès.',
            'data' => new SettingResource($setting),
        ]);
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['nullable'],
        ]);

        foreach ($request->input('settings') as $item) {
            $value = $item['value'];
            if (is_array($value)) {
                $value = json_encode($value);
            }

            Setting::updateOrCreate(
                ['key' => $item['key']],
                [
                    'value' => $value,
                    'group' => $item['group'] ?? 'general',
                    'type' => $item['type'] ?? 'string',
                ]
            );
        }

        return response()->json([
            'message' => 'Paramètres mis à jour avec succès.',
        ]);
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(string $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json([
            'message' => 'Paramètre supprimé avec succès.',
        ]);
    }

    /**
     * Get available setting groups.
     */
    public function groups(): JsonResponse
    {
        $groups = Setting::select('group')
            ->distinct()
            ->pluck('group')
            ->sort()
            ->values();

        return response()->json([
            'data' => $groups,
        ]);
    }
}
