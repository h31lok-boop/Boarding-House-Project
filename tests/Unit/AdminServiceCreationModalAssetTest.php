<?php

test('admin and owner service creation use a button and centered modal', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/services-content.blade.php');

    expect($view)
        ->toContain('data-add-service-trigger')
        ->toContain('data-create-service-modal')
        ->toContain('class="bm-modal-overlay"')
        ->toContain('class="bm-modal bm-modal--lg"')
        ->toContain('name="form_context" value="create_service"')
        ->toContain("old('form_context') === 'create_service'")
        ->toContain('bm-service-create-body')
        ->toContain('bm-service-create-grid')
        ->toContain('bm-service-description')
        ->toContain('>Create Service</button>')
        ->not->toContain("@if (\$namespace === 'admin')")
        ->not->toContain('<h2 class="text-sm font-bold text-slate-950">Create a service</h2>');
});
