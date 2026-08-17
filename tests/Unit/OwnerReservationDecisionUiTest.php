<?php

test('pending reservations expose visible accept and reject actions to owners', function () {
    $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/admin/reservations.blade.php');

    expect($view)
        ->toContain('$acceptReservationLabel = $workspace === \'owner\' ? \'Accept\' : \'Approve\'')
        ->toContain('<th class="px-5 py-3.5">Actions</th>')
        ->toContain('@click.stop="askConfirm({{ \\Illuminate\\Support\\Js::from($confirmApprove) }})"')
        ->toContain('@click.stop="askConfirm({{ \\Illuminate\\Support\\Js::from($confirmReject) }})"')
        ->toContain('{{ $acceptReservationLabel }}')
        ->toContain('Reject')
        ->not->toContain('<td class="hidden">');
});
