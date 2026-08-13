<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $isPaid = strtolower((string) $payment->status) === 'paid';
        $documentNumber = $payment->receipt_number ?: 'PAY-'.now()->format('Y').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        $tenantName = $payment->tenant?->user?->name ?: 'Tenant';
        $tenantEmail = $payment->tenant?->user?->email;
        $houseName = $payment->boardingHouse?->name ?: 'Boarding house';
        $houseAddress = $payment->boardingHouse?->full_address ?: $payment->boardingHouse?->address;
        $statusLabel = str($payment->status ?: 'pending')->headline();
        $methodLabel = str($payment->payment_method ?: 'cash')->headline();
        $reference = $payment->reference_number ?: $payment->reference_no;
        $recordedAt = $payment->paid_at ?: $payment->created_at;
    @endphp
    <title>{{ $isPaid ? 'Payment Receipt' : 'Payment Statement' }} {{ $documentNumber }}</title>
    <style>
        :root{color-scheme:light}*{box-sizing:border-box}body{margin:0;background:#e2e8f0;color:#0f172a;font-family:Inter,Arial,sans-serif}.toolbar{display:flex;justify-content:flex-end;gap:10px;max-width:820px;margin:24px auto 12px;padding:0 16px}.toolbar button{border:0;border-radius:10px;background:#2563eb;color:#fff;cursor:pointer;font-size:14px;font-weight:700;padding:11px 18px}.toolbar button.secondary{background:#fff;color:#334155;border:1px solid #cbd5e1}.document{background:#fff;box-shadow:0 20px 55px rgba(15,23,42,.14);margin:0 auto 32px;max-width:820px;min-height:1040px;padding:54px 58px}.brand{align-items:center;display:flex;gap:14px}.brand img{border-radius:12px;height:48px;width:48px}.brand-name{font-size:22px;font-weight:800;letter-spacing:-.02em}.brand-subtitle{color:#64748b;font-size:12px;margin-top:3px}.heading{align-items:flex-end;border-bottom:2px solid #0f172a;display:flex;justify-content:space-between;margin-top:42px;padding-bottom:18px}.heading h1{font-size:30px;letter-spacing:-.04em;margin:0}.eyebrow{color:#2563eb;font-size:11px;font-weight:800;letter-spacing:.16em;margin:0 0 7px;text-transform:uppercase}.document-no{font-size:12px;text-align:right}.document-no span{color:#64748b;display:block;margin-bottom:4px}.status{border-radius:999px;display:inline-block;font-size:12px;font-weight:800;margin-top:8px;padding:6px 11px}.status.paid{background:#dcfce7;color:#047857}.status.outstanding{background:#fff7ed;color:#c2410c}.parties{display:grid;gap:36px;grid-template-columns:1fr 1fr;margin-top:32px}.section-label{color:#64748b;font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.party-name{font-size:16px;font-weight:800;margin-top:8px}.party-detail{color:#64748b;font-size:13px;line-height:1.6;margin-top:4px}.details{border:1px solid #e2e8f0;border-radius:14px;margin-top:32px;overflow:hidden}.row{align-items:flex-start;border-bottom:1px solid #e2e8f0;display:flex;gap:20px;justify-content:space-between;padding:14px 18px}.row:last-child{border-bottom:0}.label{color:#64748b;font-size:13px}.value{font-size:13px;font-weight:700;text-align:right}.total{background:#eff6ff;padding:22px 18px}.total .label,.total .value{color:#1d4ed8;font-size:22px;font-weight:900}.notes{background:#f8fafc;border-radius:12px;margin-top:24px;padding:16px 18px}.notes p{color:#475569;font-size:13px;line-height:1.6;margin:7px 0 0;white-space:pre-wrap}.footer{border-top:1px solid #e2e8f0;color:#64748b;font-size:11px;line-height:1.6;margin-top:42px;padding-top:18px}.signature{display:grid;gap:48px;grid-template-columns:1fr 1fr;margin-top:64px}.signature-line{border-top:1px solid #64748b;padding-top:8px;text-align:center}.signature-line strong{display:block;font-size:12px}.signature-line span{color:#64748b;font-size:11px}.embedded{padding:24px}.embedded .toolbar{display:none}.embedded .document{box-shadow:0 8px 30px rgba(15,23,42,.1);margin:0 auto;min-height:980px}@media(max-width:640px){.document{margin:0;min-height:100vh;padding:30px 22px}.heading,.parties{display:block}.document-no{text-align:left;margin-top:18px}.parties>div+div{margin-top:24px}.signature{grid-template-columns:1fr}.toolbar{margin-top:12px}.embedded{padding:10px}.embedded .document{min-height:0}}@page{size:A4;margin:14mm}@media print{body{background:#fff}.toolbar{display:none!important}.document{box-shadow:none;margin:0;max-width:none;min-height:0;padding:0}.heading{margin-top:30px}.signature{margin-top:48px}}
    </style>
</head>
<body class="{{ $embedded ?? false ? 'embedded' : '' }}">
    <div class="toolbar" data-document-actions>
        <button type="button" class="secondary" onclick="window.close()">Close</button>
        <a href="{{ $wordDownloadUrl }}" style="border:1px solid #bfdbfe;border-radius:10px;background:#eff6ff;color:#1d4ed8;font-size:14px;font-weight:700;padding:10px 18px;text-decoration:none">Download Word (.docx)</a>
        <button type="button" onclick="window.print()">Print Document</button>
    </div>

    <main class="document" data-payment-document>
        <header class="brand">
            <img src="{{ asset('images/boardmatch-final-logo.png') }}" alt="BoardMatch logo">
            <div>
                <div class="brand-name">BoardMatch</div>
                <div class="brand-subtitle">Boarding House Management System</div>
            </div>
        </header>

        <section class="heading">
            <div>
                <p class="eyebrow">Official Payment Document</p>
                <h1>{{ $isPaid ? 'Payment Receipt' : 'Payment Statement' }}</h1>
            </div>
            <div class="document-no">
                <span>Document number</span>
                <strong>{{ $documentNumber }}</strong><br>
                <span class="status {{ $isPaid ? 'paid' : 'outstanding' }}">{{ $statusLabel }}</span>
            </div>
        </section>

        <section class="parties">
            <div>
                <div class="section-label">Issued To</div>
                <div class="party-name">{{ $tenantName }}</div>
                @if ($tenantEmail)<div class="party-detail">{{ $tenantEmail }}</div>@endif
            </div>
            <div>
                <div class="section-label">Boarding House</div>
                <div class="party-name">{{ $houseName }}</div>
                @if ($houseAddress)<div class="party-detail">{{ $houseAddress }}</div>@endif
            </div>
        </section>

        <section class="details">
            <div class="row"><span class="label">Payment status</span><span class="value">{{ $statusLabel }}</span></div>
            <div class="row"><span class="label">Payment method</span><span class="value">{{ $methodLabel }}</span></div>
            <div class="row"><span class="label">Reference number</span><span class="value">{{ $reference ?: 'Not provided' }}</span></div>
            <div class="row"><span class="label">Due date</span><span class="value">{{ $payment->due_date?->format('F d, Y') ?: 'Not set' }}</span></div>
            <div class="row"><span class="label">{{ $isPaid ? 'Payment date' : 'Recorded date' }}</span><span class="value">{{ $recordedAt?->format('F d, Y h:i A') ?: 'Not recorded' }}</span></div>
            <div class="row total"><span class="label">{{ $isPaid ? 'Total Paid' : 'Amount Due' }}</span><span class="value">PHP {{ number_format((float) $payment->amount, 2) }}</span></div>
        </section>

        @if (filled($payment->notes))
            <section class="notes">
                <div class="section-label">Notes</div>
                <p>{{ $payment->notes }}</p>
            </section>
        @endif

        <section class="signature">
            <div class="signature-line"><strong>{{ $tenantName }}</strong><span>Tenant acknowledgment</span></div>
            <div class="signature-line"><strong>Authorized Representative</strong><span>Boarding house / BoardMatch</span></div>
        </section>

        <footer class="footer">
            Generated by BoardMatch on {{ now()->format('F d, Y h:i A') }}.
            @if (! $isPaid) This statement is not proof of payment until its status is marked Paid. @else Keep this receipt for your records. @endif
        </footer>
    </main>

    @if ($autoPrint)
        <script>window.addEventListener('load', () => window.setTimeout(() => window.print(), 150));</script>
    @endif
</body>
</html>
