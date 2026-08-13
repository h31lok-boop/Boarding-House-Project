<?php

test('role account dropdowns contain only profile and logout actions', function () {
    $projectRoot = dirname(__DIR__, 2);
    $adminShell = file_get_contents($projectRoot.'/resources/views/components/admin/shell.blade.php');
    $userShell = file_get_contents($projectRoot.'/resources/views/components/user/shell.blade.php');
    $userDashboard = file_get_contents($projectRoot.'/resources/views/user/dashboard.blade.php');

    expect($adminShell)
        ->not->toContain('>Profile Management</a>')
        ->not->toContain('>Security Settings</a>')
        ->toContain('role="menuitem">Profile</a>')
        ->toContain('role="menuitem">Logout</button>')
        ->and($userShell)
        ->not->toContain('>Match Preferences</a>')
        ->toContain('>Profile</a>')
        ->toContain('>Log out</button>')
        ->and($userDashboard)
        ->not->toContain('>My Profile</a>')
        ->not->toContain('>Account Settings</a>')
        ->not->toContain('profileOpen');
});
