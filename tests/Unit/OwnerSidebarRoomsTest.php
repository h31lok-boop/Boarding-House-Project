<?php

test('owner sidebar displays rooms after properties and highlights the rooms route', function () {
    $projectRoot = dirname(__DIR__, 2);
    $sidebar = file_get_contents($projectRoot.'/resources/views/components/sidebar/admin-panel.blade.php');

    $propertiesPosition = strpos($sidebar, "'key' => 'boarding-houses'");
    $roomsPosition = strpos($sidebar, "'key' => 'rooms'");
    $reservationsPosition = strpos($sidebar, "'key' => 'reservations'");

    expect($propertiesPosition)->not->toBeFalse()
        ->and($roomsPosition)->not->toBeFalse()
        ->and($reservationsPosition)->not->toBeFalse()
        ->and($roomsPosition)->toBeGreaterThan($propertiesPosition)
        ->and($roomsPosition)->toBeLessThan($reservationsPosition)
        ->and($sidebar)->toContain("'href' => \$r('owner.rooms', ['owner' => 'mine'])")
        ->and($sidebar)->toContain("request()->routeIs('owner.rooms', 'owner.rooms.*')");
});
