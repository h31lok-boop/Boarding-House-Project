<?php

test('admin service creation uses a button and centered modal', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/services-content.blade.php');

    expect($view)
        ->toContain('data-add-service-trigger')
        ->toContain('data-create-service-modal')
        ->toContain('class="bm-modal-overlay"')
        ->toContain('class="bm-modal bm-modal--lg"')
        ->toContain('name="form_context" value="create_service"')
        ->toContain("old('form_context') === 'create_service'")
        ->toContain('>Create Service</button>');
});
