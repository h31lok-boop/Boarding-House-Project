<x-layouts.dashboard>
<x-user.shell>
@php
    $imageFor = function ($house, int $index): string {
        $path = $house?->images?->first()?->image_path
            ?? $house?->featured_image
            ?? $house?->exterior_image
            ?? null;
        if ($path) {
            return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
                ? $path
                : \Illuminate\Support\Facades\Storage::url($path);
        }
        return 'https://placehold.co/48x48/e0e7ff/6366f1?text=BH';
    };

    $avatarFor = function (string $name): string {
        $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=6366f1&color=fff&size=48&bold=true';
    };

    $threads = $messages->getCollection()->values()->map(function ($message, int $index) use ($imageFor, $avatarFor) {
        $house = $message->boardingHouse;
        $ownerName = $house?->owner?->name ?? 'Property Owner';
        return [
            'id'          => $message->id,
            'house_id'    => $house?->id,
            'owner_name'  => $ownerName,
            'owner_role'  => 'Property Owner',
            'property'    => $house?->name ?? 'BoardMatch Support',
            'address'     => $house?->address ?? '',
            'room_type'   => 'Private Room',
            'price'       => $house ? '₱3,500.00' : '',
            'ref'         => 'PAY-HAZEL-'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            'status'      => ucfirst((string)($message->status ?: 'Pending')),
            'message'     => $message->message,
            'time'        => optional($message->created_at)->format('M d') ?? '',
            'time_full'   => optional($message->created_at)->format('h:i A') ?? '',
            'house_image' => $imageFor($house, $index),
            'avatar'      => $avatarFor($ownerName),
            'unread'      => $index < 2 ? (2 - $index) : 0,
            'online'      => in_array($index, [3, 5]),
            'details_url' => $house ? route('user.browse.show', $house) : route('user.messages'),
        ];
    });

    // Sample conversations when DB is empty
    if ($threads->isEmpty()) {
        $threads = collect([
            ['id'=>1,'house_id'=>null,'owner_name'=>'Maria Santos','owner_role'=>'Property Owner','property'=>'Sunrise Student Boarding House','address'=>'123 Sunrise St., Baguio City','room_type'=>'Private Room','price'=>'₱3,500.00','ref'=>'PAY-HAZEL-005','status'=>'Pending','message'=>"Thank you for your message! Yes, the room is still available.",'time'=>'10:24 AM','time_full'=>'10:24 AM','house_image'=>'https://placehold.co/64x64/e0e7ff/6366f1?text=BH','avatar'=>'https://ui-avatars.com/api/?name=Maria+Santos&background=6366f1&color=fff&size=48','unread'=>2,'online'=>true,'details_url'=>'#'],
            ['id'=>2,'house_id'=>null,'owner_name'=>'James Lorenzo','owner_role'=>'Property Owner','property'=>'Greenview Boarding House','address'=>'','room_type'=>'Shared Room','price'=>'₱4,000.00','ref'=>'PAY-HAZEL-002','status'=>'Pending','message'=>"We can schedule a viewing this weekend. What time works ...",'time'=>'Yesterday','time_full'=>'','house_image'=>'https://placehold.co/64x64/dcfce7/16a34a?text=BH','avatar'=>'https://ui-avatars.com/api/?name=James+Lorenzo&background=16a34a&color=fff&size=48','unread'=>1,'online'=>false,'details_url'=>'#'],
            ['id'=>3,'house_id'=>null,'owner_name'=>'Ace De Guzman','owner_role'=>'Property Manager','property'=>'Maplewood Residences','address'=>'','room_type'=>'Single Room','price'=>'₱3,800.00','ref'=>'PAY-HAZEL-003','status'=>'Replied','message'=>"Noted on your requirements. I'll check and get back to you.",'time'=>'Yesterday','time_full'=>'','house_image'=>'https://placehold.co/64x64/fef9c3/ca8a04?text=BH','avatar'=>'https://ui-avatars.com/api/?name=Ace+De+Guzman&background=ca8a04&color=fff&size=48','unread'=>0,'online'=>false,'details_url'=>'#'],
            ['id'=>4,'house_id'=>null,'owner_name'=>'Liza Bautista','owner_role'=>'Property Owner','property'=>'Horizon Boarding Home','address'=>'','room_type'=>'Private Room','price'=>'₱4,200.00','ref'=>'PAY-HAZEL-004','status'=>'Replied','message'=>"Perfect! See you tomorrow at 3 PM.",'time'=>'Aug 04','time_full'=>'','house_image'=>'https://placehold.co/64x64/fce7f3/be185d?text=BH','avatar'=>'https://ui-avatars.com/api/?name=Liza+Bautista&background=be185d&color=fff&size=48','unread'=>0,'online'=>true,'details_url'=>'#'],
            ['id'=>5,'house_id'=>null,'owner_name'=>'Ryan Cruz','owner_role'=>'Property Owner','property'=>'Lakeview Boarding House','address'=>'','room_type'=>'Shared Room','price'=>'₱3,200.00','ref'=>'PAY-HAZEL-005','status'=>'Replied','message'=>"Sorry, that room is already taken.",'time'=>'Aug 03','time_full'=>'','house_image'=>'https://placehold.co/64x64/ede9fe/7c3aed?text=BH','avatar'=>'https://ui-avatars.com/api/?name=Ryan+Cruz&background=7c3aed&color=fff&size=48','unread'=>0,'online'=>false,'details_url'=>'#'],
            ['id'=>6,'house_id'=>null,'owner_name'=>'Patricia Reyes','owner_role'=>'Property Owner','property'=>'Oakridge Boarding House','address'=>'','room_type'=>'Studio','price'=>'₱4,500.00','ref'=>'PAY-HAZEL-006','status'=>'Replied','message'=>"Thank you! I'll send the requirements right away.",'time'=>'Aug 02','time_full'=>'','house_image'=>'https://placehold.co/64x64/fee2e2/dc2626?text=BH','avatar'=>'https://ui-avatars.com/api/?name=Patricia+Reyes&background=dc2626&color=fff&size=48','unread'=>0,'online'=>true,'details_url'=>'#'],
            ['id'=>7,'house_id'=>null,'owner_name'=>'Mark Joseph','owner_role'=>'Property Owner','property'=>'Westwood Boarding House','address'=>'','room_type'=>'Dormitory','price'=>'₱2,800.00','ref'=>'PAY-HAZEL-007','status'=>'Pending','message'=>"Hello! Is the room still available?",'time'=>'Aug 01','time_full'=>'','house_image'=>'https://placehold.co/64x64/e0f2fe/0284c7?text=BH','avatar'=>'https://ui-avatars.com/api/?name=Mark+Joseph&background=0284c7&color=fff&size=48','unread'=>0,'online'=>false,'details_url'=>'#'],
        ]);
    }

    $firstThread = $threads->first();
    $totalConversations = $messages->total() ?: $threads->count();
@endphp

<style>
.msg-bubble-me {
    background: #4f46e5;
    color: #fff;
    border-radius: 18px 18px 4px 18px;
}
.msg-bubble-them {
    background: #f3f4f6;
    color: #111827;
    border-radius: 18px 18px 18px 4px;
}
.conv-item { transition: background .15s; }
.conv-item:hover { background: #f9fafb; }
.conv-item.active { background: #eef2ff; }
.tab-btn {
    display:inline-flex;align-items:center;gap:5px;
    padding:6px 14px;border-radius:999px;font-size:13px;font-weight:600;
    border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;
    transition:all .15s;white-space:nowrap;
}
.tab-btn:hover { border-color:#6366f1;color:#6366f1; }
.tab-btn.active { background:#6366f1;border-color:#6366f1;color:#fff; }
.pg-btn{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;border-radius:6px;font-size:13px;font-weight:500;border:1px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;transition:all .15s;}
.pg-btn:hover{border-color:#6366f1;color:#6366f1;}
.pg-btn.active{border-color:#6366f1;color:#6366f1;font-weight:700;}
.pg-btn.arrow{border:none;background:transparent;color:#9ca3af;}
.pg-btn.arrow:hover{color:#6366f1;}
</style>

<div
    x-data="chatApp()"
    x-init="init()"
    class="space-y-4">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-gray-400">
        <a href="{{ route('user.dashboard') }}" class="hover:text-gray-600 transition-colors">Dashboard</a>
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">Messages</span>
    </nav>

    {{-- Title --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
        <p class="mt-1 text-sm text-gray-500">Communicate with property owners and managers.</p>
    </div>

    {{-- Chat layout --}}
    <div class="grid overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm xl:grid-cols-[420px_1fr]" style="min-height:600px">

        {{-- ══ LEFT: Conversations ══ --}}
        <div class="flex flex-col border-r border-gray-200" style="min-height:600px">

            {{-- Search + Filter --}}
            <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" placeholder="Search conversations..."
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2 pl-8 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>
                <button class="flex items-center gap-1.5 rounded-xl border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors shrink-0">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                    Filter
                </button>
            </div>

            {{-- Filter tabs --}}
            <div class="flex items-center gap-2 overflow-x-auto border-b border-gray-100 px-4 py-3 scrollbar-hide">
                <button @click="tab='all'" class="tab-btn" :class="tab==='all' ? 'active' : ''">
                    All <span x-text="conversations.length" class="text-[11px]"></span>
                </button>
                <button @click="tab='unread'" class="tab-btn" :class="tab==='unread' ? 'active' : ''">
                    Unread <span class="text-[11px]">4</span>
                </button>
                <button @click="tab='owners'" class="tab-btn" :class="tab==='owners' ? 'active' : ''">Owners</button>
                <button @click="tab='managers'" class="tab-btn" :class="tab==='managers' ? 'active' : ''">Managers</button>
                <button @click="tab='archived'" class="tab-btn" :class="tab==='archived' ? 'active' : ''">Archived</button>
            </div>

            {{-- Conversation list --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
                <template x-for="conv in filteredConversations" :key="conv.id">
                    <button type="button"
                            @click="setActive(conv)"
                            class="conv-item flex w-full items-start gap-3 px-4 py-4 text-left"
                            :class="active && active.id === conv.id ? 'active' : ''">
                        {{-- Avatar --}}
                        <div class="relative shrink-0">
                            <img :src="conv.avatar" :alt="conv.owner_name"
                                 class="h-11 w-11 rounded-full object-cover border border-gray-100">
                            <span x-show="conv.online"
                                  class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                        </div>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-gray-900 truncate" x-text="conv.owner_name"></span>
                                <span class="shrink-0 text-[11px] text-gray-400" x-text="conv.time"></span>
                            </div>
                            <p class="text-xs font-medium text-gray-500 truncate mt-0.5" x-text="conv.property"></p>
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <p class="text-xs text-gray-400 truncate" x-text="conv.message"></p>
                                <span x-show="conv.unread > 0"
                                      class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-orange-500 text-[10px] font-bold text-white"
                                      x-text="conv.unread"></span>
                            </div>
                        </div>
                    </button>
                </template>
                <template x-if="filteredConversations.length === 0">
                    <div class="py-12 text-center text-sm text-gray-400">No conversations found.</div>
                </template>
            </div>

            {{-- Pagination footer --}}
            <div class="border-t border-gray-100 px-4 py-3 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    Showing 1 to <span x-text="filteredConversations.length"></span> of
                    <span x-text="conversations.length"></span> conversations
                </p>
                <div class="flex items-center gap-1">
                    <button class="pg-btn arrow"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg></button>
                    <span class="pg-btn active">1</span>
                    <button class="pg-btn">2</button>
                    <button class="pg-btn arrow"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg></button>
                </div>
            </div>
        </div>

        {{-- ══ RIGHT: Chat panel ══ --}}
        <div class="flex flex-col" style="min-height:600px">
            <template x-if="active">
                <div class="flex flex-col h-full">

                    {{-- Chat header --}}
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <img :src="active.avatar" :alt="active.owner_name"
                                     class="h-10 w-10 rounded-full object-cover border border-gray-100">
                                <span x-show="active.online" class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-green-400"></span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900" x-text="active.owner_name"></p>
                                <p class="text-xs text-gray-400" x-text="active.owner_role"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="#" class="flex items-center gap-1.5 text-sm font-semibold text-gray-600 hover:text-gray-800 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                View Profile
                            </a>
                            <button class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Property info card --}}
                    <div class="border-b border-gray-100 px-5 py-3">
                        <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3">
                            <img :src="active.house_image" :alt="active.property"
                                 class="h-16 w-20 shrink-0 rounded-lg object-cover border border-gray-200">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-bold text-gray-900 leading-snug" x-text="active.property"></p>
                                    <div class="text-right shrink-0">
                                        <p class="text-sm font-bold text-orange-500" x-text="active.price+' / month'"></p>
                                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-semibold text-orange-600">Pending</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-0.5" x-text="active.ref"></p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-3 text-[11px] text-gray-500">
                                    <span x-show="active.address" class="flex items-center gap-1">
                                        <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                        <span x-text="active.address || '123 Sunrise St., Baguio City'"></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/></svg>
                                        <span x-text="active.room_type || 'Private Room'"></span>
                                    </span>
                                    <a :href="active.details_url" class="text-indigo-600 font-semibold hover:underline">View Listing</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Messages area --}}
                    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4" id="chatMessages">

                        {{-- Date separator --}}
                        <div class="flex items-center gap-3">
                            <div class="flex-1 border-t border-gray-100"></div>
                            <span class="text-[11px] font-medium text-gray-400">Today</span>
                            <div class="flex-1 border-t border-gray-100"></div>
                        </div>

                        {{-- Messages --}}
                        <template x-for="(msg, idx) in currentMessages" :key="idx">
                            <div :class="msg.from === 'me' ? 'flex justify-end' : 'flex justify-start items-end gap-2.5'">

                                {{-- Owner avatar (received) --}}
                                <template x-if="msg.from !== 'me'">
                                    <img :src="active.avatar" class="h-8 w-8 rounded-full object-cover border border-gray-100 shrink-0 mb-0.5">
                                </template>

                                <div :class="msg.from === 'me' ? 'max-w-sm' : 'max-w-sm'">
                                    <div :class="msg.from === 'me' ? 'msg-bubble-me' : 'msg-bubble-them'"
                                         class="px-4 py-2.5 text-sm leading-relaxed"
                                         x-text="msg.text">
                                    </div>
                                    <div :class="msg.from === 'me' ? 'flex items-center justify-end gap-1 mt-1' : 'mt-1 ml-1'">
                                        <span class="text-[11px] text-gray-400" x-text="msg.time"></span>
                                        <template x-if="msg.from === 'me'">
                                            <svg class="h-3 w-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Message input --}}
                    <div class="border-t border-gray-100 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <button class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                            </button>
                            <form method="POST" action="{{ route('user.messages.store') }}" class="flex flex-1 items-center gap-2">
                                @csrf
                                <input type="hidden" name="boarding_house_id" :value="active.house_id">
                                <input name="message" required
                                       class="flex-1 rounded-full border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition"
                                       placeholder="Type your message...">
                                <button type="submit"
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>

            {{-- No conversation selected --}}
            <template x-if="!active">
                <div class="flex flex-1 items-center justify-center p-10 text-center" style="min-height:600px">
                    <div>
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50">
                            <svg class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                        </div>
                        <p class="text-base font-semibold text-gray-700 mb-1">Select a conversation</p>
                        <p class="text-sm text-gray-400">Choose a conversation from the list to start chatting.</p>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function chatApp() {
    const threads = {!! json_encode($threads->values()->all()) !!};

    const sampleChats = {
        default: [
            { from: 'owner', text: "Hello! I saw your inquiry about the available room. Is there anything specific you'd like to know?", time: '10:12 AM' },
            { from: 'me',    text: "Hi Maria! Yes, I'd like to know if the room is still available and if electricity and water are included in the rent.", time: '10:15 AM', seen: true },
            { from: 'owner', text: "Thank you for your message! Yes, the room is still available. Electricity and water are included in the rent.", time: '10:20 AM' },
            { from: 'me',    text: "That's great to hear! Can we schedule a viewing?", time: '10:22 AM', seen: true },
            { from: 'owner', text: "Absolutely! How about this Saturday at 2 PM?", time: '10:24 AM' },
            { from: 'me',    text: "Perfect! See you then. Thank you!", time: '10:24 AM', seen: true },
        ],
    };

    return {
        conversations: threads,
        active: threads.length ? threads[0] : null,
        tab: 'all',

        init() {
            if (this.conversations.length) this.active = this.conversations[0];
        },

        get filteredConversations() {
            if (this.tab === 'unread')   return this.conversations.filter(c => c.unread > 0);
            if (this.tab === 'owners')   return this.conversations.filter(c => c.owner_role === 'Property Owner');
            if (this.tab === 'managers') return this.conversations.filter(c => c.owner_role === 'Property Manager');
            if (this.tab === 'archived') return [];
            return this.conversations;
        },

        get currentMessages() {
            if (!this.active) return [];
            const inquiry = { from: 'me', text: this.active.message, time: this.active.time_full || '10:15 AM', seen: true };
            const base = sampleChats.default;
            // replace the "me" entry with the actual message
            return base.map((m, i) => i === 1 ? { ...m, text: this.active.message } : m);
        },

        setActive(conv) {
            this.active = conv;
            this.$nextTick(() => {
                const el = document.getElementById('chatMessages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    };
}
</script>
</x-user.shell>
</x-layouts.dashboard>
