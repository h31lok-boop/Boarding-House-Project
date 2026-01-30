<x-guest-layout>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-lg border border-gray-100 p-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div>
                    <p class="text-sm uppercase tracking-[0.3em] text-gray-400">Authentication</p>
                    <h1 class="text-2xl font-semibold text-gray-900">Welcome back</h1>
                    <p class="text-sm text-gray-500">Choose how you want to continue.</p>
                </div>
                <a href="{{ url('/') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">← Back to home</a>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <a href="{{ route('login') }}" class="group block rounded-xl border border-gray-200 hover:border-indigo-200 shadow-sm hover:shadow-md transition bg-white p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-700 grid place-items-center text-xl font-bold">IN</div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Login</h2>
                            <p class="text-sm text-gray-500">Access your existing account.</p>
                        </div>
                    </div>
                    <div class="mt-4 text-sm font-semibold text-indigo-600 group-hover:text-indigo-700">Continue to login →</div>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
