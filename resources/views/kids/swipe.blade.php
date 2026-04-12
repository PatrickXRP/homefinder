<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Swipe - HomeFinder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #111; min-height: 100dvh; overflow: hidden; }
        .swipe-btn { transition: all 0.15s; -webkit-tap-highlight-color: transparent; }
        .swipe-btn:active { transform: scale(0.85); }
        .card { touch-action: pan-y; }
        @keyframes slideOut { to { transform: translateX(100vw); opacity: 0; } }
        @keyframes slideIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .slide-in { animation: slideIn 0.3s ease-out; }
    </style>
</head>
<body class="flex flex-col h-[100dvh]">
    {{-- Top bar --}}
    <div class="flex items-center justify-between px-4 py-2 bg-black/50">
        <div class="flex items-center gap-2">
            <span class="text-2xl">{{ session('kid_emoji') }}</span>
            <span class="text-white font-bold">{{ session('kid_name') }}</span>
        </div>
        <div class="flex gap-2">
            <a href="/kids/swipe?mode=photo" class="px-3 py-1.5 rounded-full text-xs font-medium {{ $mode === 'photo' ? 'bg-white text-black' : 'bg-white/20 text-white' }}">📸 Foto</a>
            <a href="/kids/swipe?mode=specs" class="px-3 py-1.5 rounded-full text-xs font-medium {{ $mode === 'specs' ? 'bg-white text-black' : 'bg-white/20 text-white' }}">📊 Specs</a>
            <a href="/kids/huizen" class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-500/30 text-yellow-300">🏆</a>
        </div>
        <a href="/kids/logout" class="text-white/40 text-sm">Uit</a>
    </div>

    {{-- Progress --}}
    <div class="px-4 py-1">
        <div class="w-full bg-white/10 rounded-full h-1.5">
            <div class="h-1.5 rounded-full bg-green-500 transition-all" style="width: {{ $totalPhotos > 0 ? min(($swipedCount / $totalPhotos) * 100, 100) : 0 }}%"></div>
        </div>
        <div class="text-white/40 text-xs text-center mt-1">{{ $swipedCount }} / {{ $totalPhotos }} foto's · ronde {{ $round }}</div>
    </div>

    @if($current)
        {{-- Photo card --}}
        <div class="flex-1 flex flex-col card slide-in" id="swipe-card">
            <div class="flex-1 relative mx-2 rounded-3xl overflow-hidden bg-gray-900">
                <img src="{{ $current['image_url'] }}" class="w-full h-full object-cover" id="photo" loading="eager">
            </div>

            {{-- Specs bar --}}
            @if($mode === 'specs')
                <div class="flex justify-around px-4 py-3 text-white">
                    <div class="text-center">
                        <div class="text-xl font-bold">€{{ number_format($current['price'] ?? 0, 0, ',', '.') }}</div>
                        <div class="text-xs text-white/50">Prijs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold">{{ $current['living_area'] ?? '?' }}</div>
                        <div class="text-xs text-white/50">m²</div>
                    </div>
                    <div class="text-center">
                        @php
                            $p = $current['plot_area'] ?? null;
                            $pd = $p ? ($p >= 10000 ? number_format($p/10000,1) . 'ha' : number_format($p,0,',','.')) : '?';
                        @endphp
                        <div class="text-xl font-bold">{{ $pd }}</div>
                        <div class="text-xs text-white/50">Perceel</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold">{{ $current['bedrooms'] ?? '?' }}</div>
                        <div class="text-xs text-white/50">Kamers</div>
                    </div>
                </div>
            @endif

            {{-- Swipe buttons --}}
            <div class="flex justify-center items-center gap-5 px-4 py-4 pb-6">
                <form method="POST" action="/kids/swipe" class="inline">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $current['property_id'] }}">
                    <input type="hidden" name="photo_index" value="{{ $current['photo_index'] }}">
                    <input type="hidden" name="image_url" value="{{ $current['image_url'] }}">
                    <input type="hidden" name="rating" value="bah">
                    <button type="submit" class="swipe-btn w-20 h-20 rounded-full bg-red-500/20 border-2 border-red-500/50 flex items-center justify-center text-4xl">👎</button>
                </form>
                <form method="POST" action="/kids/swipe" class="inline">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $current['property_id'] }}">
                    <input type="hidden" name="photo_index" value="{{ $current['photo_index'] }}">
                    <input type="hidden" name="image_url" value="{{ $current['image_url'] }}">
                    <input type="hidden" name="rating" value="niet_leuk">
                    <button type="submit" class="swipe-btn w-16 h-16 rounded-full bg-orange-500/20 border-2 border-orange-500/50 flex items-center justify-center text-3xl">😕</button>
                </form>
                <form method="POST" action="/kids/swipe" class="inline">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $current['property_id'] }}">
                    <input type="hidden" name="photo_index" value="{{ $current['photo_index'] }}">
                    <input type="hidden" name="image_url" value="{{ $current['image_url'] }}">
                    <input type="hidden" name="rating" value="gaat_wel">
                    <button type="submit" class="swipe-btn w-16 h-16 rounded-full bg-yellow-500/20 border-2 border-yellow-500/50 flex items-center justify-center text-3xl">😐</button>
                </form>
                <form method="POST" action="/kids/swipe" class="inline">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $current['property_id'] }}">
                    <input type="hidden" name="photo_index" value="{{ $current['photo_index'] }}">
                    <input type="hidden" name="image_url" value="{{ $current['image_url'] }}">
                    <input type="hidden" name="rating" value="leuk">
                    <button type="submit" class="swipe-btn w-16 h-16 rounded-full bg-green-500/20 border-2 border-green-500/50 flex items-center justify-center text-3xl">😊</button>
                </form>
                <form method="POST" action="/kids/swipe" class="inline">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $current['property_id'] }}">
                    <input type="hidden" name="photo_index" value="{{ $current['photo_index'] }}">
                    <input type="hidden" name="image_url" value="{{ $current['image_url'] }}">
                    <input type="hidden" name="rating" value="super_tof">
                    <button type="submit" class="swipe-btn w-20 h-20 rounded-full bg-pink-500/20 border-2 border-pink-500/50 flex items-center justify-center text-4xl">😍</button>
                </form>
            </div>
        </div>
    @else
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center px-8">
                <div class="text-7xl mb-4">🎉</div>
                <h2 class="text-white text-3xl font-bold mb-3">Alles gezien!</h2>
                <p class="text-white/60 mb-6">Je hebt {{ $swipedCount }} foto's beoordeeld in {{ $round - 1 }} ronde(s)</p>
                <a href="/kids/huizen" class="inline-block px-8 py-4 bg-yellow-500 text-black rounded-2xl text-xl font-bold">🏆 Bekijk je favorieten</a>
            </div>
        </div>
    @endif
</body>
</html>
