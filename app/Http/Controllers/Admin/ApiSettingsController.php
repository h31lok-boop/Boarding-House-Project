<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\IntegrationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiSettingsController extends Controller
{
    public function index(Request $request, IntegrationSettingsService $settings): View
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return view('admin.api-settings', [
            'integrationGroups' => $settings->groupsForAdmin(),
        ]);
    }

    public function update(Request $request, IntegrationSettingsService $settings): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate(array_merge([
            'settings' => ['nullable', 'array'],
            'reset' => ['nullable', 'array'],
            'reset.*' => ['nullable', 'boolean'],
        ], $settings->validationRules()));

        $resetKeys = collect($validated['reset'] ?? [])
            ->filter(fn (mixed $value) => filter_var($value, FILTER_VALIDATE_BOOL))
            ->keys()
            ->all();

        $settings->save($validated['settings'] ?? [], $resetKeys);

        return redirect()
            ->route('admin.api-settings.index')
            ->with('success', 'API settings saved. The new configuration is now active.');
    }
}
