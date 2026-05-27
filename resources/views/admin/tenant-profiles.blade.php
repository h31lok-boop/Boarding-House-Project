<x-layouts.dashboard>
<x-admin.shell>
    @php
        $badge = fn ($verified) => $verified ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200';
    @endphp

    <div x-data="{ viewOpen: false, editOpen: false, selected: {} }" class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Management</p>
            <h1 class="mt-2 text-2xl font-bold">Tenant Profiles</h1>
            <p class="mt-2 text-sm ui-muted">Review student profile details, verification status, emergency contacts, and school information.</p>
        </div>

        <form method="GET" class="ui-card p-4 grid gap-3 md:grid-cols-[1fr_180px_auto]">
            <input name="q" value="{{ request('q') }}" class="ui-input text-sm" placeholder="Search tenant name or email">
            <select name="verified" class="ui-input text-sm">
                <option value="">All verification</option>
                <option value="yes" @selected(request('verified') === 'yes')>Verified</option>
                <option value="no" @selected(request('verified') === 'no')>Unverified</option>
            </select>
            <button class="btn-secondary">Filter</button>
        </form>

        <div class="ui-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[color:var(--surface-2)] text-xs uppercase ui-muted">
                        <tr>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">School / Course</th>
                            <th class="px-5 py-3 text-left">Emergency Contact</th>
                            <th class="px-5 py-3 text-left">Verification</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y ui-border">
                        @forelse ($tenants as $tenant)
                            @php
                                $profile = $tenant->tenantProfile;
                                $verified = (bool) ($profile?->id_verified);
                                $payload = [
                                    'student_id' => $profile?->student_id,
                                    'school_company' => $profile?->school_company,
                                    'course_or_position' => $profile?->course_or_position,
                                    'valid_id_type' => $profile?->valid_id_type,
                                    'valid_id_number' => $profile?->valid_id_number,
                                    'emergency_contact_name' => $profile?->emergency_contact_name,
                                    'emergency_contact_number' => $profile?->emergency_contact_number,
                                    'preferred_language' => $profile?->preferred_language,
                                    'id_verified' => $verified,
                                    'tenant_name' => $tenant->name,
                                    'tenant_email' => $tenant->email,
                                    'update_url' => route('admin.tenant-profiles.update', $tenant),
                                ];
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $tenant->name }}</p>
                                    <p class="text-xs ui-muted">{{ $tenant->email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p>{{ $profile?->school_company ?? 'Not set' }}</p>
                                    <p class="text-xs ui-muted">{{ $profile?->course_or_position ?? 'No course/position' }}</p>
                                </td>
                                <td class="px-5 py-4 ui-muted">{{ $profile?->emergency_contact_name ?? 'Not set' }} {{ $profile?->emergency_contact_number ? '· '.$profile->emergency_contact_number : '' }}</td>
                                <td class="px-5 py-4"><span class="badge border {{ $badge($verified) }}">{{ $verified ? 'Verified' : 'Needs Review' }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; viewOpen = true">View</button>
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="selected = {{ \Illuminate\Support\Js::from($payload) }}; editOpen = true">Edit</button>
                                        @if ($profile)
                                            <form method="POST" action="{{ route('admin.tenant-profiles.destroy', $profile) }}" onsubmit="return confirm('Delete this tenant profile?')">
                                                @csrf @method('DELETE')
                                                <button class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center ui-muted">No tenant profiles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t ui-border px-5 py-4">{{ $tenants->links() }}</div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="viewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <div class="ui-card w-full max-w-2xl p-6">
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Tenant Profile</h2><button type="button" @click="viewOpen = false" class="text-xl ui-muted">x</button></div>
                <dl class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                    <div><dt class="ui-muted">Tenant</dt><dd class="font-semibold" x-text="selected.tenant_name"></dd></div>
                    <div><dt class="ui-muted">Email</dt><dd x-text="selected.tenant_email"></dd></div>
                    <div><dt class="ui-muted">Student ID</dt><dd x-text="selected.student_id || 'Not set'"></dd></div>
                    <div><dt class="ui-muted">School</dt><dd x-text="selected.school_company || 'Not set'"></dd></div>
                    <div><dt class="ui-muted">Course / Position</dt><dd x-text="selected.course_or_position || 'Not set'"></dd></div>
                    <div><dt class="ui-muted">Valid ID</dt><dd x-text="`${selected.valid_id_type || 'ID'} ${selected.valid_id_number || ''}`"></dd></div>
                    <div><dt class="ui-muted">Emergency Contact</dt><dd x-text="`${selected.emergency_contact_name || 'Not set'} ${selected.emergency_contact_number || ''}`"></dd></div>
                    <div><dt class="ui-muted">Verification</dt><dd x-text="selected.id_verified ? 'Verified' : 'Needs Review'"></dd></div>
                </dl>
                <div class="mt-6 flex justify-end"><button type="button" @click="viewOpen = false" class="btn-secondary">Close</button></div>
            </div>
        </div>

        <div data-modal-root role="dialog" aria-modal="true" x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/75 p-4 backdrop-blur-sm">
            <form method="POST" :action="selected.update_url" class="ui-card max-h-[90vh] w-full max-w-2xl overflow-y-auto p-6">
                @csrf @method('PATCH')
                <div class="flex items-center justify-between"><h2 class="text-lg font-semibold">Edit Tenant Profile</h2><button type="button" @click="editOpen = false" class="text-xl ui-muted">x</button></div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="text-sm">Student ID<input name="student_id" class="ui-input mt-1" :value="selected.student_id"></label>
                    <label class="text-sm">School / Company<input name="school_company" class="ui-input mt-1" :value="selected.school_company"></label>
                    <label class="text-sm md:col-span-2">Course / Position<input name="course_or_position" class="ui-input mt-1" :value="selected.course_or_position"></label>
                    <label class="text-sm">Valid ID Type<input name="valid_id_type" class="ui-input mt-1" :value="selected.valid_id_type"></label>
                    <label class="text-sm">Valid ID Number<input name="valid_id_number" class="ui-input mt-1" :value="selected.valid_id_number"></label>
                    <label class="text-sm">Emergency Contact<input name="emergency_contact_name" class="ui-input mt-1" :value="selected.emergency_contact_name"></label>
                    <label class="text-sm">Emergency Number<input name="emergency_contact_number" class="ui-input mt-1" :value="selected.emergency_contact_number"></label>
                    <label class="text-sm md:col-span-2">Preferred Language<input name="preferred_language" class="ui-input mt-1" :value="selected.preferred_language"></label>
                    <label class="md:col-span-2 flex items-center gap-2 text-sm"><input type="hidden" name="id_verified" value="0"><input type="checkbox" name="id_verified" value="1" :checked="selected.id_verified"> Mark as verified</label>
                </div>
                <div class="mt-6 flex justify-end gap-2"><button type="button" @click="editOpen = false" class="btn-secondary">Cancel</button><button class="btn-primary">Save Profile</button></div>
            </form>
        </div>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
