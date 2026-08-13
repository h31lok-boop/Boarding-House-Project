<?php

test('tenant message badge is database driven instead of hardcoded', function () {
    $projectRoot = dirname(__DIR__, 2);
    $sidebar = file_get_contents($projectRoot.'/resources/views/components/sidebar/user-panel.blade.php');

    expect($sidebar)
        ->not->toContain("'badge' => '2'")
        ->toContain("'badge' => \$messageBadge")
        ->toContain("->where('user_id', auth()->id())")
        ->toContain("->where('type', 'inquiry')")
        ->toContain("->whereIn('reference_id', \$inquiryReferences)")
        ->toContain('$messageBadge = $unreadMessageCount > 0');
});
