<?php

test('admin and owner payments provide printable document routes and actions', function () {
    $projectRoot = dirname(__DIR__, 2);
    $routes = file_get_contents($projectRoot.'/routes/web.php');
    $payments = file_get_contents($projectRoot.'/resources/views/admin/payments.blade.php');
    $document = file_get_contents($projectRoot.'/resources/views/admin/payment-document.blade.php');

    $wordService = file_get_contents($projectRoot.'/app/Services/PaymentWordDocumentService.php');

    expect(substr_count($routes, "name('payments.document')"))->toBe(2)
        ->and(substr_count($routes, "name('payments.document.word')"))->toBe(2)
        ->and($payments)
        ->toContain("'document_url' =>")
        ->toContain("'document_print_url' =>")
        ->toContain("'document_word_url' =>")
        ->toContain('>Download Word (.docx)</a>')
        ->toContain('>Preview</a>')
        ->toContain('>Print</a>')
        ->and($document)
        ->toContain('data-payment-document')
        ->toContain('onclick="window.print()"')
        ->toContain('@media print')
        ->toContain('Download Word (.docx)')
        ->toContain("\$isPaid ? 'Payment Receipt' : 'Payment Statement'")
        ->toContain('This statement is not proof of payment until its status is marked Paid.')
        ->and($wordService)
        ->toContain('class PaymentWordDocumentService')
        ->toContain('ZipArchive::CREATE | ZipArchive::OVERWRITE')
        ->toContain('application/vnd.openxmlformats-officedocument.wordprocessingml.document')
        ->toContain('Payment Receipt')
        ->toContain('Payment Statement');
});
