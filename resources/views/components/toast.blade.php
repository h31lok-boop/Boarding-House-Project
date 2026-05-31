@php
    $statusLabels = [
        'tenant-match-profile-updated' => 'Preferences saved successfully.',
        'tenant-account-updated'        => 'Profile updated successfully.',
        'password-updated'              => 'Password updated successfully.',
        'profile-updated'               => 'Profile updated successfully.',
        'verification-link-sent'        => 'Verification link sent to your email.',
    ];

    $toasts = [];

    if (session('success'))
        $toasts[] = ['type' => 'success', 'msg' => session('success')];

    if (session('error'))
        $toasts[] = ['type' => 'error', 'msg' => session('error')];

    if (session('warning'))
        $toasts[] = ['type' => 'warning', 'msg' => session('warning')];

    if (session('info'))
        $toasts[] = ['type' => 'info', 'msg' => session('info')];

    if (session('status')) {
        $raw = session('status');
        $msg = $statusLabels[$raw] ?? str_replace('-', ' ', ucfirst($raw));
        // Only add if not already a duplicate of session('success')
        if (!session('success'))
            $toasts[] = ['type' => 'success', 'msg' => $msg];
    }

    if ($errors->any())
        $toasts[] = ['type' => 'error', 'msg' => $errors->first()];
@endphp

@if(count($toasts))
<div
    aria-live="assertive"
    class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none"
    style="width:360px;max-width:calc(100vw - 2rem)">

    @foreach($toasts as $i => $toast)
    @php
        $cfg = match($toast['type']) {
            'success' => [
                'bg'     => '#f0fdf4',
                'border' => '#bbf7d0',
                'text'   => '#15803d',
                'bar'    => '#22c55e',
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            'error' => [
                'bg'     => '#fff1f2',
                'border' => '#fecdd3',
                'text'   => '#be123c',
                'bar'    => '#f43f5e',
                'icon'   => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
            ],
            'warning' => [
                'bg'     => '#fffbeb',
                'border' => '#fde68a',
                'text'   => '#b45309',
                'bar'    => '#f59e0b',
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',
            ],
            default => [
                'bg'     => '#f0f9ff',
                'border' => '#bae6fd',
                'text'   => '#0369a1',
                'bar'    => '#0ea5e9',
                'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>',
            ],
        };
    @endphp

    <div
        x-data="{
            show: true,
            progress: 100,
            _timer: null,
            _raf: null,
            _start: null,
            duration: 5000,
            init() {
                this._start = Date.now();
                const tick = () => {
                    const elapsed = Date.now() - this._start;
                    this.progress = Math.max(0, 100 - (elapsed / this.duration * 100));
                    if (this.progress > 0) {
                        this._raf = requestAnimationFrame(tick);
                    } else {
                        this.dismiss();
                    }
                };
                this._raf = requestAnimationFrame(tick);
            },
            pause() {
                cancelAnimationFrame(this._raf);
                this.duration = this.duration * (this.progress / 100);
            },
            resume() {
                this._start = Date.now();
                const tick = () => {
                    const elapsed = Date.now() - this._start;
                    this.progress = Math.max(0, 100 - (elapsed / this.duration * 100));
                    if (this.progress > 0) {
                        this._raf = requestAnimationFrame(tick);
                    } else {
                        this.dismiss();
                    }
                };
                this._raf = requestAnimationFrame(tick);
            },
            dismiss() {
                cancelAnimationFrame(this._raf);
                this.show = false;
            }
        }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-x-8 scale-95"
        x-transition:enter-end="opacity-100 translate-x-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0 scale-100"
        x-transition:leave-end="opacity-0 translate-x-8 scale-95"
        @mouseenter="pause()"
        @mouseleave="resume()"
        class="pointer-events-auto overflow-hidden rounded-xl border shadow-lg"
        style="background:{{ $cfg['bg'] }};border-color:{{ $cfg['border'] }}"
        role="alert">

        {{-- Content --}}
        <div class="flex items-start gap-3 px-4 py-3.5">
            <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                 stroke="{{ $cfg['text'] }}" stroke-width="2">{!! $cfg['icon'] !!}</svg>
            <p class="flex-1 text-sm font-medium leading-snug" style="color:{{ $cfg['text'] }}">
                {{ $toast['msg'] }}
            </p>
            <button @click="dismiss()" class="shrink-0 rounded p-0.5 opacity-60 hover:opacity-100 transition-opacity" style="color:{{ $cfg['text'] }}" aria-label="Close">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Progress bar --}}
        <div class="h-0.5 w-full" style="background:{{ $cfg['border'] }}">
            <div class="h-full transition-none" style="background:{{ $cfg['bar'] }};width:100%" :style="'width:'+progress+'%'"></div>
        </div>
    </div>
    @endforeach

</div>
@endif
