<?php

test('owner applications summarize properties in the table and render full details in the modal', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/users.blade.php');

    expect($view)
        ->toContain('data-owner-property-summary="{{ $submittedHouseCount }}"')
        ->toContain('Open the application to view all properties.')
        ->toContain('template x-for="house in (selected.houses || [])"')
        ->toContain('<dt>Boarding Houses</dt>');
});
