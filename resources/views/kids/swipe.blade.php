<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Swipe - HomeFinder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100dvh; overflow: hidden; background: #111; touch-action: manipulation; }
        .swipe-btn { -webkit-tap-highlight-color: transparent; transition: transform 0.1s; }
        .swipe-btn:active { transform: scale(0.8) !important; }
    </style>
</head>
<body class="flex flex-col" style="height: 100dvh;">

    {{-- Top bar — fixed small --}}
    <div class="flex items-center justify-between px-3 py-1.5 bg-black shrink-0">
        <div class="flex items-center gap-1.5">
            <span class="text-lg">{{ session('kid_emoji') }}</span>
            <span class="text-white font-bold text-sm">{{ session('kid_name') }}</span>
        </div>
        <div class="flex gap-1.5">
            @if($account->module_photo_swiper)
                <a href="/kids/swipe?mode=photo" class="px-2.5 py-1 rounded-full text-xs font-medium {{ $mode === 'photo' ? 'bg-white text-black' : 'bg-white/20 text-white' }}">📸</a>
            @endif
            @if($account->module_property_swiper)
                <a href="/kids/swipe?mode=property" class="px-2.5 py-1 rounded-full text-xs font-medium {{ $mode === 'property' ? 'bg-white text-black' : 'bg-white/20 text-white' }}">🏠</a>
            @endif
            @if($account->module_property_overview || $account->module_property_swiper)
                <a href="/kids/huizen" class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/30 text-yellow-300">🏆</a>
            @endif
        </div>
        <a href="/kids/logout" class="text-white/30 text-xs">✕</a>
    </div>

    {{-- Progress — tiny --}}
    <div class="px-3 py-1 shrink-0">
        <div class="w-full bg-white/10 rounded-full h-1">
            <div class="h-1 rounded-full bg-green-500" style="width: {{ $totalPhotos > 0 ? min(($swipedCount / $totalPhotos) * 100, 100) : 0 }}%"></div>
        </div>
        <div class="text-white/30 text-[10px] text-center">{{ $swipedCount }}/{{ $totalPhotos }}</div>
    </div>

    @if($current)
        {{-- Photo — takes available space but leaves room for buttons --}}
        <div class="flex-1 min-h-0 px-2 pb-1" id="photo-container">
            <div class="relative w-full h-full rounded-2xl overflow-hidden bg-gray-900" id="photo-card">
                <img src="{{ $current['image_url'] }}" class="w-full h-full object-cover" id="photo" draggable="false">

                {{-- Swipe overlay hint --}}
                <div id="swipe-overlay" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-0 transition-opacity" style="font-size: 5rem;"></div>
            </div>
        </div>

        {{-- Specs bar (compact) --}}
        @if($mode === 'specs')
            <div class="flex justify-around px-3 py-1.5 text-white shrink-0">
                <div class="text-center">
                    <div class="text-base font-bold">€{{ number_format($current['price'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-white/40">Prijs</div>
                </div>
                <div class="text-center">
                    <div class="text-base font-bold">{{ $current['living_area'] ?? '?' }}m²</div>
                    <div class="text-[10px] text-white/40">Wonen</div>
                </div>
                <div class="text-center">
                    @php $p = $current['plot_area'] ?? null; $pd = $p ? ($p >= 10000 ? number_format($p/10000,1).'ha' : number_format($p,0,',','.')) : '?'; @endphp
                    <div class="text-base font-bold">{{ $pd }}</div>
                    <div class="text-[10px] text-white/40">Perceel</div>
                </div>
                <div class="text-center">
                    <div class="text-base font-bold">{{ $current['bedrooms'] ?? '?' }}</div>
                    <div class="text-[10px] text-white/40">Kamers</div>
                </div>
            </div>
        @endif

        {{-- Rating buttons — always visible at bottom --}}
        <div class="flex justify-center items-center gap-3 px-3 py-3 pb-4 shrink-0 bg-black/50" id="buttons">
            @foreach([
                ['rating' => 'bah', 'emoji' => '👎', 'size' => 'w-[72px] h-[72px] text-4xl', 'bg' => 'bg-red-500/30 border-red-500/60'],
                ['rating' => 'niet_leuk', 'emoji' => '😕', 'size' => 'w-[60px] h-[60px] text-3xl', 'bg' => 'bg-orange-500/30 border-orange-500/60'],
                ['rating' => 'gaat_wel', 'emoji' => '😐', 'size' => 'w-[60px] h-[60px] text-3xl', 'bg' => 'bg-yellow-500/30 border-yellow-500/60'],
                ['rating' => 'leuk', 'emoji' => '😊', 'size' => 'w-[60px] h-[60px] text-3xl', 'bg' => 'bg-green-500/30 border-green-500/60'],
                ['rating' => 'super_tof', 'emoji' => '😍', 'size' => 'w-[72px] h-[72px] text-4xl', 'bg' => 'bg-pink-500/30 border-pink-500/60'],
            ] as $btn)
                <form method="POST" action="/kids/swipe" class="inline" id="form-{{ $btn['rating'] }}">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $current['property_id'] }}">
                    <input type="hidden" name="photo_index" value="{{ $current['photo_index'] }}">
                    <input type="hidden" name="image_url" value="{{ $current['image_url'] }}">
                    <input type="hidden" name="rating" value="{{ $btn['rating'] }}">
                    <button type="submit" class="swipe-btn {{ $btn['size'] }} rounded-full {{ $btn['bg'] }} border-2 flex items-center justify-center">{{ $btn['emoji'] }}</button>
                </form>
            @endforeach
        </div>

        <script>
            // Touch swipe on photo
            const card = document.getElementById('photo-card');
            const overlay = document.getElementById('swipe-overlay');
            let startX = 0, currentX = 0, isDragging = false;

            card.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                isDragging = true;
            }, { passive: true });

            card.addEventListener('touchmove', (e) => {
                if (!isDragging) return;
                currentX = e.touches[0].clientX;
                const diff = currentX - startX;
                const absDiff = Math.abs(diff);

                // Visual feedback
                if (absDiff > 30) {
                    card.style.transform = `translateX(${diff * 0.3}px) rotate(${diff * 0.02}deg)`;
                    card.style.transition = 'none';

                    if (diff > 80) {
                        overlay.textContent = '😍';
                        overlay.style.opacity = '1';
                        overlay.style.background = 'rgba(236,72,153,0.3)';
                    } else if (diff > 40) {
                        overlay.textContent = '😊';
                        overlay.style.opacity = '0.8';
                        overlay.style.background = 'rgba(34,197,94,0.3)';
                    } else if (diff < -80) {
                        overlay.textContent = '👎';
                        overlay.style.opacity = '1';
                        overlay.style.background = 'rgba(239,68,68,0.3)';
                    } else if (diff < -40) {
                        overlay.textContent = '😕';
                        overlay.style.opacity = '0.8';
                        overlay.style.background = 'rgba(249,115,22,0.3)';
                    } else {
                        overlay.style.opacity = '0';
                    }
                }
            }, { passive: true });

            card.addEventListener('touchend', (e) => {
                if (!isDragging) return;
                isDragging = false;
                const diff = currentX - startX;

                card.style.transition = 'transform 0.3s ease';
                card.style.transform = '';
                overlay.style.opacity = '0';

                if (diff > 80) {
                    document.getElementById('form-super_tof').submit();
                } else if (diff > 40) {
                    document.getElementById('form-leuk').submit();
                } else if (diff < -80) {
                    document.getElementById('form-bah').submit();
                } else if (diff < -40) {
                    document.getElementById('form-niet_leuk').submit();
                }

                startX = 0;
                currentX = 0;
            });

            // Mouse swipe (laptop touchscreen)
            card.addEventListener('mousedown', (e) => {
                startX = e.clientX;
                isDragging = true;
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                currentX = e.clientX;
                const diff = currentX - startX;
                const absDiff = Math.abs(diff);

                if (absDiff > 30) {
                    card.style.transform = `translateX(${diff * 0.3}px) rotate(${diff * 0.02}deg)`;
                    card.style.transition = 'none';

                    if (diff > 80) { overlay.textContent = '😍'; overlay.style.opacity = '1'; overlay.style.background = 'rgba(236,72,153,0.3)'; }
                    else if (diff > 40) { overlay.textContent = '😊'; overlay.style.opacity = '0.8'; overlay.style.background = 'rgba(34,197,94,0.3)'; }
                    else if (diff < -80) { overlay.textContent = '👎'; overlay.style.opacity = '1'; overlay.style.background = 'rgba(239,68,68,0.3)'; }
                    else if (diff < -40) { overlay.textContent = '😕'; overlay.style.opacity = '0.8'; overlay.style.background = 'rgba(249,115,22,0.3)'; }
                    else { overlay.style.opacity = '0'; }
                }
            });

            document.addEventListener('mouseup', (e) => {
                if (!isDragging) return;
                isDragging = false;
                const diff = currentX - startX;

                card.style.transition = 'transform 0.3s ease';
                card.style.transform = '';
                overlay.style.opacity = '0';

                if (diff > 80) document.getElementById('form-super_tof').submit();
                else if (diff > 40) document.getElementById('form-leuk').submit();
                else if (diff < -80) document.getElementById('form-bah').submit();
                else if (diff < -40) document.getElementById('form-niet_leuk').submit();

                startX = 0; currentX = 0;
            });
        </script>
    @else
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center px-8">
                <div class="text-7xl mb-4">🎉</div>
                <h2 class="text-white text-3xl font-bold mb-3">Alles gezien!</h2>
                <p class="text-white/60 mb-6">{{ $swipedCount }} foto's beoordeeld</p>
                <a href="/kids/huizen" class="inline-block px-8 py-4 bg-yellow-500 text-black rounded-2xl text-xl font-bold">🏆 Favorieten</a>
            </div>
        </div>
    @endif
</body>
</html>
