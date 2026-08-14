<?php

test('rooms table identifies the boarding house assigned to every room', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/rooms.blade.php');

    expect($view)
        ->toContain('<th class="px-5 py-3 text-left">Boarding House</th>')
        ->toContain("{{ \$room->boardingHouse?->name ?? 'Unassigned' }}")
        ->toContain('id="boarding-house-category"')
        ->toContain('name="boarding_house_id"')
        ->toContain("'All My Boarding Houses'")
        ->toContain('onchange="this.form.requestSubmit()"')
        ->toContain('colspan="7"');
});
