<?php

use App\Models\BoardingHouse;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentWordDocumentService;
test('payment service creates a valid printable word document', function () {
    $user = new User([
        'name' => 'Test Tenant',
        'email' => 'tenant@example.com',
    ]);
    $tenant = new Tenant;
    $tenant->setRelation('user', $user);

    $house = new BoardingHouse;
    $house->setRawAttributes([
        'name' => 'Test Boarding House',
        'full_address' => 'Matti, Digos City, Davao del Sur',
    ], true);

    $payment = new Payment;
    $payment->setRawAttributes([
        'id' => 1,
        'amount' => 3500,
        'status' => 'paid',
        'payment_method' => 'paymongo',
        'reference_number' => 'PM-TEST-001',
        'receipt_number' => 'RCT-TEST-001',
        'due_date' => '2026-08-15',
        'paid_at' => '2026-08-13 10:30:00',
        'notes' => 'Monthly boarding payment.',
    ], true);
    $payment->setRelation('tenant', $tenant);
    $payment->setRelation('boardingHouse', $house);
    $payment->setRelation('receipts', collect());

    $document = app(PaymentWordDocumentService::class)->create($payment);

    try {
        expect($document['filename'])
            ->toEndWith('.docx')
            ->and(is_file($document['path']))->toBeTrue()
            ->and(filesize($document['path']))->toBeGreaterThan(1000);

        $zip = new \ZipArchive;
        expect($zip->open($document['path']))->toBeTrue();

        $parts = [
            '[Content_Types].xml',
            '_rels/.rels',
            'docProps/core.xml',
            'docProps/app.xml',
            'word/document.xml',
            'word/styles.xml',
            'word/settings.xml',
            'word/_rels/document.xml.rels',
        ];

        foreach ($parts as $part) {
            $xml = $zip->getFromName($part);
            expect($xml)->not->toBeFalse();

            $dom = new DOMDocument;
            expect($dom->loadXML($xml))->toBeTrue();
        }

        $content = $zip->getFromName('word/document.xml');
        expect($content)
            ->toContain('Payment Receipt')
            ->toContain('Test Tenant')
            ->toContain('Test Boarding House')
            ->toContain('PHP 3,500.00')
            ->toContain('PM-TEST-001');

    } finally {
        if (isset($zip) && $zip instanceof \ZipArchive) {
            $zip->close();
        }

        if (is_file($document['path'])) {
            unlink($document['path']);
        }
    }
});
