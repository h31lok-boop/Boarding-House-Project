@php
    $aiService = app(\App\Services\OpenAIService::class);
    $assistantConfigured = $aiService->isConfigured();
    $assistantProvider = $aiService->providerLabel();
@endphp

<div
    x-data="boardmatchAssistant({
        endpoint: @js(route('assistant.ask')),
        configured: @js($assistantConfigured),
    })"
    @keydown.escape.window="close()"
    class="inline-flex shrink-0"
>
    <button
        type="button"
        @click="openAssistant()"
        class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-violet-200 bg-violet-50 text-violet-700 shadow-sm transition hover:border-violet-300 hover:bg-violet-100 focus:outline-none focus:ring-4 focus:ring-violet-100 dark:border-violet-400/20 dark:bg-violet-400/10 dark:text-violet-300 dark:hover:bg-violet-400/15 dark:focus:ring-violet-900"
        aria-label="Open BoardMatch AI assistant"
        title="Ask BoardMatch AI"
        :aria-expanded="open"
    >
        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 1.2 4.2a5 5 0 0 0 3.5 3.5L21 12l-4.3 1.3a5 5 0 0 0-3.5 3.5L12 21l-1.2-4.2a5 5 0 0 0-3.5-3.5L3 12l4.3-1.3a5 5 0 0 0 3.5-3.5L12 3Z"/>
            <path stroke-linecap="round" stroke-width="1.8" d="M19 3v4M17 5h4"/>
        </svg>
        <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full border-2 border-white dark:border-slate-950 {{ $assistantConfigured ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
    </button>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.150ms
            @click.self="close()"
            data-modal-root
            data-ai-assistant-modal
            role="dialog"
            aria-modal="true"
            aria-labelledby="boardmatch-ai-title"
            class="bm-modal-overlay"
        >
        <section class="bm-modal w-full max-w-lg overflow-hidden" @click.stop>
            <header class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 dark:border-slate-700">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 1.2 4.2a5 5 0 0 0 3.5 3.5L21 12l-4.3 1.3a5 5 0 0 0-3.5 3.5L12 21l-1.2-4.2a5 5 0 0 0-3.5-3.5L3 12l4.3-1.3a5 5 0 0 0 3.5-3.5L12 3Z"/></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 id="boardmatch-ai-title" class="truncate text-base font-black text-slate-950 dark:text-white">BoardMatch AI Assistant</h2>
                        <p class="mt-0.5 flex items-center gap-1.5 text-xs font-semibold {{ $assistantConfigured ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            <span class="h-2 w-2 rounded-full {{ $assistantConfigured ? 'bg-emerald-500' : 'bg-amber-400' }}"></span>
                            {{ $assistantConfigured ? 'Ready via '.$assistantProvider : 'API key required' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="resetConversation()" class="hidden rounded-xl px-3 py-2 text-xs font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-white sm:inline-flex">Clear chat</button>
                    <button type="button" @click="close()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close AI assistant">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>
            </header>

            <div x-ref="messages" class="h-[min(20rem,44dvh)] min-h-52 space-y-3 overflow-y-auto bg-slate-50/70 px-4 py-4 dark:bg-slate-900/50 sm:px-5">
                <template x-for="(message, index) in messages" :key="index">
                    <div class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                        <div
                            class="max-w-[88%] rounded-2xl px-3.5 py-2.5 text-sm leading-5 shadow-sm sm:max-w-[82%]"
                            :class="message.role === 'user'
                                ? 'rounded-br-md bg-blue-600 text-white'
                                : 'rounded-bl-md border border-slate-200 bg-white text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100'"
                        >
                            <p class="whitespace-pre-wrap break-words" x-text="message.content"></p>
                        </div>
                    </div>
                </template>

                <div x-show="loading" class="flex justify-start">
                    <div class="flex items-center gap-2 rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <span class="h-2 w-2 animate-bounce rounded-full bg-violet-500 [animation-delay:-0.3s]"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-violet-500 [animation-delay:-0.15s]"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-violet-500"></span>
                        <span class="ml-1">Thinking…</span>
                    </div>
                </div>
            </div>

            <footer class="border-t border-slate-100 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-950 sm:px-5 sm:py-4">
                <div x-show="error" x-transition class="mb-2.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300" x-text="error"></div>
                <form @submit.prevent="send()" class="flex items-end gap-2.5">
                    <label class="min-w-0 flex-1">
                        <span class="sr-only">Ask BoardMatch AI a question</span>
                        <textarea
                            x-ref="question"
                            x-model="question"
                            @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); send(); }"
                            rows="1"
                            maxlength="1200"
                            class="block max-h-28 min-h-11 w-full resize-none rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-3 text-sm leading-5 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-100 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:focus:border-violet-400 dark:focus:ring-violet-900"
                            placeholder="Ask about reservations, payments, listings, or using BoardMatch…"
                        ></textarea>
                    </label>
                    <button
                        type="submit"
                        :disabled="loading || question.trim().length < 2"
                        class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-blue-600 text-white shadow-md shadow-blue-500/20 transition hover:from-violet-500 hover:to-blue-500 focus:outline-none focus:ring-4 focus:ring-violet-200 disabled:cursor-not-allowed disabled:opacity-45 dark:focus:ring-violet-900"
                        aria-label="Send question"
                    >
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12 14-7-4 14-3-6-7-1Z"/><path stroke-linecap="round" stroke-width="1.8" d="m12 13 7-8"/></svg>
                    </button>
                </form>
                <p class="mt-2 text-center text-[10px] font-medium leading-4 text-slate-400">Answers use role-authorized records. Verify payment and availability details before acting.</p>
            </footer>
        </section>
        </div>
    </template>
</div>
