<?php

test('walk in button uses an isolated modal component', function () {
    $projectRoot = dirname(__DIR__, 2);
    $view = file_get_contents($projectRoot.'/resources/views/admin/reservations.blade.php');
    $javascript = file_get_contents($projectRoot.'/resources/js/app.js');

    expect($view)
        ->toContain('data-walk-in-workspace')
        ->toContain('x-data="walkInReservation({')
        ->toContain('data-walk-in-trigger')
        ->toContain('@click.prevent="openWalkIn()"')
        ->toContain('data-walk-in-modal')
        ->toContain('x-show="walkInOpen"')
        ->and($javascript)
        ->toContain("Alpine.data('walkInReservation'")
        ->toContain('openWalkIn()')
        ->toContain('this.walkInOpen = true');
});
