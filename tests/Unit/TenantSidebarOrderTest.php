<?php

test('tenant sidebar places preferences before matchmaking', function () {
    $projectRoot = dirname(__DIR__, 2);
    $sidebar = file_get_contents($projectRoot.'/resources/views/components/sidebar/user-panel.blade.php');

    expect(strpos($sidebar, "'label' => 'My Preferences'"))
        ->toBeLessThan(strpos($sidebar, "'label' => 'Matchmaking'"));
});
