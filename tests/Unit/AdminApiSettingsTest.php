<?php

use App\Services\IntegrationSettingsService;
use Tests\TestCase;

uses(TestCase::class);

it('defines admin routes and an encrypted integration settings store', function () {
    $projectRoot = dirname(__DIR__, 2);
    $routes = file_get_contents($projectRoot.'/routes/web.php');
    $controller = file_get_contents($projectRoot.'/app/Http/Controllers/Admin/ApiSettingsController.php');
    $model = file_get_contents($projectRoot.'/app/Models/IntegrationSetting.php');
    $provider = file_get_contents($projectRoot.'/app/Providers/AppServiceProvider.php');

    expect($routes)
        ->toContain("Route::get('/api-settings'")
        ->toContain("Route::put('/api-settings'")
        ->and($controller)
        ->toContain('isSuperAdmin()')
        ->and($model)
        ->toContain("'value' => 'encrypted'")
        ->and($provider)
        ->toContain('applyToRuntimeConfig()');

    $indexRoute = app('router')->getRoutes()->getByName('admin.api-settings.index');
    $updateRoute = app('router')->getRoutes()->getByName('admin.api-settings.update');

    expect($indexRoute->gatherMiddleware())
        ->toContain('auth', 'verified', 'admin')
        ->and($updateRoute->gatherMiddleware())
        ->toContain('auth', 'verified', 'admin');
});

it('never sends configured secret values back to the admin form', function () {
    config()->set('services.groq.api_key', 'gsk-must-never-render');
    config()->set('services.groq.base_url', 'https://api.groq.com/openai/v1');

    $groups = app(IntegrationSettingsService::class)->groupsForAdmin();
    $fields = collect($groups)->flatMap(fn (array $group) => $group['fields'])->keyBy('key');

    expect($fields['groq_api_key']['configured'])->toBeTrue()
        ->and($fields['groq_api_key']['value'])->toBe('')
        ->and($fields['groq_api_key']['source'])->toBe('.env fallback')
        ->and($fields['groq_base_url']['value'])->toBe('https://api.groq.com/openai/v1');
});

it('exposes every active integration area in one admin view', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/api-settings.blade.php');
    $service = file_get_contents($projectRoot.'/app/Services/IntegrationSettingsService.php');

    expect($view)
        ->toContain('API & integration settings')
        ->toContain('Existing credentials are never displayed')
        ->toContain('Remove saved override and use .env')
        ->and($service)
        ->toContain("'title' => 'AI providers'")
        ->toContain("'title' => 'Google services'")
        ->toContain("'title' => 'Email delivery'");
});

it('marks the configured provider and integrations that are used by the system', function () {
    config()->set('services.ai_evaluation.provider', 'groq');
    config()->set('services.groq.enabled', true);
    config()->set('services.groq.api_key', 'test-groq-key');
    $groups = collect(app(IntegrationSettingsService::class)->groupsForAdmin())->keyBy('key');

    expect($groups['ai']['runtime_status'])
        ->toMatchArray(['active' => true, 'label' => 'Groq in use']);

    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/api-settings.blade.php');

    expect($view)
        ->toContain('data-integration-runtime-status')
        ->toContain("\$group['runtime_status']['label']");
});
