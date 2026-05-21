@php
    $showPageHeader = $showPageHeader ?? true;
    $uploadDocumentHref = $uploadDocumentHref ?? '#document-upload';

    $iconPaths = [
        'bell' => '<path d="M15 17H9m9-5a6 6 0 1 0-12 0c0 3-1.5 4-2 5h16c-.5-1-2-2-2-5Z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'question' => '<path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1 .7-1.5 1.2-1.5 2.5"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'shield-check' => '<path d="M12 3 19 7v5c0 4.5-2.7 7.9-7 9-4.3-1.1-7-4.5-7-9V7l7-4Z"/><path d="m9 12 2 2 4-4"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'x-circle' => '<circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>',
        'calendar' => '<path d="M7 3v4M17 3v4"/><path d="M4 8h16"/><path d="M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/>',
        'upload' => '<path d="M12 16V5"/><path d="m8 9 4-4 4 4"/><path d="M20 16.5a4 4 0 0 0-4-4h-1a6 6 0 0 0-11.3 2A3.5 3.5 0 0 0 5.5 21H18a4 4 0 0 0 2-7.5"/>',
        'file' => '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z"/><path d="M14 3v6h6"/><path d="M8 13h8M8 17h5"/>',
        'download' => '<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>',
        'more' => '<circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>',
        'eye' => '<path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/>',
        'replace' => '<path d="M3 7h12a4 4 0 0 1 0 8H8"/><path d="m8 11-4 4 4 4"/><path d="M21 17H9a4 4 0 0 1 0-8h7"/><path d="m16 13 4-4-4-4"/>',
        'trash' => '<path d="M4 7h16M9 7V5h6v2M7 7l1 13h8l1-13"/><path d="M10 11v5M14 11v5"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.8 1.8 0 0 0 .36 2l.05.05a2.1 2.1 0 0 1-2.97 2.97l-.05-.05a1.8 1.8 0 0 0-2-.36 1.8 1.8 0 0 0-1.09 1.65V21a2.1 2.1 0 0 1-4.2 0v-.08a1.8 1.8 0 0 0-1.09-1.65 1.8 1.8 0 0 0-2 .36l-.05.05a2.1 2.1 0 0 1-2.97-2.97l.05-.05a1.8 1.8 0 0 0 .36-2A1.8 1.8 0 0 0 2.15 13H2a2.1 2.1 0 0 1 0-4.2h.08a1.8 1.8 0 0 0 1.65-1.09 1.8 1.8 0 0 0-.36-2l-.05-.05a2.1 2.1 0 0 1 2.97-2.97l.05.05a1.8 1.8 0 0 0 2 .36A1.8 1.8 0 0 0 9.43 1.45V1.4a2.1 2.1 0 0 1 4.2 0v.08a1.8 1.8 0 0 0 1.09 1.65 1.8 1.8 0 0 0 2-.36l.05-.05a2.1 2.1 0 0 1 2.97 2.97l-.05.05a1.8 1.8 0 0 0-.36 2A1.8 1.8 0 0 0 20.85 8.8H21a2.1 2.1 0 0 1 0 4.2h-.08A1.8 1.8 0 0 0 19.4 15Z"/>',
    ];

    $uiIcon = fn ($name, $class = 'h-5 w-5') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8">'.$iconPaths[$name].'</svg>';

    $statusClasses = [
        'Approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'Pending Review' => 'bg-orange-100 text-orange-700 ring-orange-200',
        'Under Review' => 'bg-orange-100 text-orange-700 ring-orange-200',
        'Rejected' => 'bg-rose-100 text-rose-700 ring-rose-200',
        'Expired' => 'bg-slate-200 text-slate-700 ring-slate-300',
    ];

    $stats = [
        ['label' => 'Approved', 'value' => '5', 'description' => 'Documents approved', 'icon' => 'shield-check', 'iconClass' => 'bg-emerald-100 text-emerald-600 ring-emerald-200'],
        ['label' => 'Pending Review', 'value' => '2', 'description' => 'Awaiting admin review', 'icon' => 'clock', 'iconClass' => 'bg-orange-100 text-orange-600 ring-orange-200'],
        ['label' => 'Rejected', 'value' => '1', 'description' => 'Requires attention', 'icon' => 'x-circle', 'iconClass' => 'bg-rose-100 text-rose-600 ring-rose-200'],
        ['label' => 'Expired', 'value' => '1', 'description' => 'Needs replacement', 'icon' => 'calendar', 'iconClass' => 'bg-slate-100 text-slate-600 ring-slate-200'],
    ];

    $documents = [
        ['name' => 'Business Permit', 'subtitle' => 'DTI / Mayor\'s Permit', 'status' => 'Approved', 'submitted' => 'May 1, 2025', 'expiration' => 'May 1, 2026', 'daysLeft' => '364 days left', 'reviewStatus' => 'Approved', 'file' => 'business_permit.pdf'],
        ['name' => 'Fire Safety Certificate', 'subtitle' => 'BFP Certification', 'status' => 'Pending Review', 'submitted' => 'May 15, 2025', 'expiration' => 'May 15, 2026', 'daysLeft' => '378 days left', 'reviewStatus' => 'Under Review', 'file' => 'fire_safety_certificate.pdf'],
        ['name' => 'Sanitary Permit', 'subtitle' => 'City Health Office', 'status' => 'Approved', 'submitted' => 'Apr 20, 2025', 'expiration' => 'Apr 20, 2026', 'daysLeft' => '353 days left', 'reviewStatus' => 'Approved', 'file' => 'sanitary_permit.pdf'],
        ['name' => 'Boarding House Permit', 'subtitle' => 'Admin Permit', 'status' => 'Pending Review', 'submitted' => 'May 10, 2025', 'expiration' => 'May 10, 2026', 'daysLeft' => '373 days left', 'reviewStatus' => 'Under Review', 'file' => 'boarding_house_permit.pdf'],
        ['name' => 'Valid ID of Owner', 'subtitle' => 'Government ID', 'status' => 'Approved', 'submitted' => 'Apr 18, 2025', 'expiration' => '&mdash;', 'daysLeft' => null, 'reviewStatus' => 'Approved', 'file' => 'owner_valid_id.pdf'],
        ['name' => 'Proof of Ownership or Lease Agreement', 'subtitle' => 'Title / Lease Contract', 'status' => 'Rejected', 'submitted' => 'May 5, 2025', 'expiration' => '&mdash;', 'daysLeft' => null, 'reviewStatus' => 'Rejected', 'file' => 'lease_agreement.pdf'],
        ['name' => 'Photos of Boarding House', 'subtitle' => 'Exterior & Interior Photos', 'status' => 'Approved', 'submitted' => 'Apr 25, 2025', 'expiration' => '&mdash;', 'daysLeft' => null, 'reviewStatus' => 'Approved', 'file' => 'boarding_house_photos.zip'],
        ['name' => 'House Rules Document', 'subtitle' => 'Boarding House Rules', 'status' => 'Approved', 'submitted' => 'Apr 28, 2025', 'expiration' => '&mdash;', 'daysLeft' => null, 'reviewStatus' => 'Approved', 'file' => 'house_rules_document.pdf'],
    ];

    $selectedDocument = $documents[1];
    $tabs = ['All Documents', 'Submitted', 'Pending', 'Approved', 'Rejected', 'Expired'];
@endphp

<div
    id="compliance-management"
    x-data="{
        tab: 'All Documents',
        activeMenu: null,
        modalType: null,
        selectedDocument: null,
        init() {
            const params = new URLSearchParams(window.location.search);

            if (params.get('modal') === 'submit') {
                this.openDocumentModal('submit');
                params.delete('modal');

                const query = params.toString();
                const cleanUrl = `${window.location.pathname}${query ? `?${query}` : ''}${window.location.hash}`;
                window.history.replaceState({}, '', cleanUrl);
            }
        },
        matches(status, reviewStatus) {
            if (this.tab === 'All Documents') return true;
            if (this.tab === 'Submitted') return true;
            if (this.tab === 'Pending') return status === 'Pending Review' || reviewStatus === 'Under Review';
            return status === this.tab;
        },
        openDocumentModal(type, document = null) {
            this.modalType = type;
            this.selectedDocument = document;
            this.activeMenu = null;
        },
        closeDocumentModal() {
            this.modalType = null;
        },
        badgeClass(status) {
            return {
                'Approved': 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                'Pending Review': 'bg-orange-100 text-orange-700 ring-orange-200',
                'Under Review': 'bg-orange-100 text-orange-700 ring-orange-200',
                'Rejected': 'bg-rose-100 text-rose-700 ring-rose-200',
                'Expired': 'bg-slate-200 text-slate-700 ring-slate-300',
            }[status] || 'bg-slate-100 text-slate-700 ring-slate-200';
        }
    }"
    @keydown.escape.window="closeDocumentModal()"
    class="space-y-6"
>
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Compliance</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Manage documents and requirements for admin approval.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Notifications">
                    {!! $uiIcon('bell', 'h-5 w-5') !!}
                    <span class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white">3</span>
                </button>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50" aria-label="Help">
                    {!! $uiIcon('question', 'h-5 w-5') !!}
                </button>
                <button type="button" class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-700 text-xs font-bold text-white">JD</span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold text-slate-950">Juan Dela Cruz</span>
                        <span class="block text-xs text-slate-500">Owner</span>
                    </span>
                    <span class="text-slate-500">{!! $uiIcon('chevron-down', 'h-4 w-4') !!}</span>
                </button>
            </div>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full ring-1 {{ $stat['iconClass'] }}">
                        {!! $uiIcon($stat['icon'], 'h-7 w-7') !!}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $stat['description'] }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6">
        <div class="min-w-0 space-y-5">
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-950">Documents &amp; Requirements</h2>
                        <p class="mt-1 text-sm text-slate-500">Track submitted requirements, review status, and expiration dates.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button id="document-upload" type="button" @click="openDocumentModal('upload')" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white shadow-sm shadow-blue-900/20 transition hover:bg-blue-800">
                            {!! $uiIcon('upload', 'h-5 w-5') !!}
                            <span>Upload Document</span>
                        </button>
                        <button type="button" @click="openDocumentModal('submit')" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-900/20 transition hover:bg-emerald-700">
                            {!! $uiIcon('shield-check', 'h-5 w-5') !!}
                            <span>Submit for Review</span>
                        </button>
                    </div>
                </div>

                <div class="border-b border-slate-200 p-4">
                    <div class="overflow-x-auto">
                        <div class="flex min-w-max gap-2">
                        @foreach ($tabs as $filter)
                            <button type="button" @click="tab = @js($filter)" :class="tab === @js($filter) ? 'bg-blue-700 text-white shadow-sm shadow-blue-900/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="rounded-full px-4 py-2 text-sm font-semibold transition">
                                {{ $filter }}
                            </button>
                        @endforeach
                        </div>
                    </div>
                </div>

                <div class="hidden overflow-x-auto lg:block">
                    <table class="min-w-[1020px] w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Document</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Date Submitted</th>
                                <th class="px-5 py-4">Expiration Date</th>
                                <th class="px-5 py-4">Review Status</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($documents as $document)
                                <tr x-show="matches(@js($document['status']), @js($document['reviewStatus']))" class="align-middle hover:bg-slate-50/70">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                                                {!! $uiIcon('file', 'h-5 w-5') !!}
                                            </span>
                                            <div>
                                                <p class="font-bold text-slate-950">{{ $document['name'] }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $document['subtitle'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses[$document['status']] }}">
                                            {{ $document['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">{{ $document['submitted'] }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-800">{!! $document['expiration'] !!}</p>
                                        @if ($document['daysLeft'])
                                            <p class="mt-1 text-xs text-slate-500">{{ $document['daysLeft'] }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses[$document['reviewStatus']] }}">
                                            {{ $document['reviewStatus'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex justify-end gap-2">
                                            <a href="#document-details" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50" title="Download submitted document">
                                                {!! $uiIcon('download', 'h-4 w-4') !!}
                                            </a>
                                            <div class="relative" @click.outside="activeMenu = null">
                                                <button type="button" @click="activeMenu = activeMenu === @js($document['name']) ? null : @js($document['name'])" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50" title="More actions">
                                                    {!! $uiIcon('more', 'h-4 w-4') !!}
                                                </button>
                                                <div x-show="activeMenu === @js($document['name'])" style="display: none;" class="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-xl">
                                                    <button type="button" @click="openDocumentModal('details', @js($document))" class="flex w-full items-center gap-2 px-3 py-2 text-left text-slate-700 hover:bg-slate-50">{!! $uiIcon('eye', 'h-4 w-4') !!} View details</button>
                                                    <button type="button" @click="openDocumentModal('replace', @js($document))" class="flex w-full items-center gap-2 px-3 py-2 text-left text-slate-700 hover:bg-slate-50">{!! $uiIcon('replace', 'h-4 w-4') !!} Replace document</button>
                                                    <button type="button" @click="openDocumentModal('delete', @js($document))" class="flex w-full items-center gap-2 px-3 py-2 text-left text-rose-700 hover:bg-rose-50">{!! $uiIcon('trash', 'h-4 w-4') !!} Delete document</button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-slate-200 lg:hidden">
                    @foreach ($documents as $document)
                        <article x-show="matches(@js($document['status']), @js($document['reviewStatus']))" class="p-4">
                            <div class="flex items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100">
                                    {!! $uiIcon('file', 'h-5 w-5') !!}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-slate-950">{{ $document['name'] }}</h3>
                                    <p class="mt-1 text-sm text-slate-500">{{ $document['subtitle'] }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses[$document['status']] }}">{{ $document['status'] }}</span>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClasses[$document['reviewStatus']] }}">{{ $document['reviewStatus'] }}</span>
                                    </div>
                                    <dl class="mt-4 grid gap-3 text-sm text-slate-600">
                                        <div><dt class="font-semibold text-slate-900">Date Submitted</dt><dd>{{ $document['submitted'] }}</dd></div>
                                        <div><dt class="font-semibold text-slate-900">Expiration Date</dt><dd>{!! $document['expiration'] !!} @if ($document['daysLeft']) <span class="text-slate-500">({{ $document['daysLeft'] }})</span> @endif</dd></div>
                                    </dl>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a href="#document-details" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{!! $uiIcon('download', 'h-4 w-4') !!} Download</a>
                                        <button type="button" @click="openDocumentModal('details', @js($document))" class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 px-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">{!! $uiIcon('eye', 'h-4 w-4') !!} View details</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 ring-1 ring-blue-200">
                            {!! $uiIcon('bell', 'h-6 w-6') !!}
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-slate-950">Expiration Reminders</h2>
                            <p class="mt-1 text-sm text-slate-600">You will receive an email reminder 30 days before a document expires.</p>
                        </div>
                    </div>
                    <button type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        {!! $uiIcon('settings', 'h-4 w-4') !!}
                        Notification Settings
                    </button>
                </div>
            </div>
        </div>

    </section>

    <div x-show="modalType" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @click.self="closeDocumentModal()">
        <div class="max-h-[85vh] w-full overflow-hidden rounded-2xl bg-white shadow-2xl" :class="modalType === 'delete' || modalType === 'submit' ? 'max-w-lg' : 'max-w-4xl'">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950" x-text="modalType === 'upload' ? 'Upload Document' : modalType === 'replace' ? 'Replace Document' : modalType === 'delete' ? 'Delete Document?' : modalType === 'submit' ? 'Submit for Review?' : 'Document Details'"></h2>
                    <p class="text-sm text-slate-500" x-text="selectedDocument?.name || 'Compliance requirements'"></p>
                </div>
                <button type="button" @click="closeDocumentModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900">{!! $uiIcon('x', 'h-5 w-5') !!}</button>
            </div>

            <div class="max-h-[calc(85vh-138px)] overflow-y-auto p-6">
                <div x-show="modalType === 'details'" class="space-y-5 text-sm">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-700 ring-1 ring-orange-200">{!! $uiIcon('file', 'h-7 w-7') !!}</span>
                        <div>
                            <h3 class="text-base font-bold text-slate-950" x-text="selectedDocument?.name"></h3>
                            <p class="mt-1 text-sm text-slate-500" x-text="selectedDocument?.subtitle"></p>
                        </div>
                    </div>
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div><dt class="text-slate-500">Status</dt><dd class="mt-1"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(selectedDocument?.status)" x-text="selectedDocument?.status"></span></dd></div>
                        <div><dt class="text-slate-500">Review Status</dt><dd class="mt-1"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1" :class="badgeClass(selectedDocument?.reviewStatus)" x-text="selectedDocument?.reviewStatus"></span></dd></div>
                        <div><dt class="text-slate-500">Date Submitted</dt><dd class="mt-1 font-semibold text-slate-800" x-text="selectedDocument?.submitted"></dd></div>
                        <div><dt class="text-slate-500">Expiration Date</dt><dd class="mt-1 font-semibold text-slate-800" x-html="selectedDocument?.expiration"></dd></div>
                        <div><dt class="text-slate-500">Days left</dt><dd class="mt-1 font-semibold text-orange-700" x-text="selectedDocument?.daysLeft || 'Not applicable'"></dd></div>
                        <div><dt class="text-slate-500">Last Updated</dt><dd class="mt-1 font-semibold text-slate-800">May 16, 2025</dd></div>
                    </dl>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-slate-500">Submitted File</p>
                        <p class="mt-2 break-all font-semibold text-slate-800" x-text="selectedDocument?.file"></p>
                    </div>
                    <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4">
                        <span class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700 ring-1 ring-orange-200">Under Review</span>
                        <p class="mt-3 text-sm text-orange-900">Your document is currently being reviewed by an admin. Please check back later for updates.</p>
                    </div>
                </div>

                <div x-show="modalType === 'upload' || modalType === 'replace'" class="space-y-4">
                    <label class="block"><span class="text-sm font-semibold text-slate-700">Document Type</span><select class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"><option>Fire Safety Certificate</option><option>Business Permit</option><option>Sanitary Permit</option><option>Boarding House Permit</option></select></label>
                    <button type="button" class="flex min-h-40 w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 text-center text-sm text-slate-500 hover:border-blue-300 hover:bg-blue-50">
                        <span class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">{!! $uiIcon('upload', 'h-6 w-6') !!}</span>
                        <span>Drag and drop document here</span>
                        <span class="text-blue-700">or click to upload</span>
                    </button>
                </div>

                <div x-show="modalType === 'delete'" class="rounded-2xl bg-rose-50 p-4 text-sm text-rose-800">
                    Delete <span class="font-bold" x-text="selectedDocument?.name"></span>? This action cannot be undone.
                </div>

                <div x-show="modalType === 'submit'" class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800">
                    Submit all pending documents for admin review.
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="closeDocumentModal()" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                <button x-show="modalType === 'details'" type="button" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50">{!! $uiIcon('download', 'h-4 w-4') !!} Download</button>
                <button x-show="modalType === 'details'" type="button" @click="modalType = 'replace'" class="inline-flex h-10 items-center justify-center rounded-xl border border-blue-200 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-50">Replace Document</button>
                <button x-show="modalType === 'upload' || modalType === 'replace'" type="button" @click="closeDocumentModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Save Document</button>
                <button x-show="modalType === 'delete'" type="button" @click="closeDocumentModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-rose-600 px-4 text-sm font-semibold text-white hover:bg-rose-700">Delete Document</button>
                <button x-show="modalType === 'submit'" type="button" @click="closeDocumentModal()" class="inline-flex h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700">Submit for Review</button>
            </div>
        </div>
    </div>
</div>
