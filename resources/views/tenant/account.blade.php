<x-app-layout main-class="w-full">
    @php
        $statusLabel = $tenantStatus ?? 'Pending';
        $statusIsApproved = strtolower($statusLabel) === 'approved';
        $statusClasses = $statusIsApproved
            ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
            : 'bg-amber-100 text-amber-700 border-amber-200';

        $booking = $bookingDetails ?? [];
        $billing = $billingHistory ?? ['enabled' => false, 'total_paid' => 0, 'outstanding' => 0, 'items' => []];
        $notifications = $notificationPreferences ?? [];
        $profileImageUrl = $tenant->profile_image ? \Illuminate\Support\Facades\Storage::url($tenant->profile_image) : '';
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">Tenant Account</h2>
            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                Status: {{ $statusLabel }}
            </span>
        </div>
    </x-slot>

    <div class="mx-auto w-full max-w-6xl space-y-6">
        <section class="grid grid-cols-1 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] gap-6">
            <div class="space-y-6">
                <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <div class="mb-4">
                        <h3 class="text-base font-semibold text-gray-900">Profile</h3>
                        <p class="text-sm text-gray-500">Update your account profile, avatar, and reminder preferences.</p>
                    </div>

                    <form method="POST" action="{{ route('tenant.account.update') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PATCH')

                        <x-profile-image-uploader
                            label=""
                            name="profile_image"
                            :initial="$profileImageUrl"
                            :max-size-kb="5120"
                            :size="96"
                            :circle="true"
                        />
                        <x-input-error :messages="$errors->get('profile_image')" class="mt-1" />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="tenant_account_name" value="Full Name" />
                                <x-text-input
                                    id="tenant_account_name"
                                    name="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('name', $tenant->name)"
                                    required
                                />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="tenant_account_phone" value="Phone Number" />
                                <x-text-input
                                    id="tenant_account_phone"
                                    name="phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :value="old('phone', $tenant->phone)"
                                />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="tenant_account_email" value="Email Address" />
                            <x-text-input
                                id="tenant_account_email"
                                name="email"
                                type="email"
                                class="mt-1 block w-full"
                                :value="old('email', $tenant->email)"
                                required
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <h4 class="text-sm font-semibold text-gray-900">Notifications</h4>
                            <p class="text-xs text-gray-500 mt-1">Control which reminders you receive.</p>

                            <div class="mt-3 space-y-3">
                                <label class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-gray-700">Payment reminders</span>
                                    <input
                                        type="checkbox"
                                        name="notify_payment_reminders"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        @checked(old('notify_payment_reminders', $notifications['payment_reminders'] ?? true))
                                    >
                                </label>
                                <label class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-gray-700">Booking and room updates</span>
                                    <input
                                        type="checkbox"
                                        name="notify_booking_updates"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        @checked(old('notify_booking_updates', $notifications['booking_updates'] ?? true))
                                    >
                                </label>
                                <label class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-gray-700">Support ticket updates</span>
                                    <input
                                        type="checkbox"
                                        name="notify_ticket_updates"
                                        value="1"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        @checked(old('notify_ticket_updates', $notifications['ticket_updates'] ?? true))
                                    >
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Save Changes</x-primary-button>

                            @if (session('status') === 'tenant-account-updated')
                                <p class="text-sm font-medium text-emerald-600">Account updated successfully.</p>
                            @endif
                        </div>
                    </form>
                </article>

                <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">Security</h3>
                    <p class="text-sm text-gray-500 mt-1">Change your account password.</p>

                    <form method="POST" action="{{ route('password.update') }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="tenant_current_password" value="Current Password" />
                            <x-text-input
                                id="tenant_current_password"
                                name="current_password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="current-password"
                            />
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tenant_new_password" value="New Password" />
                            <x-text-input
                                id="tenant_new_password"
                                name="password"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tenant_new_password_confirmation" value="Confirm New Password" />
                            <x-text-input
                                id="tenant_new_password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                autocomplete="new-password"
                            />
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>Update Password</x-primary-button>

                            @if (session('status') === 'password-updated')
                                <p class="text-sm font-medium text-emerald-600">Password updated successfully.</p>
                            @endif
                        </div>
                    </form>
                </article>
            </div>

            <div class="space-y-6">
                <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900">Booking / Room Details</h3>
                    <div class="mt-4 space-y-3 text-sm text-gray-700">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Boarding House</p>
                            <p class="font-semibold text-gray-900">{{ $booking['boarding_house'] ?? 'Not assigned' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Room No.</p>
                            <p class="font-semibold text-gray-900">{{ $booking['room_number'] ?? 'Not assigned' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Booking Reference</p>
                            <p class="font-semibold text-gray-900">{{ $booking['booking_reference'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Booking Status</p>
                            <p class="font-semibold text-gray-900">{{ $booking['status'] ?? $statusLabel }}</p>
                        </div>
                        @if (!empty($booking['move_in_date']))
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Move-in Date</p>
                                <p class="font-semibold text-gray-900">{{ $booking['move_in_date'] }}</p>
                            </div>
                        @endif
                        @if (!empty($booking['address']))
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-400">Address</p>
                                <p class="font-semibold text-gray-900">{{ $booking['address'] }}</p>
                            </div>
                        @endif
                    </div>
                </article>

            </div>
        </section>

        <article class="rounded-2xl border border-gray-100 bg-white shadow-sm p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Billing History</h3>
                    <p class="text-sm text-gray-500">Payment records and receipt links.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Total Paid</p>
                    <p class="text-sm font-semibold text-emerald-700">PHP {{ number_format((float) ($billing['total_paid'] ?? 0), 2) }}</p>
                    <p class="text-xs uppercase tracking-wide text-gray-400 mt-2">Outstanding</p>
                    <p class="text-sm font-semibold text-rose-700">PHP {{ number_format((float) ($billing['outstanding'] ?? 0), 2) }}</p>
                </div>
            </div>

            @if (!$billing['enabled'])
                <p class="mt-4 text-sm text-gray-500">Billing module is not configured yet.</p>
            @elseif (empty($billing['items']))
                <p class="mt-4 text-sm text-gray-500">No billing records found.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500">
                                <th class="py-2 pr-4">Reference</th>
                                <th class="py-2 pr-4">Amount</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Due Date</th>
                                <th class="py-2 pr-4">Paid Date</th>
                                <th class="py-2">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($billing['items'] as $item)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 pr-4 font-semibold text-gray-900">{{ $item['reference'] }}</td>
                                    <td class="py-2 pr-4 text-gray-700">PHP {{ number_format((float) $item['amount'], 2) }}</td>
                                    <td class="py-2 pr-4">
                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold {{ strtolower($item['status']) === 'paid' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200' }}">
                                            {{ $item['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-2 pr-4 text-gray-600">{{ $item['due_date'] ?? '-' }}</td>
                                    <td class="py-2 pr-4 text-gray-600">{{ $item['paid_date'] ?? '-' }}</td>
                                    <td class="py-2">
                                        @if (!empty($item['receipt_url']))
                                            <a href="{{ $item['receipt_url'] }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                                View Receipt
                                            </a>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </div>
</x-app-layout>
