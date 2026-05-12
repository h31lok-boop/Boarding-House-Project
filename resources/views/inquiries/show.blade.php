<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inquiry {{ $inquiry->inquiry_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-semibold">{{ $inquiry->inquiry_number }}</h3>
                            <p class="text-sm text-gray-500 mt-1">From: {{ $inquiry->user?->name ?? 'Unknown' }}</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                @if($inquiry->priority === 'high') bg-red-100 text-red-800
                                @elseif($inquiry->priority === 'normal') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($inquiry->priority) }} Priority
                            </span>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                @if($inquiry->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($inquiry->status === 'reviewed') bg-blue-100 text-blue-800
                                @elseif($inquiry->status === 'responded') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($inquiry->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <div class="grid grid-cols-2 gap-6 mb-6">
                            <div>
                                <p class="text-sm text-gray-500">Boarding House</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $inquiry->boardingHouse?->name ?? 'Unknown' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tenant Email</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $inquiry->user?->email ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Created</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $inquiry->created_at?->format('M d, Y - g:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Replied</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $inquiry->replied_at?->format('M d, Y - g:i A') ?? 'Not replied' }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <p class="text-sm text-gray-500 mb-2">Message</p>
                            <div class="bg-gray-50 rounded-lg p-4 text-gray-900">
                                {{ $inquiry->message }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Update Inquiry</h3>

                    @if(session('status'))
                        <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="@if(auth()->user()->role === 'owner'){{ route('owner.inquiries.update', $inquiry->id) }}@elseif(auth()->user()->role === 'caretaker'){{ route('caretaker.inquiries.update', $inquiry->id) }}@else{{ route('admin.inquiries.update', $inquiry->id) }}@endif">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-semibold text-gray-900 mb-2">Status</label>
                                <select name="status" id="status" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900" required>
                                    <option value="pending" @selected($inquiry->status === 'pending')>Pending</option>
                                    <option value="reviewed" @selected($inquiry->status === 'reviewed')>Reviewed</option>
                                    <option value="responded" @selected($inquiry->status === 'responded')>Responded</option>
                                    <option value="closed" @selected($inquiry->status === 'closed')>Closed</option>
                                </select>
                                @error('status')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-semibold text-gray-900 mb-2">Priority</label>
                                <select name="priority" id="priority" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900" required>
                                    <option value="low" @selected($inquiry->priority === 'low')>Low</option>
                                    <option value="normal" @selected($inquiry->priority === 'normal')>Normal</option>
                                    <option value="high" @selected($inquiry->priority === 'high')>High</option>
                                </select>
                                @error('priority')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="replied_at" class="block text-sm font-semibold text-gray-900 mb-2">Mark as Replied (Optional)</label>
                                <input type="datetime-local" name="replied_at" id="replied_at" value="{{ $inquiry->replied_at?->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-gray-900">
                                @error('replied_at')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-3">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                                    Update Inquiry
                                </button>
                                <a href="@if(auth()->user()->role === 'owner'){{ route('owner.inquiries.index') }}@elseif(auth()->user()->role === 'caretaker'){{ route('caretaker.inquiries.index') }}@else{{ route('admin.inquiries.index') }}@endif" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg">
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
