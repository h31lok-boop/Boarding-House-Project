<?php

test('AI database context uses an explicit safe field allowlist', function () {
    $source = file_get_contents(dirname(__DIR__, 2).'/app/Services/BoardMatchAiContextService.php');

    expect($source)
        ->toContain('read-only, role-scoped snapshot')
        ->toContain('MAX_CONTEXT_CHARACTERS')
        ->not->toContain("select('*')")
        ->not->toContain("'password',")
        ->not->toContain("'password_hash',")
        ->not->toContain("'remember_token',")
        ->not->toContain("'paymongo_secret_key',")
        ->not->toContain("'paymongo_webhook_secret',")
        ->not->toContain("'gcash_api_key',");
});
