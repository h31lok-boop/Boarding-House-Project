<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Receipt {{ $receipt->receipt_number ?: '#'.$receipt->id }}</title>
    <style>
        body{font-family:Arial,sans-serif;background:#f1f5f9;color:#0f172a;margin:0;padding:32px}.receipt{max-width:640px;margin:auto;background:#fff;padding:32px;border-radius:16px;box-shadow:0 10px 30px #0f172a12}.row{display:flex;justify-content:space-between;gap:24px;padding:10px 0;border-bottom:1px solid #e2e8f0}.label{color:#64748b;font-size:13px}.value{font-weight:700;text-align:right}.total{font-size:22px;color:#2563eb;border-bottom:0}.actions{max-width:640px;margin:16px auto;text-align:right}button,a{border:0;border-radius:10px;padding:10px 16px;font-weight:700;text-decoration:none;background:#2563eb;color:#fff;cursor:pointer}@media print{body{background:#fff;padding:0}.receipt{box-shadow:none;border-radius:0}.actions{display:none}}
    </style>
</head>
<body>
    <div class="actions"><button onclick="window.print()">Print receipt</button></div>
    <main class="receipt">
        <p style="letter-spacing:.14em;text-transform:uppercase;color:#2563eb;font-weight:700;font-size:12px">BoardMatch payment receipt</p>
        <h1 style="margin:8px 0 24px">Payment received</h1>
        <div class="row"><span class="label">Receipt number</span><span class="value">{{ $receipt->receipt_number ?: 'RCT-'.$receipt->id }}</span></div>
        <div class="row"><span class="label">Tenant</span><span class="value">{{ $receipt->user?->name ?: 'Tenant' }}</span></div>
        <div class="row"><span class="label">Payment date</span><span class="value">{{ $receipt->payment_date?->format('M d, Y') ?: '—' }}</span></div>
        <div class="row"><span class="label">Payment method</span><span class="value">Cash Payment</span></div>
        <div class="row"><span class="label">Reference</span><span class="value">{{ $receipt->reference_number ?: '—' }}</span></div>
        <div class="row total"><span>Total paid</span><span>₱{{ number_format((float) $receipt->amount, 2) }}</span></div>
        <p style="color:#64748b;font-size:12px;margin-top:24px">Status: {{ $receipt->status_label }}. Keep this receipt for your records.</p>
    </main>
</body>
</html>
