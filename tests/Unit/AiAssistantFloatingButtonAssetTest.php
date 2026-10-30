<?php

test('AI assistant uses one fixed floating action button outside workspace headers', function () {
    $projectRoot = dirname(__DIR__, 2);
    $assistant = file_get_contents($projectRoot.'/resources/views/components/ai-assistant.blade.php');
    $userShell = file_get_contents($projectRoot.'/resources/views/components/user/shell.blade.php');
    $adminShell = file_get_contents($projectRoot.'/resources/views/components/admin/shell.blade.php');
    $css = file_get_contents($projectRoot.'/resources/css/app.css');

    expect($assistant)
        ->toContain('data-ai-assistant-fab')
        ->toContain('@click="openAssistant()"')
        ->toContain('ai-assistant-fab__button')
        ->toContain('stroke="currentColor"')
        ->not->toContain('linearGradient')
        ->not->toContain('boardmatch-ai-flower')
        ->not->toContain('absolute -right-0.5 -top-0.5')
        ->and($adminShell)
        ->not->toContain('admin-header-ai')
        ->and($css)
        ->toContain('position: fixed;')
        ->toContain('background: #f4f0ff;')
        ->toContain('border-radius: 0.875rem;')
        ->toContain('color: #7c3aed;')
        ->toContain('height: 3.25rem;')
        ->toContain('width: 3.25rem;')
        ->toContain('height: 1.8125rem;')
        ->toContain('width: 1.8125rem;')
        ->toContain('z-index: 1000;')
        ->toContain('env(safe-area-inset-bottom, 0px)');

    expect(substr_count($userShell, '<x-ai-assistant />'))->toBe(1)
        ->and(strrpos($userShell, '<x-ai-assistant />'))->toBeGreaterThan(strpos($userShell, '</main>'))
        ->and(substr_count($adminShell, '<x-ai-assistant />'))->toBe(1)
        ->and(strrpos($adminShell, '<x-ai-assistant />'))->toBeGreaterThan(strpos($adminShell, '</main>'));
});
