<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Maintenance</h2>
    </x-slot>

    <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 p-6">
        <h3 class="text-base font-semibold text-gray-900">Request Summary</h3>
        @if($hasMaintenanceModule)
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm text-amber-700">Open Requests</p>
                    <p class="mt-1 text-2xl font-bold text-amber-900">{{ number_format($openRequestsCount) }}</p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm text-emerald-700">Resolved Requests</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-900">{{ number_format($resolvedRequestsCount) }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-gray-500">Detailed request management can be added here.</p>
        @else
            <p class="mt-3 text-sm text-gray-500">
                Maintenance module is not configured yet. Create a <code>maintenance_requests</code> table to enable tracking.
            </p>
        @endif
    </div>
</x-app-layout>
