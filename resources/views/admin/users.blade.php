<x-layouts.dashboard>
<x-admin.shell>
    @php
        $accountType = $accountType ?? 'tenant';
        $typeLabels = [
            'tenant' => 'Tenants',
            'owner' => 'Owner Applications',
            'admin' => 'Administrators',
        ];
        $activeTypeLabel = $typeLabels[$accountType] ?? 'Tenants';
        $tabs = [
            ['key' => 'tenant', 'label' => 'Tenants', 'count' => $tenantCount ?? 0],
            ['key' => 'owner', 'label' => 'Owners', 'count' => $ownerCount ?? 0, 'pending' => $pendingOwnerCount ?? 0],
            ['key' => 'admin', 'label' => 'Administrators', 'count' => $adminCount ?? 0],
        ];
        $badge = fn (bool $active) => $active
            ? 'border-emerald-200 bg-emerald-100 text-emerald-700'
            : 'border-rose-200 bg-rose-100 text-rose-700';
    @endphp

    <div
        x-data="{
            addOpen: false,
            viewOpen: false,
            editOpen: false,
            selected: {},
            viewTab: 'overview',
            openView(user) {
                this.selected = user;
                this.viewTab = user.role === 'owner' ? 'application' : 'overview';
                this.viewOpen = true;
            }
        }"
        @keydown.escape.window="viewOpen = false; editOpen = false; addOpen = false"
        class="space-y-4"
    >
        <section class="ui-card overflow-hidden">
            <div class="flex flex-col gap-4 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Account Administration</p>
                    <h1 class="mt-1 text-xl font-bold">User Management</h1>
                    <p class="mt-1 text-sm ui-muted">Tenants and property owners are managed separately. Owner access stays locked until their permit and boarding house application pass review.</p>
                </div>
                <button type="button" @click="addOpen = true" class="btn-primary shrink-0">Add User</button>
            </div>

            <nav class="flex flex-wrap gap-2 border-t ui-border px-5 py-3" aria-label="User account types">
                @foreach ($tabs as $tab)
                    <a
                        href="{{ route('admin.user-management', ['account_type' => $tab['key']]) }}"
                        class="inline-flex min-h-10 items-center gap-2 rounded-xl border px-3.5 py-2 text-sm font-semibold transition {{ $accountType === $tab['key'] ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'ui-border bg-[color:var(--surface-1)] ui-muted hover:border-blue-300 hover:text-blue-600' }}"
                        @if ($accountType === $tab['key']) aria-current="page" @endif
                    >
                        <span>{{ $tab['label'] }}</span>
                        <span class="rounded-full px-2 py-0.5 text-[11px] {{ $accountType === $tab['key'] ? 'bg-white/20 text-white' : 'bg-[color:var(--surface-2)]' }}">{{ number_format($tab['count']) }}</span>
                        @if (($tab['pending'] ?? 0) > 0)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">{{ number_format($tab['pending']) }} pending</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </section>

        @if ($accountType === 'owner')
            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="ui-card p-4"><p class="text-xs font-semibold ui-muted">Owner Applications</p><p class="mt-1 text-xl font-bold">{{ number_format($ownerCount ?? 0) }}</p></div>
                <div class="ui-card border-blue-200 p-4">
                    <p class="text-xs font-semibold text-blue-600">Linked Properties</p>
                    <p class="mt-1 text-xl font-bold text-blue-600">{{ number_format($linkedOwnerPropertyCount ?? 0) }}</p>
                    @if (($unassignedPropertyCount ?? 0) > 0)
                        <p class="mt-1 text-[11px] font-semibold text-amber-600">{{ number_format($unassignedPropertyCount) }} unassigned</p>
                    @else
                        <p class="mt-1 text-[11px] ui-muted">All properties have owners</p>
                    @endif
                </div>
                <div class="ui-card border-amber-200 p-4"><p class="text-xs font-semibold text-amber-600">Awaiting Review</p><p class="mt-1 text-xl font-bold text-amber-600">{{ number_format($pendingOwnerCount ?? 0) }}</p></div>
                <div class="ui-card border-emerald-200 p-4"><p class="text-xs font-semibold text-emerald-600">Verified Owners</p><p class="mt-1 text-xl font-bold text-emerald-600">{{ number_format($verifiedOwnerCount ?? 0) }}</p></div>
            </section>
        @endif

        <section class="ui-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b ui-border px-5 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="font-bold">{{ $activeTypeLabel }}</h2>
                    <p class="text-xs ui-muted">
                        @if ($accountType === 'owner')
                            Open an application to inspect its business permit and submitted boarding house before deciding.
                        @elseif ($accountType === 'admin')
                            System administrator accounts with platform-wide access.
                        @else
                            Student and tenant accounts are listed independently from owners.
                        @endif
                    </p>
                </div>
                <form method="GET" action="{{ route('admin.user-management') }}" class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                    <input type="hidden" name="account_type" value="{{ $accountType }}">
                    <input name="q" value="{{ request('q') }}" class="ui-input h-10 min-w-0 text-sm sm:w-56" placeholder="Search name or email">
                    <select name="status" class="ui-input h-10 text-sm sm:w-44">
                        <option value="">All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        @if ($accountType === 'owner')<option value="pending" @selected(request('status') === 'pending')>Pending review</option>@endif
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive / rejected</option>
                    </select>
                    <button class="btn-secondary h-10">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-[11px] font-bold uppercase tracking-wide ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Account</th>
                            @if ($accountType === 'owner')
                                <th class="px-5 py-3 text-left">Boarding Houses</th>
                                <th class="px-5 py-3 text-left">Submitted Files</th>
                                <th class="px-5 py-3 text-left">Review Status</th>
                            @else
                                <th class="px-5 py-3 text-left">Role</th>
                                <th class="px-5 py-3 text-left">Contact</th>
                                <th class="px-5 py-3 text-left">Account Status</th>
                            @endif
                            <th class="px-5 py-3 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($users as $user)
                            @php
                                $accountStatus = strtolower((string) ($user->status ?: ($user->account_status ?? 'inactive')));
                                $active = $accountStatus === 'active' && (bool) $user->is_active;
                                $isAdmin = $user->role === 'admin';
                                $isOwner = $user->role === 'owner';
                                $roleLabel = $isAdmin ? 'Administrator' : ($isOwner ? 'Property Owner' : 'Student / Tenant');
                                $ownerProfile = $user->ownerProfile;
                                $houses = $user->ownedBoardingHouses->sortBy('id')->values();
                                $house = $houses->first();
                                $submittedHouseCount = $houses->count() ?: (filled($ownerProfile?->boarding_house_name) ? 1 : 0);
                                $permitPath = $ownerProfile?->proof_of_ownership ?: $ownerProfile?->valid_id_file;
                                $permitExists = filled($permitPath) && Illuminate\Support\Facades\Storage::disk('public')->exists($permitPath);
                                $permitUrl = $permitExists ? Illuminate\Support\Facades\Storage::disk('public')->url($permitPath) : null;
                                $permitType = strtolower((string) pathinfo((string) $permitPath, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'image';
                                $verificationStatus = strtolower((string) ($ownerProfile?->verification_status ?: ($isOwner ? 'pending' : 'not applicable')));
                                $isSeededDemo = (bool) ($ownerProfile?->is_seeded_demo ?? false);
                                $ownerApproved = $isOwner && $user->hasApprovedOwnerAccess();
                                $wasRejected = $verificationStatus === 'rejected';
                                $approvalState = $ownerApproved ? 'approved' : ($wasRejected ? 'not_approved' : 'pending');
                                $approvalLabel = $ownerApproved ? 'Approved' : ($wasRejected ? 'Rejected' : 'Pending approval');
                                $photoPaths = $houses->flatMap(fn ($ownedHouse) => collect($ownedHouse->images ?? [])->pluck('image_path')
                                    ->merge(collect($ownedHouse->photos ?? [])->pluck('photo_path'))
                                    ->merge(collect([$ownedHouse->featured_image, $ownedHouse->exterior_image, $ownedHouse->room_image, $ownedHouse->cr_image, $ownedHouse->kitchen_image])))
                                    ->filter()->unique()->values();
                                $photoUrls = $photoPaths->map(fn ($path) => \Illuminate\Support\Facades\Storage::disk('public')->url($path))->values()->all();
                                $permissions = $isAdmin
                                    ? ['Manage platform accounts', 'Review owner applications', 'Approve listings', 'Monitor payments and reports']
                                    : ($isOwner
                                        ? ($ownerApproved
                                            ? ['Approved owner account', 'Manage approved boarding houses', 'Manage rooms and reservations', 'Receive verified payments']
                                            : ['Access remains locked before approval', 'Submitted permit requires administrator review', 'Boarding house remains unpublished', 'Login is disabled'])
                                        : ['Browse approved listings', 'Reserve rooms', 'Pay rent in cash', 'Message property owners']);
                                $initials = collect(preg_split('/\s+/', trim((string) $user->name)))
                                    ->filter()->map(fn ($word) => strtoupper(substr($word, 0, 1)))->take(2)->implode('') ?: 'U';
                                $userPhotoUrl = $user->photo_url;
                                $reviewReady = (bool) (($isSeededDemo || $permitUrl) && $house);
                                $verifyUrl = $isOwner && ! $ownerApproved ? route('admin.owners.verify', $user) : null;
                                $payload = [
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'role' => $user->role,
                                    'role_label' => $roleLabel,
                                    'phone' => $user->phone ?? $user->contact_number,
                                    'active' => $active,
                                    'initials' => $initials,
                                    'photo_url' => $userPhotoUrl,
                                    'permissions' => $permissions,
                                    'created' => optional($user->created_at)->format('M d, Y') ?? 'Unknown',
                                    'last_login' => optional($user->updated_at)->diffForHumans() ?? 'Unknown',
                                    'verified' => (bool) $user->email_verified_at,
                                    'account_status' => $accountStatus,
                                    'verification_status' => $verificationStatus,
                                    'approval_state' => $approvalState,
                                    'approval_label' => $approvalLabel,
                                    'owner_approved' => $ownerApproved,
                                    'seeded_demo' => $isSeededDemo,
                                    'permit_url' => $permitUrl,
                                    'permit_type' => $permitType,
                                    'permit_number' => $ownerProfile?->business_permit_number ?: $ownerProfile?->valid_id_number,
                                    'houses' => $houses->map(fn ($ownedHouse) => [
                                        'id' => $ownedHouse->id,
                                        'name' => $ownedHouse->name,
                                        'address' => $ownedHouse->address ?: $ownedHouse->full_address,
                                        'status' => strtolower((string) ($ownedHouse->approval_status ?: $ownedHouse->status ?: 'pending')),
                                    ])->values()->all(),
                                    'house_name' => $house?->name ?: $ownerProfile?->boarding_house_name,
                                    'house_address' => $house?->address ?: $ownerProfile?->boarding_house_address,
                                    'house_status' => strtolower((string) ($house?->approval_status ?: $house?->status ?: 'pending')),
                                    'house_description' => $house?->description,
                                    'house_rules' => $house?->house_rules ?: $ownerProfile?->house_rules,
                                    'monthly_rent' => $ownerProfile?->monthly_rent_range ?: ($house?->monthly_payment ? 'PHP '.number_format((float) $house->monthly_payment) : null),
                                    'photos' => $photoUrls,
                                    'review_ready' => $reviewReady,
                                    'verify_url' => $verifyUrl,
                                    'reject_url' => $isOwner && ! $ownerApproved && $verificationStatus !== 'rejected' ? route('admin.owners.reject', $user) : null,
                                    'update_url' => route('admin.users.update', $user),
                                    'delete_url' => auth()->id() === $user->id ? null : route('admin.users.destroy', $user),
                                ];
                            @endphp
                            <tr
                                class="cursor-pointer transition hover:bg-slate-50/80 focus-within:bg-blue-50/40 dark:hover:bg-slate-800/60"
                                role="button"
                                tabindex="0"
                                @click="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                @keydown.enter="openView({{ \Illuminate\Support\Js::from($payload) }})"
                                @keydown.space.prevent="openView({{ \Illuminate\Support\Js::from($payload) }})"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-xs font-bold text-blue-700">
                                            @if ($userPhotoUrl)<img src="{{ $userPhotoUrl }}" alt="{{ $user->name }}" class="h-full w-full object-cover" loading="lazy">@else{{ $initials }}@endif
                                        </span>
                                        <div class="min-w-0"><p class="truncate font-semibold">{{ $user->name }}</p><p class="truncate text-xs ui-muted">{{ $user->email }}</p></div>
                                    </div>
                                </td>
                                @if ($accountType === 'owner')
                                    <td class="px-5 py-4" data-owner-property-summary="{{ $submittedHouseCount }}">
                                        @if ($submittedHouseCount > 0)
                                            <p class="font-semibold">
                                                {{ $submittedHouseCount }} boarding {{ \Illuminate\Support\Str::plural('house', $submittedHouseCount) }}
                                            </p>
                                            <p class="mt-0.5 text-xs ui-muted">Open the application to view all properties.</p>
                                        @else
                                            <p class="font-semibold text-amber-700">No boarding house submitted</p>
                                            <p class="mt-0.5 text-xs ui-muted">The application has no linked property.</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $isSeededDemo || $permitUrl ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                            <span aria-hidden="true">{{ $isSeededDemo || $permitUrl ? '✓' : '✕' }}</span>
                                            {{ $isSeededDemo ? 'Seeded demo exemption' : ($permitUrl ? 'Permit submitted' : 'Permit missing') }}
                                        </span>
                                        <p class="mt-1 text-[11px] ui-muted">{{ count($photoUrls) }} property photo{{ count($photoUrls) === 1 ? '' : 's' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold {{ $ownerApproved ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : ($verificationStatus === 'rejected' ? 'border-rose-200 bg-rose-100 text-rose-700' : 'border-amber-200 bg-amber-100 text-amber-700') }}">
                                            <span aria-hidden="true">{{ $ownerApproved ? '✓' : ($verificationStatus === 'rejected' ? '✕' : '●') }}</span>
                                            {{ $approvalLabel }}
                                        </span>
                                        <p class="mt-1 text-[11px] ui-muted">{{ $ownerApproved ? ($isSeededDemo ? 'Demo account enabled' : 'Verified account enabled') : 'Login blocked' }}</p>
                                    </td>
                                @else
                                    <td class="px-5 py-4 font-medium">{{ $roleLabel }}</td>
                                    <td class="px-5 py-4 ui-muted">{{ $user->phone ?? $user->contact_number ?? 'Not set' }}</td>
                                    <td class="px-5 py-4"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $badge($active) }}">{{ $active ? 'Active' : ucfirst($accountStatus) }}</span></td>
                                @endif
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($isOwner && $verifyUrl)
                                            <form method="POST" action="{{ $verifyUrl }}" @submit.stop onsubmit="return confirm('Approve the submitted permit and boarding house, then enable owner access?')">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    @click.stop
                                                    @disabled(! $reviewReady)
                                                    title="{{ $reviewReady ? 'Approve this owner application' : 'A stored permit and linked boarding house are required before approval' }}"
                                                    class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-600"
                                                >
                                                    Approve
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" @click.stop="openView({{ \Illuminate\Support\Js::from($payload) }})" class="inline-flex h-9 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-bold text-blue-700 transition hover:bg-blue-100">
                                            {{ $isOwner ? 'Review' : 'Open account' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center"><p class="font-semibold">No {{ strtolower($activeTypeLabel) }} found</p><p class="mt-1 text-xs ui-muted">Try changing the filters or search term.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())<div class="border-t ui-border px-5 py-4">{{ $users->links() }}</div>@endif
        </section>

        <div data-modal-root role="dialog" aria-modal="true" aria-labelledby="add-user-title" x-show="addOpen" x-cloak @click.self="addOpen = false" class="bm-modal-overlay">
            <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="bm-modal bm-modal--notification-detail" @click.stop>
                @csrf
                <div class="bm-modal__header"><div><p class="bm-modal__eyebrow">Create</p><h2 id="add-user-title" class="bm-modal__title">Add User</h2><p class="bm-modal__subtitle">Create a tenant or administrator. Owners must submit the public permit-based application.</p></div><button type="button" @click="addOpen = false" class="bm-modal__close" aria-label="Close add user modal">&times;</button></div>
                <div class="bm-modal__body bm-modal__body--compact"><div class="bm-modal__grid bm-modal__grid--two-col"><label>Name<input name="name" required></label><label>Email<input name="email" type="email" required></label><label>Role<select name="role" required><option value="user">Student / Tenant</option><option value="admin">Administrator</option></select></label><label>Phone<input name="phone"></label><label class="sm:col-span-2">Profile Photo<input name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp"><span class="mt-1 block text-[11px] font-medium ui-muted">JPG, PNG, or WEBP. Maximum 2 MB.</span></label><label class="sm:col-span-2">Password<input name="password" type="password" required minlength="8"></label><label class="sm:col-span-2 bm-modal__checkbox"><input type="checkbox" name="is_active" value="1" checked><span>Active account</span></label></div></div>
                <div class="bm-modal__footer"><button type="button" @click="addOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button><button class="bm-modal__button bm-modal__button--primary">Save User</button></div>
            </form>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" aria-labelledby="user-details-title" x-show="viewOpen" x-cloak @click.self="viewOpen = false" class="bm-modal-overlay">
            <section class="bm-modal bm-modal--lg" @click.stop>
                <div class="bm-modal__header">
                    <div class="flex min-w-0 flex-1 items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-bold text-blue-700"><template x-if="selected.photo_url"><img :src="selected.photo_url" :alt="selected.name" class="h-full w-full object-cover"></template><span x-show="!selected.photo_url" x-text="selected.initials || 'U'"></span></span><div class="min-w-0"><p class="bm-modal__eyebrow" x-text="selected.role === 'owner' ? 'Owner Application Review' : 'Account Details'"></p><h2 id="user-details-title" class="bm-modal__title truncate" x-text="selected.name"></h2><p class="bm-modal__subtitle truncate" x-text="selected.email"></p></div><span x-show="selected.role === 'owner'" class="ml-auto hidden shrink-0 items-center gap-1 rounded-full border px-3 py-1.5 text-xs font-bold sm:inline-flex" :class="selected.owner_approved ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : (selected.approval_state === 'not_approved' ? 'border-rose-200 bg-rose-100 text-rose-700' : 'border-amber-200 bg-amber-100 text-amber-700')"><span aria-hidden="true" x-text="selected.owner_approved ? '\u2713' : (selected.approval_state === 'not_approved' ? '\u2715' : '\u25cf')"></span><span x-text="selected.approval_label"></span></span></div>
                    <button type="button" @click="viewOpen = false" class="bm-modal__close" aria-label="Close user details modal">&times;</button>
                </div>

                <div class="flex flex-wrap gap-1 border-b ui-border px-5">
                    <button type="button" @click="viewTab = selected.role === 'owner' ? 'application' : 'overview'" class="border-b-2 px-3 py-2.5 text-xs font-bold" :class="['application','overview'].includes(viewTab) ? 'border-blue-600 text-blue-600' : 'border-transparent ui-muted'" x-text="selected.role === 'owner' ? 'Application' : 'Overview'"></button>
                    <button type="button" x-show="selected.role === 'owner'" @click="viewTab = 'permit'" class="border-b-2 px-3 py-2.5 text-xs font-bold" :class="viewTab === 'permit' ? 'border-blue-600 text-blue-600' : 'border-transparent ui-muted'">Business Permit</button>
                    <button type="button" x-show="selected.role === 'owner'" @click="viewTab = 'photos'" class="border-b-2 px-3 py-2.5 text-xs font-bold" :class="viewTab === 'photos' ? 'border-blue-600 text-blue-600' : 'border-transparent ui-muted'">Property Photos</button>
                    <button type="button" @click="viewTab = 'permissions'" class="border-b-2 px-3 py-2.5 text-xs font-bold" :class="viewTab === 'permissions' ? 'border-blue-600 text-blue-600' : 'border-transparent ui-muted'">Access</button>
                </div>

                <div class="bm-modal__body bm-modal__body--compact">
                    <div x-show="viewTab === 'overview'">
                        <dl class="bm-modal__details bm-modal__details--two-col"><div class="bm-modal__detail"><dt>Role</dt><dd x-text="selected.role_label"></dd></div><div class="bm-modal__detail"><dt>Account Status</dt><dd class="capitalize" x-text="selected.account_status"></dd></div><div class="bm-modal__detail"><dt>Phone</dt><dd x-text="selected.phone || 'Not set'"></dd></div><div class="bm-modal__detail"><dt>Registered</dt><dd x-text="selected.created"></dd></div></dl>
                    </div>

                    <div x-show="viewTab === 'application' && selected.role === 'owner'" class="space-y-4">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800" x-show="selected.owner_approved"><strong>&#10003; Approved:</strong> <span x-text="selected.seeded_demo ? 'this seeded demonstration owner is enabled under the demo-only permit exemption.' : 'the permit and boarding house application were verified, and owner access is enabled.'"></span></div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800" x-show="selected.approval_state === 'pending'"><strong>Access locked:</strong> this owner cannot sign in or commercialize the property until an administrator approves the submitted permit and application.</div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800" x-show="selected.approval_state === 'not_approved'"><strong>&#10005; Not approved:</strong> account login and listing publication remain disabled.</div>
                        <div class="bm-modal__details bm-modal__details--two-col">
                            <div class="bm-modal__detail sm:col-span-2">
                                <dt>Boarding Houses</dt>
                                <dd class="mt-2 space-y-2">
                                    <template x-for="house in (selected.houses || [])" :key="house.id">
                                        <div class="rounded-lg border ui-border px-3 py-2">
                                            <p class="font-semibold" x-text="house.name"></p>
                                            <p class="mt-0.5 text-xs ui-muted" x-text="house.address || 'Address not provided'"></p>
                                        </div>
                                    </template>
                                    <span x-show="!selected.houses?.length" x-text="selected.house_name || 'Not submitted'"></span>
                                </dd>
                            </div>
                            <div class="bm-modal__detail"><dt>Owner Approval</dt><dd class="font-bold" :class="selected.owner_approved ? 'text-emerald-700' : (selected.approval_state === 'not_approved' ? 'text-rose-700' : 'text-amber-700')" x-text="selected.approval_label"></dd></div>
                            <div class="bm-modal__detail"><dt>Rent Range</dt><dd x-text="selected.monthly_rent || 'Not set'"></dd></div>
                            <div class="bm-modal__detail"><dt>Permit Number</dt><dd x-text="selected.seeded_demo ? 'Demo exemption (seeded record)' : (selected.permit_number || 'Not provided')"></dd></div>
                        </div>
                        <section class="bm-modal__section"><h3 class="bm-modal__section-title">Review checklist</h3><div class="mt-3 grid gap-2 sm:grid-cols-3"><span class="rounded-lg border px-3 py-2 text-xs font-bold" :class="selected.seeded_demo || selected.permit_url ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700'" x-text="selected.seeded_demo ? '\u2713 Seeded permit exemption' : (selected.permit_url ? '\u2713 Permit uploaded' : '\u2715 Permit missing')"></span><span class="rounded-lg border px-3 py-2 text-xs font-bold" :class="selected.houses?.length || selected.house_name ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700'" x-text="selected.houses?.length ? '\u2713 ' + selected.houses.length + ' properties linked' : (selected.house_name ? '\u2713 Property submitted' : '\u2715 Property missing')"></span><span class="rounded-lg border px-3 py-2 text-xs font-bold" :class="selected.photos?.length ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'" x-text="selected.photos?.length ? '\u2713 ' + selected.photos.length + ' photo(s)' : 'No property photos'"></span></div></section>
                        <label x-show="selected.reject_url" class="block text-sm font-bold">Reason if rejected<textarea name="rejection_reason" form="reject-owner-form" rows="3" class="ui-input mt-2 w-full" placeholder="Explain which permit or property detail must be corrected."></textarea></label>
                    </div>

                    <div x-show="viewTab === 'permit' && selected.role === 'owner'">
                        <div x-show="selected.seeded_demo" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-center text-sm font-semibold text-emerald-700">&#10003; Seeded demonstration record. The permit exemption applies only to seeded owners; public registrations never receive it.</div>
                        <div x-show="!selected.seeded_demo && !selected.permit_url" class="rounded-xl border border-rose-200 bg-rose-50 p-5 text-center text-sm font-semibold text-rose-700">No readable business permit was submitted. This real owner account cannot be approved or used.</div>
                        <template x-if="selected.permit_url && selected.permit_type === 'pdf'"><iframe :src="selected.permit_url" title="Submitted business permit" class="h-[52vh] w-full rounded-xl border ui-border bg-white"></iframe></template>
                        <template x-if="selected.permit_url && selected.permit_type !== 'pdf'"><img :src="selected.permit_url" alt="Submitted business permit" class="mx-auto max-h-[52vh] w-auto rounded-xl border ui-border object-contain"></template>
                    </div>

                    <div x-show="viewTab === 'photos' && selected.role === 'owner'">
                        <div x-show="!selected.photos?.length" class="rounded-xl border ui-border p-5 text-center text-sm ui-muted">No property photos were submitted.</div>
                        <div x-show="selected.photos?.length" class="grid gap-3 sm:grid-cols-2"><template x-for="(photo, index) in (selected.photos || [])" :key="photo"><figure class="overflow-hidden rounded-xl border ui-border bg-[color:var(--surface-2)]"><img :src="photo" :alt="'Submitted boarding house photo ' + (index + 1)" class="h-52 w-full object-cover"><figcaption class="px-3 py-2 text-xs font-semibold ui-muted" x-text="'Photo ' + (index + 1)"></figcaption></figure></template></div>
                    </div>

                    <div x-show="viewTab === 'permissions'" class="space-y-2"><template x-for="permission in (selected.permissions || [])" :key="permission"><div class="flex items-center gap-3 rounded-xl border ui-border bg-[color:var(--surface-2)] px-4 py-3"><span class="text-emerald-600">&#10003;</span><span class="text-sm font-medium" x-text="permission"></span></div></template></div>
                </div>

                <div class="bm-modal__footer items-center justify-between">
                    <p x-show="selected.role === 'owner'" class="mr-auto max-w-sm text-xs ui-muted" x-text="selected.owner_approved ? (selected.seeded_demo ? 'Approved seeded demo account. Its permit exemption is never applied to public registrations.' : 'Approved owner account with verified access.') : 'Approval activates the owner account and publishes only the reviewed boarding house application.'"></p>
                    <template x-if="selected.reject_url"><form id="reject-owner-form" method="POST" :action="selected.reject_url" onsubmit="return confirm('Reject this owner application and keep its account locked?')">@csrf @method('PATCH')<button class="bm-modal__button bm-modal__button--danger">Reject</button></form></template>
                    <template x-if="selected.verify_url"><form method="POST" :action="selected.verify_url" onsubmit="return confirm('Approve the submitted permit and boarding house, then enable owner access?')">@csrf @method('PATCH')<button class="bm-modal__button bg-emerald-600 text-white hover:bg-emerald-700" :disabled="!selected.review_ready" :class="!selected.review_ready ? 'cursor-not-allowed opacity-50' : ''">Approve Owner</button></form></template>
                    <button type="button" x-show="selected.role !== 'owner' || selected.owner_approved" @click="editOpen = true; viewOpen = false" class="bm-modal__button bm-modal__button--primary">Edit</button>
                    <button type="button" @click="viewOpen = false" class="bm-modal__button bm-modal__button--secondary">Close</button>
                </div>
            </section>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" aria-labelledby="edit-user-title" x-show="editOpen" x-cloak @click.self="editOpen = false" class="bm-modal-overlay">
            <form method="POST" :action="selected.update_url" enctype="multipart/form-data" class="bm-modal bm-modal--notification-detail" @click.stop>
                @csrf @method('PATCH')
                <div class="bm-modal__header"><div class="flex min-w-0 items-center gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-blue-50 text-sm font-bold text-blue-700"><template x-if="selected.photo_url"><img :src="selected.photo_url" :alt="selected.name" class="h-full w-full object-cover"></template><span x-show="!selected.photo_url" x-text="selected.initials || 'U'"></span></span><div class="min-w-0"><p class="bm-modal__eyebrow">Edit</p><h2 id="edit-user-title" class="bm-modal__title truncate" x-text="selected.name || 'Edit User'"></h2><p class="bm-modal__subtitle">An unverified owner cannot be activated from this form.</p></div></div><button type="button" @click="editOpen = false" class="bm-modal__close" aria-label="Close edit user modal">&times;</button></div>
                <div class="bm-modal__body bm-modal__body--compact"><div class="bm-modal__grid bm-modal__grid--two-col"><label>Name<input name="name" required :value="selected.name"></label><label>Email<input name="email" type="email" required :value="selected.email"></label><label>Role<select name="role" required><option value="user" :selected="['user','tenant','student'].includes(selected.role)">Student / Tenant</option><option value="owner" :selected="selected.role === 'owner'">Property Owner</option><option value="admin" :selected="selected.role === 'admin'">Administrator</option></select></label><label>Phone<input name="phone" :value="selected.phone"></label><label class="sm:col-span-2">Replace Profile Photo<input name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp"><span class="mt-1 block text-[11px] font-medium ui-muted">Leave empty to keep the current photo.</span></label><label class="sm:col-span-2">New Password<input name="password" type="password" minlength="8" placeholder="Leave blank to keep current"></label><label class="sm:col-span-2 bm-modal__checkbox"><input type="checkbox" name="is_active" value="1" :checked="selected.active"><span>Active account</span></label></div></div>
                <div class="bm-modal__footer"><button type="button" @click="editOpen = false" class="bm-modal__button bm-modal__button--secondary">Cancel</button><button class="bm-modal__button bm-modal__button--primary">Save Changes</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
