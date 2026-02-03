<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boarding House Applications</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3 text-left">Tenant</th>
                            <th class="px-5 py-3 text-left">Email</th>
                            <th class="px-5 py-3 text-left">Boarding House</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($applications as $application)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $application->user->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $application->user->email }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $application->boardingHouse->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                                        @if($application->status === 'approved') bg-emerald-100 text-emerald-700
                                        @elseif($application->status === 'rejected') bg-rose-100 text-rose-700
                                        @else bg-amber-100 text-amber-700 @endif">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right space-x-2">
                                    @if($application->status === 'pending')
                                        <form action="{{ route('admin.applications.approve', $application) }}" method="POST" class="inline">
                                            @csrf
                                            <button class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-emerald-700">Approve</button>
                                        </form>
                                        <form action="{{ route('admin.applications.reject', $application) }}" method="POST" class="inline">
                                            @csrf
                                            <button class="bg-rose-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-rose-700">Reject</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-500">Action completed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-gray-500">No applications yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
