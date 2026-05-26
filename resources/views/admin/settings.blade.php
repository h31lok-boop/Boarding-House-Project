<x-layouts.dashboard>
<x-admin.shell>
    <div class="space-y-6">
        <div class="ui-card p-6">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--brand-600)]">Settings</p>
            <h1 class="mt-2 text-2xl font-bold">Admin and Owner Settings</h1>
            <p class="mt-2 text-sm ui-muted">Manage profile, security, privacy, backup, and restore options from one clean settings page.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-5">
            @foreach (['profile' => 'Profile', 'security' => 'Security', 'privacy' => 'Privacy', 'backup' => 'Backup', 'restore' => 'Restore'] as $id => $label)
                <a href="#{{ $id }}" class="ui-card p-4 text-center text-sm font-semibold hover:-translate-y-0.5 transition">{{ $label }}</a>
            @endforeach
        </div>

        <section id="profile" class="ui-card p-6 scroll-mt-24">
            <h2 class="text-lg font-semibold">Profile</h2>
            <p class="mt-2 text-sm ui-muted">Update the signed-in Admin/Owner account information.</p>
            <form method="POST" action="{{ route('admin.settings.profile') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf @method('PATCH')
                <label class="text-sm">Name<input name="name" required value="{{ auth()->user()->name }}" class="ui-input mt-1"></label>
                <label class="text-sm">Email<input name="email" type="email" required value="{{ auth()->user()->email }}" class="ui-input mt-1"></label>
                <label class="text-sm md:col-span-2">Phone<input name="phone" value="{{ auth()->user()->phone ?? auth()->user()->contact_number }}" class="ui-input mt-1"></label>
                <div class="md:col-span-2 flex justify-end"><button class="btn-primary">Save Profile</button></div>
            </form>
        </section>

        <section id="security" class="ui-card p-6 scroll-mt-24">
            <h2 class="text-lg font-semibold">Security</h2>
            <p class="mt-2 text-sm ui-muted">Change password and keep dashboard access protected.</p>
            <form method="POST" action="{{ route('admin.settings.security') }}" class="mt-5 grid gap-4 md:grid-cols-3">
                @csrf @method('PATCH')
                <label class="text-sm">Current Password<input name="current_password" type="password" required class="ui-input mt-1"></label>
                <label class="text-sm">New Password<input name="password" type="password" required minlength="8" class="ui-input mt-1"></label>
                <label class="text-sm">Confirm Password<input name="password_confirmation" type="password" required minlength="8" class="ui-input mt-1"></label>
                <div class="md:col-span-3 flex justify-end"><button class="btn-primary">Update Security</button></div>
            </form>
        </section>

        <section id="privacy" class="ui-card p-6 scroll-mt-24">
            <h2 class="text-lg font-semibold">Privacy</h2>
            <p class="mt-2 text-sm ui-muted">Control visibility and data handling preferences for admin-managed records.</p>
            <form method="POST" action="{{ route('admin.settings.action') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="action" value="save_privacy">
                <label class="flex items-center gap-3 text-sm"><input type="checkbox" checked> Hide tenant contact details in exported reports</label>
                <label class="flex items-center gap-3 text-sm"><input type="checkbox" checked> Require role-based access for management records</label>
                <label class="flex items-center gap-3 text-sm"><input type="checkbox"> Allow owner contact details on public listings</label>
                <div class="flex justify-end"><button class="btn-primary">Save Privacy</button></div>
            </form>
        </section>

        <section id="backup" class="ui-card p-6 scroll-mt-24">
            <h2 class="text-lg font-semibold">Backup</h2>
            <p class="mt-2 text-sm ui-muted">Record backup actions for listings, rooms, users, transactions, and reports.</p>
            <form method="POST" action="{{ route('admin.settings.action') }}" class="mt-5 flex flex-wrap gap-3">
                @csrf
                <input type="hidden" name="action" value="backup">
                <button class="btn-primary">Run Backup</button>
                <button type="button" onclick="window.print()" class="btn-secondary">Print Backup Log</button>
            </form>
        </section>

        <section id="restore" class="ui-card p-6 scroll-mt-24">
            <h2 class="text-lg font-semibold">Restore</h2>
            <p class="mt-2 text-sm ui-muted">Request a restore review before applying recovered data to active records.</p>
            <form method="POST" action="{{ route('admin.settings.action') }}" class="mt-5 flex flex-wrap gap-3">
                @csrf
                <input type="hidden" name="action" value="restore">
                <button class="btn-primary">Request Restore</button>
                <a href="{{ route('admin.reports') }}" class="btn-secondary">Review Reports First</a>
            </form>
        </section>
    </div>
</x-admin.shell>
</x-layouts.dashboard>
