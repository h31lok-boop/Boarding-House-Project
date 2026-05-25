@php
    $showPageHeader = $showPageHeader ?? true;
    $isAdminWorkspace = request()->routeIs('admin.*');
    $routeName = fn (string $admin, string $owner, $params = []) => route($isAdminWorkspace ? $admin : $owner, $params);
    $filters = $filters ?? ['q' => request('q'), 'status' => request('status')];
    $statusClass = function (?string $status): string {
        return match (strtolower((string) $status)) {
            'approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'rejected' => 'bg-rose-100 text-rose-700 ring-rose-200',
            'under_review', 'pending' => 'bg-orange-100 text-orange-700 ring-orange-200',
            default => 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    };
@endphp

<div id="compliance-management" class="space-y-6">
    @if ($showPageHeader)
        <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Compliance</h1>
                <p class="mt-1 text-sm text-slate-600 sm:text-base">Upload OSAS documents and track admin validation status.</p>
            </div>
            <form method="POST" action="{{ $routeName('admin.compliance.submit', 'owner.compliance.submit') }}">
                @csrf
                <button class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Submit Pending Documents
                </button>
            </form>
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Approved', 'value' => $stats['approved'] ?? 0, 'tone' => 'bg-emerald-100 text-emerald-700'],
            ['label' => 'Pending Review', 'value' => $stats['pending'] ?? 0, 'tone' => 'bg-orange-100 text-orange-700'],
            ['label' => 'Rejected', 'value' => $stats['rejected'] ?? 0, 'tone' => 'bg-rose-100 text-rose-700'],
            ['label' => 'Total Documents', 'value' => $stats['total'] ?? 0, 'tone' => 'bg-blue-100 text-blue-700'],
        ] as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">{{ $stat['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($stat['value']) }}</p>
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stat['tone'] }}">Live data</span>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(320px,0.45fr)_minmax(0,1fr)]">
        <form method="POST" action="{{ $routeName('admin.compliance.documents.store', 'owner.compliance.documents.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="text-lg font-bold text-slate-950">Upload Document</h2>
            <div class="mt-5 space-y-4">
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Boarding House</span>
                    <select name="boarding_house_id" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm" required>
                        <option value="">Select listing</option>
                        @foreach ($houseOptions as $house)
                            <option value="{{ $house->id }}" @selected(old('boarding_house_id') == $house->id)>{{ $house->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Requirement Name</span>
                    <input name="requirement_name" value="{{ old('requirement_name') }}" placeholder="Business Permit, Fire Safety Certificate..." class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm" required>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold text-slate-700">Document File</span>
                    <input name="uploaded_file" type="file" class="mt-2 block w-full text-sm text-slate-600" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required>
                </label>
                <button class="w-full rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800">Upload Document</button>
            </div>
        </form>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <form method="GET" action="{{ $routeName('admin.compliance.index', 'owner.compliance.index') }}" class="grid gap-3 border-b border-slate-200 p-4 lg:grid-cols-[minmax(240px,1fr)_180px_auto]">
                <input name="q" value="{{ $filters['q'] }}" type="search" placeholder="Search document, file, or listing" class="h-11 rounded-xl border-slate-200 text-sm">
                <select name="status" class="h-11 rounded-xl border-slate-200 text-sm">
                    <option value="">All status</option>
                    @foreach (['pending', 'under_review', 'approved', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Filter</button>
                    <a href="{{ $routeName('admin.compliance.index', 'owner.compliance.index') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-[940px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Document</th>
                            <th class="px-5 py-4">Listing</th>
                            <th class="px-5 py-4">Submitted</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Remarks</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($requirements as $document)
                            <tr class="align-top hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-950">{{ $document->requirement_name }}</p>
                                    <p class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ basename($document->uploaded_file) }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $document->boardingHouse?->name }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ optional($document->submission_date)->format('M d, Y') ?: optional($document->created_at)->format('M d, Y') }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusClass($document->validation_status) }}">{{ ucfirst(str_replace('_', ' ', $document->validation_status)) }}</span>
                                </td>
                                <td class="px-5 py-4 max-w-xs text-slate-600">{{ $document->validator_remarks ?: 'No admin remarks yet.' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ $routeName('admin.compliance.documents.download', 'owner.compliance.documents.download', $document) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Download</a>
                                        <details class="relative">
                                            <summary class="cursor-pointer rounded-lg border border-blue-200 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">Replace</summary>
                                            <form method="POST" action="{{ $routeName('admin.compliance.documents.update', 'owner.compliance.documents.update', $document) }}" enctype="multipart/form-data" class="absolute right-0 z-30 mt-2 w-80 rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="boarding_house_id" value="{{ $document->boarding_house_id }}">
                                                <label class="block text-xs font-bold text-slate-600">Requirement name</label>
                                                <input name="requirement_name" value="{{ $document->requirement_name }}" class="mt-1 h-10 w-full rounded-xl border-slate-200 text-sm" required>
                                                <label class="mt-3 block text-xs font-bold text-slate-600">Replacement file</label>
                                                <input name="uploaded_file" type="file" class="mt-1 block w-full text-sm text-slate-600" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                                                <button class="mt-4 w-full rounded-xl bg-blue-700 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800">Save Replacement</button>
                                            </form>
                                        </details>
                                        <form method="POST" action="{{ $routeName('admin.compliance.documents.destroy', 'owner.compliance.documents.destroy', $document) }}" onsubmit="return confirm('Delete this document?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No compliance documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4">{{ $requirements->links() }}</div>
        </section>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Listing Compliance Summary</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($houses as $item)
                <article class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-bold text-slate-950">{{ $item['house']->name }}</h3>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $item['compliance']['badge'] }}">{{ $item['compliance']['label'] }}</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-600">{{ $item['compliance']['remarks'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
</div>
