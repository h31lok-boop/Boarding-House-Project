@props([
    'receipt' => null,
    'bookings' => collect(),
    'action' => '#',
    'inputId' => 'receipt-upload',
    'loading' => false,
])

@php
    $initialPaymentMethod = old('payment_method', 'GCash');
    $receiptStatus = $receipt?->status_label ?? null;
    $receiptHasFile = filled($receipt?->receipt_path);
    $receiptUrl = $receipt && $receiptHasFile ? route('payment-receipts.show', $receipt) : null;
    $receiptDownloadUrl = $receipt && $receiptHasFile ? route('payment-receipts.download', $receipt) : null;
    $receiptDeleteUrl = $receipt ? route('user.payment-receipts.destroy', $receipt) : null;
    $receiptName = $receipt?->original_filename ?: ($receipt?->receipt_path ? basename($receipt->receipt_path) : 'Cash payment submission');
@endphp

<section
    x-data="paymentReceiptUpload({{ \Illuminate\Support\Js::from([
        'maxSize' => 5 * 1024 * 1024,
        'allowedTypes' => ['image/jpeg', 'image/png', 'application/pdf'],
        'allowedExtensions' => ['jpg', 'jpeg', 'png', 'pdf'],
        'initialPaymentMethod' => $initialPaymentMethod,
        'existing' => $receipt ? [
            'hasFile' => $receiptHasFile,
            'name' => $receiptName,
            'url' => $receiptUrl,
            'downloadUrl' => $receiptDownloadUrl,
            'deleteUrl' => $receiptDeleteUrl,
            'uploadedAt' => $receipt->created_at?->format('M d, Y - h:i A'),
            'status' => $receiptStatus,
            'statusKey' => $receipt->status,
            'isImage' => $receiptHasFile && $receipt->is_image,
            'paymentMethod' => $receipt->payment_method,
            'amount' => number_format((float) $receipt->amount, 2),
            'referenceNumber' => $receipt->reference_number,
            'transactionId' => $receipt->transaction_id,
            'rejectionReason' => $receipt->rejection_reason,
        ] : null,
    ]) }})"
    {{ $attributes->merge(['class' => 'rounded-2xl border border-blue-100 bg-white p-4 shadow-md shadow-blue-100/40 ring-1 ring-inset ring-blue-50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none dark:ring-0']) }}
>
    <div class="mb-3.5 flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-sm shadow-blue-500/30">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5A4.5 4.5 0 0 1 6.75 10.5a5.25 5.25 0 0 1 10.335-1.284A4.125 4.125 0 0 1 18 17.25h-.75" />
            </svg>
        </span>
        <div>
            <h2 class="text-sm font-bold text-slate-950 dark:text-white">Upload Proof of Payment</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Choose a payment method, enter the details, and submit your receipt for review.</p>
        </div>
    </div>

    @if ($loading)
        <div class="grid animate-pulse gap-3.5 lg:grid-cols-[minmax(0,1.1fr)_minmax(240px,0.9fr)]">
            <div class="h-52 rounded-xl bg-slate-100 dark:bg-slate-800"></div>
            <div class="h-52 rounded-xl bg-slate-100 dark:bg-slate-800"></div>
        </div>
    @else
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" @submit="if (!canSubmit()) { $event.preventDefault() } else { submitting = true }" class="space-y-3.5">
            @csrf

            <div x-show="isCashPayment()" x-cloak class="rounded-xl border border-amber-200 bg-amber-50 p-2.5 text-xs text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                <div class="flex gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 4.5h.008v.008H12v-.008Z" />
                    </svg>
                    <p class="font-semibold">You selected Cash Payment. Please visit the boarding house and hand your payment directly to the landlord. The payment will be recorded once confirmed by the landlord.</p>
                </div>
            </div>

            <div class="grid gap-3.5 lg:grid-cols-[minmax(0,1.15fr)_minmax(240px,0.85fr)]">
                <div
                    x-show="requiresReceipt()"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="handleDrop($event)"
                    :class="dragging ? 'border-[#2563eb] bg-blue-50/70 dark:bg-blue-400/10' : 'border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/40'"
                    class="flex min-h-48 flex-col items-center justify-center rounded-xl border-2 border-dashed p-4 text-center transition duration-200 hover:border-[#2563eb]/60 hover:bg-blue-50/40 dark:hover:bg-blue-400/5"
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-[#2563eb] shadow-sm dark:bg-slate-900 dark:text-blue-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5A4.5 4.5 0 0 1 6.75 10.5a5.25 5.25 0 0 1 10.335-1.284A4.125 4.125 0 0 1 18 17.25h-.75" />
                        </svg>
                    </div>
                    <p class="mt-2.5 text-xs font-semibold text-slate-950 dark:text-white">Drag and drop your receipt here</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Accepts JPG, JPEG, PNG, or PDF files</p>
                    <input id="{{ $inputId }}" x-ref="fileInput" name="receipt" type="file" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" :disabled="!requiresReceipt()" @change="handleFile($event.target.files[0])">
                    <label for="{{ $inputId }}" class="mt-3.5 inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-lg bg-[#2563eb] px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition duration-200 hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/30">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5h10.5A2.25 2.25 0 0 0 19.5 17.25v-1.5M4.5 15.75v1.5A2.25 2.25 0 0 0 6.75 19.5" />
                        </svg>
                        <span x-text="file ? 'Replace File' : 'Upload Receipt'"></span>
                    </label>
                    <p class="mt-2.5 text-[11px] font-medium text-slate-400 dark:text-slate-500">Maximum file size: 5 MB</p>

                    <template x-if="fileError">
                        <p class="mt-3 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/20" x-text="fileError"></p>
                    </template>
                    @error('receipt')
                        <p class="mt-3 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-red-200 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/20">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="isCashPayment()" x-cloak class="flex min-h-48 flex-col justify-center rounded-xl border border-amber-200 bg-amber-50 p-3.5 dark:border-amber-400/20 dark:bg-amber-400/10">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-amber-600 shadow-sm dark:bg-slate-900 dark:text-amber-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5v10.5H2.25V8.25Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12h.008v.008H6.75V12Zm10.5 3h.008v.008h-.008V15ZM12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                        </svg>
                    </div>
                    <h3 class="mt-2.5 text-sm font-semibold text-amber-950 dark:text-amber-100">Cash payment selected</h3>
                    <p class="mt-1 text-xs leading-5 text-amber-800 dark:text-amber-200">No receipt upload is required for cash payments. Enter the amount, payment date, and any notes so the landlord can verify it in person.</p>
                </div>

                <div>
                    <p class="mb-2 text-xs font-semibold text-slate-950 dark:text-white" x-text="file ? 'Selected File Preview' : 'Recent Upload Preview'"></p>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-800/40">
                        <template x-if="file">
                            <div>
                                <div class="flex h-28 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                    <template x-if="previewUrl">
                                        <img :src="previewUrl" alt="Receipt preview" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!previewUrl">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-[#2563eb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25A2.25 2.25 0 0 0 6 4.5v15A2.25 2.25 0 0 0 8.25 21.75h7.5A2.25 2.25 0 0 0 18 19.5v-2.25" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 2.25V7.5A1.5 1.5 0 0 0 15 9h4.5" />
                                            </svg>
                                            <p class="mt-2 text-xs font-semibold text-slate-500 dark:text-slate-400">PDF receipt</p>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-2.5 space-y-1.5 text-xs">
                                    <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">File name</span><span class="truncate font-semibold text-slate-950 dark:text-white" x-text="file.name"></span></div>
                                    <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">File size</span><span class="font-semibold text-slate-950 dark:text-white" x-text="formatBytes(file.size)"></span></div>
                                    <div class="flex justify-between gap-3"><span class="text-slate-500 dark:text-slate-400">File type</span><span class="font-semibold text-slate-950 dark:text-white" x-text="file.type || fileExtension(file.name).toUpperCase()"></span></div>
                                </div>
                                <button type="button" @click="removeFile()" class="mt-2.5 inline-flex items-center justify-center rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                    Remove file
                                </button>
                            </div>
                        </template>

                        <template x-if="!file && existing">
                            <div>
                                <div class="flex h-28 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                    <template x-if="existing.hasFile && existing.isImage">
                                        <img :src="existing.url" alt="Latest receipt" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!existing.hasFile || !existing.isImage">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-[#2563eb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25A2.25 2.25 0 0 0 6 4.5v15A2.25 2.25 0 0 0 8.25 21.75h7.5A2.25 2.25 0 0 0 18 19.5v-2.25" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 2.25V7.5A1.5 1.5 0 0 0 15 9h4.5" />
                                            </svg>
                                            <p class="mt-2 max-w-full truncate px-3 text-xs font-semibold text-slate-500 dark:text-slate-400" x-text="existing.name"></p>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-2.5">
                                    <p class="text-xs font-semibold text-slate-950 dark:text-white" x-text="existing.name"></p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-text="existing.uploadedAt"></p>
                                    <span
                                        class="mt-2.5 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold leading-none ring-1 ring-inset"
                                        :class="{
                                            'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20': existing.statusKey === 'pending_review',
                                            'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20': existing.statusKey === 'approved',
                                            'bg-red-50 text-red-700 ring-red-200 dark:bg-red-400/10 dark:text-red-300 dark:ring-red-400/20': existing.statusKey === 'rejected',
                                        }"
                                        x-text="existing.status"
                                    ></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!file && !existing">
                            <div class="flex h-36 items-center justify-center text-center">
                                <div>
                                    <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm dark:bg-slate-900 dark:text-slate-400">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625A3.375 3.375 0 0 0 16.125 8.25h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25A2.25 2.25 0 0 0 6 4.5v15A2.25 2.25 0 0 0 8.25 21.75h7.5A2.25 2.25 0 0 0 18 19.5v-2.25" />
                                        </svg>
                                    </div>
                                    <p class="mt-2 text-xs font-semibold text-slate-950 dark:text-white">No upload yet</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Your latest receipt will appear here.</p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-800 dark:bg-slate-800/40 lg:grid-cols-[minmax(190px,0.7fr)_minmax(0,1.3fr)]">
                <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Payment Method
                    <select
                        name="payment_method"
                        x-model="paymentMethod"
                        @change="handleMethodChange()"
                        required
                        class="mt-1 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                    >
                        @foreach (['GCash', 'Maya', 'Bank Transfer', 'Cash Payment'] as $method)
                            <option value="{{ $method }}" @selected($initialPaymentMethod === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-300">{{ $message }}</span>@enderror
                </label>

                <div class="rounded-xl border border-blue-100 bg-white p-2.5 shadow-sm dark:border-blue-400/20 dark:bg-slate-900">
                    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <div x-show="paymentMethod === 'GCash'" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#2563eb] text-[9px] font-black text-white shadow-sm shadow-blue-500/20">
                                GCash
                            </div>
                            <div x-show="paymentMethod === 'Maya'" x-cloak class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-[9px] font-black text-white shadow-sm shadow-emerald-500/20">
                                Maya
                            </div>
                            <div x-show="paymentMethod === 'Bank Transfer'" x-cloak class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-white shadow-sm dark:bg-slate-700">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 2.25 8.25 12 13.5l9.75-5.25L12 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 10.5v6m4.5-6v6m7.5-6v6m4.5-6v6M3 18.75h18" />
                                </svg>
                            </div>
                            <div x-show="paymentMethod === 'Cash Payment'" x-cloak class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white shadow-sm shadow-amber-500/20">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5v10.5H2.25V8.25Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12h.008v.008H6.75V12Zm10.5 3h.008v.008h-.008V15ZM12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold text-slate-950 dark:text-white" x-text="paymentMethod"></p>
                                <div x-show="paymentMethod === 'GCash'" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    <p>0912 345 6789</p>
                                    <p>Juan Dela Cruz</p>
                                </div>
                                <div x-show="paymentMethod === 'Maya'" x-cloak class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    <p>juan.delacruz@example.com</p>
                                    <p>Juan Dela Cruz</p>
                                </div>
                                <div x-show="paymentMethod === 'Bank Transfer'" x-cloak class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    <p>BDO - **** **** 1234</p>
                                    <p>Juan Dela Cruz</p>
                                </div>
                                <div x-show="paymentMethod === 'Cash Payment'" x-cloak class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    <p>Pay directly at the boarding house</p>
                                    <p>Landlord confirmation required</p>
                                </div>
                            </div>
                        </div>

                        <span
                            class="inline-flex w-fit items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset"
                            :class="isCashPayment()
                                ? 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20'
                                : 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/20'"
                            x-text="isCashPayment() ? 'No receipt required' : 'Receipt required'"
                        ></span>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 rounded-xl border border-slate-200 bg-white p-3.5 dark:border-slate-800 dark:bg-slate-900 md:grid-cols-2">
                <label x-show="requiresReference()" x-cloak class="text-xs font-medium text-slate-700 dark:text-slate-200">Reference Number <span class="text-red-500">*</span>
                    <input name="reference_number" type="text" :required="requiresReference()" :disabled="!requiresReference()" value="{{ old('reference_number') }}" class="mt-1 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('reference_number')<span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-300">{{ $message }}</span>@enderror
                </label>

                <label x-show="isBankTransfer()" x-cloak class="text-xs font-medium text-slate-700 dark:text-slate-200">Transaction ID <span class="text-red-500">*</span>
                    <input name="transaction_id" type="text" :required="isBankTransfer()" :disabled="!isBankTransfer()" value="{{ old('transaction_id') }}" class="mt-1 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('transaction_id')<span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-300">{{ $message }}</span>@enderror
                </label>

                <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Amount Paid <span class="text-red-500">*</span>
                    <input name="amount" type="number" min="1" max="999999.99" step="0.01" required value="{{ old('amount', '3000') }}" class="mt-1 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('amount')<span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-300">{{ $message }}</span>@enderror
                </label>

                <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Payment Date <span class="text-red-500">*</span>
                    <input name="payment_date" type="date" required value="{{ old('payment_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="mt-1 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('payment_date')<span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-300">{{ $message }}</span>@enderror
                </label>

                @if ($bookings->isNotEmpty())
                    <label class="text-xs font-medium text-slate-700 dark:text-slate-200 md:col-span-2">Booking
                        <select name="booking_id" class="mt-1 w-full rounded-lg border-slate-200 bg-white text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                            <option value="">Latest booking</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}" @selected((string) old('booking_id') === (string) $booking->id)>
                                    Booking #{{ $booking->id }}{{ $booking->room?->boardingHouse?->name ? ' - '.$booking->room->boardingHouse->name : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('booking_id')<span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-300">{{ $message }}</span>@enderror
                    </label>
                @endif

                <label x-show="isCashPayment()" x-cloak class="text-xs font-medium text-slate-700 dark:text-slate-200 md:col-span-2">Notes <span class="font-normal text-slate-400">(Optional)</span>
                    <textarea name="notes" rows="2" :disabled="!isCashPayment()" class="mt-1 w-full rounded-lg border-slate-200 bg-white px-3 py-2 text-xs shadow-sm focus:border-[#2563eb] focus:ring-[#2563eb]/30 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ old('notes') }}</textarea>
                    @error('notes')<span class="mt-1 block text-xs font-semibold text-red-600 dark:text-red-300">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" :disabled="submitting || Boolean(fileError)" class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-[#2563eb] px-5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition duration-200 hover:bg-[#1d4ed8] hover:shadow-md hover:shadow-blue-600/25 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200 disabled:cursor-not-allowed disabled:opacity-60">
                    <svg x-show="!submitting" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 9 17.25 19.5 6.75" />
                    </svg>
                    <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                    </svg>
                    <span x-text="submitting ? 'Submitting...' : 'Submit Payment Proof'"></span>
                </button>
            </div>
        </form>

        @if ($receipt)
            <form id="payment-receipt-delete" method="POST" action="{{ $receiptDeleteUrl }}" onsubmit="return confirm('Delete this payment receipt?')">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endif
</section>

<script>
    window.paymentReceiptUpload = window.paymentReceiptUpload || function (config) {
        return {
            dragging: false,
            submitting: false,
            paymentMethod: config.initialPaymentMethod || 'GCash',
            file: null,
            fileError: '',
            previewUrl: '',
            existing: config.existing,
            isCashPayment() {
                return this.paymentMethod === 'Cash Payment';
            },
            isBankTransfer() {
                return this.paymentMethod === 'Bank Transfer';
            },
            requiresReceipt() {
                return !this.isCashPayment();
            },
            requiresReference() {
                return !this.isCashPayment();
            },
            handleMethodChange() {
                this.fileError = '';

                if (this.isCashPayment()) {
                    this.removeFile();
                }
            },
            handleDrop(event) {
                if (!this.requiresReceipt()) return;

                this.dragging = false;
                this.handleFile(event.dataTransfer.files[0]);
                if (!this.fileError && this.$refs.fileInput && event.dataTransfer.files.length) {
                    this.$refs.fileInput.files = event.dataTransfer.files;
                }
            },
            handleFile(file) {
                this.fileError = '';
                this.previewUrl = '';
                this.file = null;

                if (!file || !this.requiresReceipt()) return;

                const extension = this.fileExtension(file.name);
                const isAllowedType = config.allowedTypes.includes(file.type) || config.allowedExtensions.includes(extension);

                if (!isAllowedType) {
                    this.fileError = 'Please upload a JPG, JPEG, PNG, or PDF file.';
                    this.clearInput();
                    return;
                }

                if (file.size > config.maxSize) {
                    this.fileError = 'File size must not exceed 5 MB.';
                    this.clearInput();
                    return;
                }

                this.file = file;
                if (file.type.startsWith('image/')) {
                    this.previewUrl = URL.createObjectURL(file);
                }
            },
            removeFile() {
                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.file = null;
                this.previewUrl = '';
                this.fileError = '';
                this.clearInput();
            },
            clearInput() {
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },
            canSubmit() {
                if (this.fileError) return false;

                if (this.requiresReceipt() && (!this.$refs.fileInput || !this.$refs.fileInput.files.length)) {
                    this.fileError = 'Please attach a receipt file for this payment method.';
                    return false;
                }

                return true;
            },
            fileExtension(name) {
                return (name || '').split('.').pop().toLowerCase();
            },
            formatBytes(bytes) {
                if (!bytes) return '0 B';
                const units = ['B', 'KB', 'MB'];
                const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                return `${(bytes / Math.pow(1024, index)).toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
            },
        };
    };
</script>
