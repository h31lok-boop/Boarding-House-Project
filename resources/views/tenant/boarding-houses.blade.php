<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boarding Houses</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Apply to a Boarding House</h3>
                <p class="text-sm text-gray-500">Choose an available house to request a slot.</p>
            </div>
            <div class="p-6">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif
                @if($availableHouses->isEmpty())
                    <p class="text-sm text-gray-500">No available boarding houses right now.</p>
                @else
                    <form method="POST" action="{{ route('tenant.boarding-houses.apply.select') }}">
                        @csrf
                        <label class="block text-sm text-gray-700 mb-2">Select Boarding House</label>
                        <select name="boarding_house_id" class="w-full border rounded-lg px-3 py-2 text-sm mb-4">
                            @foreach($availableHouses as $houseOption)
                                <option value="{{ $houseOption->id }}">
                                    {{ $houseOption->name }} ({{ $houseOption->tenants_count }}/{{ $houseOption->capacity }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">Submit Application</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
