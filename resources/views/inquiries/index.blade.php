<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inquiries</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold">Tenant Inquiries</h3>
                        <div class="flex gap-2">
                            <select id="statusFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="reviewed">Reviewed</option>
                                <option value="responded">Responded</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    </div>

                    @if($inquiries->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500">No inquiries yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-600">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold">Inquiry #</th>
                                        <th class="px-6 py-3 font-semibold">Tenant</th>
                                        <th class="px-6 py-3 font-semibold">Boarding House</th>
                                        <th class="px-6 py-3 font-semibold">Message Preview</th>
                                        <th class="px-6 py-3 font-semibold">Priority</th>
                                        <th class="px-6 py-3 font-semibold">Status</th>
                                        <th class="px-6 py-3 font-semibold">Created</th>
                                        <th class="px-6 py-3 font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inquiries as $inquiry)
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $inquiry->inquiry_number }}</td>
                                            <td class="px-6 py-4">{{ $inquiry->user?->name ?? 'Unknown' }}</td>
                                            <td class="px-6 py-4">{{ $inquiry->boardingHouse?->name ?? 'Unknown' }}</td>
                                            <td class="px-6 py-4 max-w-xs truncate text-gray-700">{{ Str::limit($inquiry->message, 50) }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @if($inquiry->priority === 'high') bg-red-100 text-red-800
                                                    @elseif($inquiry->priority === 'normal') bg-blue-100 text-blue-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($inquiry->priority) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    @if($inquiry->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($inquiry->status === 'reviewed') bg-blue-100 text-blue-800
                                                    @elseif($inquiry->status === 'responded') bg-green-100 text-green-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($inquiry->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">{{ $inquiry->created_at?->format('M d, Y') }}</td>
                                            <td class="px-6 py-4">
                                                @if(auth()->user()->role === 'owner')
                                                    <a href="{{ route('owner.inquiries.show', $inquiry->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                                                @elseif(auth()->user()->role === 'caretaker')
                                                    <a href="{{ route('caretaker.inquiries.show', $inquiry->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                                                @else
                                                    <a href="{{ route('admin.inquiries.show', $inquiry->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $inquiries->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
