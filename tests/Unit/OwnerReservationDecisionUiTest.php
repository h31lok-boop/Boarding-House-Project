<?php

test('pending reservations expose visible accept and reject actions to owners', function () {
    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/reservations.blade.php');

    expect($view)
        ->toContain('$acceptReservationLabel = $workspace === \'owner\' ? \'Accept\' : \'Approve\'')
        ->toContain('<th class="px-5 py-3.5">Actions</th>')
        ->toContain('@click.stop="askConfirm({{ \\Illuminate\\Support\\Js::from($confirmApprove) }})"')
        ->toContain('@click.stop="askConfirm({{ \\Illuminate\\Support\\Js::from($confirmReject) }})"')
        ->toContain('{{ $acceptReservationLabel }}')
        ->toContain('Accepted')
        ->toContain("x-show=\"['pending', 'approved', 'confirmed'].includes(selected.status_value)\"")
        ->toContain('Reject')
        ->not->toContain('<td class="hidden">');
});

test('reservation interactions use html safe alpine configuration', function () {
    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/reservations.blade.php');

    expect($view)
        ->toContain('availableRoomsUrlTemplate: @js($route(\'api.boarding-houses.available-rooms\'')
        ->not->toContain('availableRoomsUrlTemplate: @json($route(\'api.boarding-houses.available-rooms\'');
});
