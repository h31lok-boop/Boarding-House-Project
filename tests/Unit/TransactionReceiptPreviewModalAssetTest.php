<?php

test('transaction receipts open in a same-page document preview modal', function () {
    $projectRoot = dirname(__DIR__, 2);
    $transactions = file_get_contents($projectRoot.'/resources/views/admin/transactions.blade.php');
    $controller = file_get_contents($projectRoot.'/app/Http/Controllers/AdminOwnerController.php');
    $document = file_get_contents($projectRoot.'/resources/views/admin/payment-document.blade.php');
    $css = file_get_contents($projectRoot.'/resources/css/app.css');

    expect($transactions)
        ->not->toContain('<a href="#" class="inline-flex h-7')
        ->not->toContain('>View</a>')
        ->not->toContain('>Receipt</button>')
        ->not->toContain('>Action</th>')
        ->toContain('data-transaction-row-receipt-preview')
        ->toContain('aria-label="Preview {{ $receiptPayload')
        ->toContain('@keydown.enter.prevent="openReceipt(')
        ->toContain('@keydown.space.prevent="openReceipt(')
        ->toContain('openReceipt(')
        ->toContain('data-modal-root')
        ->toContain('bm-modal--document-preview')
        ->toContain('bm-document-preview-frame')
        ->toContain("'embedded' => 1")
        ->toContain('Download Word (.docx)')
        ->toContain('Print Receipt')
        ->and($controller)
        ->toContain("'embedded' => \$request->boolean('embedded')")
        ->and($document)
        ->toContain("\$embedded ?? false ? 'embedded' : ''")
        ->toContain('.embedded .toolbar{display:none}')
        ->and($css)
        ->toContain('.bm-modal--document-preview')
        ->toContain('.bm-document-preview-frame');
});
