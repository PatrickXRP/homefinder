<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Woning Swiper - HomeFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100dvh; overflow: hidden; background: #111; touch-action: manipulation; }
        .swipe-btn { -webkit-tap-highlight-color: transparent; transition: transform 0.1s; }
        .swipe-btn:active { transform: scale(0.8) !important; }
        .photo-dot { width: 8px; height: 8px; border-radius: 50%; transition: all 0.2s; }
    </style>
</head>
<body class="flex flex-col" style="height: 100dvh;">

    {{-- Top bar --}}
    <div class="flex items-center justify-between px-3 py-1.5 bg-black shrink-0">
        <div class="flex items-center gap-1.5">
            <span class="text-lg">{{ $account->emoji }}</span>
            <span class="text-white font-bold text-sm">{{ $account->name }}</span>
        </div>
        <div class="flex gap-1.5">
            @if($account->module_photo_swiper)
                <a href="/kids/swipe?mode=photo" class="px-2.5 py-1 rounded-full text-xs font-medium bg-white/20 text-white">📸</a>
            @endif
            <a href="/kids/swipe?mode=property" class="px-2.5 py-1 rounded-full text-xs font-medium bg-white text-black">🏠</a>
            <a href="/kids/huizen" class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/30 text-yellow-300">🏆</a>
        </div>
        <a href="/kids/logout" class="text-white/30 text-xs">✕</a>
    </div>

    {{-- Progress --}}
    <div class="px-3 py-1 shrink-0">
        <div class="w-full bg-white/10 rounded-full h-1">
            <div class="h-1 rounded-full bg-green-500" style="width: {{ $totalProps > 0 ? min(($swipedCount / $totalProps) * 100, 100) : 0 }}%"></div>
        </div>
        <div class="text-white/30 text-[10px] text-center">{{ $swipedCount }}/{{ $totalProps }} woningen</div>
    </div>

    @if($current)
        {{-- Photo carousel --}}
        <div class="flex-1 min-h-0 px-2 pb-1 flex flex-col">
            <div class="flex-1 relative rounded-2xl overflow-hidden bg-gray-900 min-h-0">
                @foreach($currentPhotos as $idx => $photo)
                    <img src="{{ $photo }}" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300 {{ $idx === 0 ? 'opacity-100' : 'opacity-0' }}"
                         data-photo-idx="{{ $idx }}" draggable="false">
                @endforeach

                {{-- Photo dots --}}
                @if(count($currentPhotos) > 1)
                    <div class="absolute top-2 left-0 right-0 flex justify-center gap-1 z-10">
                        @foreach(array_slice($currentPhotos, 0, 20) as $idx => $photo)
                            <div class="photo-dot {{ $idx === 0 ? 'bg-white' : 'bg-white/40' }}" data-dot="{{ $idx }}"></div>
                        @endforeach
                    </div>

                    {{-- Tap zones for photo nav --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1/3 z-10 cursor-pointer" onclick="prevPhoto()"></div>
                    <div class="absolute right-0 top-0 bottom-0 w-1/3 z-10 cursor-pointer" onclick="nextPhoto()"></div>

                    <div class="absolute bottom-2 right-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded-full z-10" id="photo-counter">
                        1/{{ count($currentPhotos) }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Specs --}}
        <div class="flex justify-around px-3 py-2 text-white shrink-0">
            <div class="text-center">
                <div class="text-lg font-bold">€{{ number_format($current->asking_price_eur ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-white/40">Prijs</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold">{{ $current->living_area_m2 ?? '?' }}m²</div>
                <div class="text-[10px] text-white/40">Wonen</div>
            </div>
            <div class="text-center">
                @php $p = $current->plot_area_m2; $pd = $p ? ($p >= 10000 ? number_format($p/10000,1).'ha' : number_format($p,0,',','.')) : '?'; @endphp
                <div class="text-lg font-bold">{{ $pd }}</div>
                <div class="text-[10px] text-white/40">Perceel</div>
            </div>
            <div class="text-center">
                <div class="text-lg font-bold">{{ $current->bedrooms ?? '?' }}</div>
                <div class="text-[10px] text-white/40">Kamers</div>
            </div>
        </div>

        {{-- Rating buttons --}}
        <div class="flex justify-center items-center gap-3 px-3 py-3 pb-4 shrink-0 bg-black/50">
            @foreach([
                ['rating' => 'bah', 'emoji' => '👎', 'size' => 'w-[72px] h-[72px] text-4xl', 'bg' => 'bg-red-500/30 border-red-500/60'],
                ['rating' => 'niet_leuk', 'emoji' => '😕', 'size' => 'w-[60px] h-[60px] text-3xl', 'bg' => 'bg-orange-500/30 border-orange-500/60'],
                ['rating' => 'gaat_wel', 'emoji' => '😐', 'size' => 'w-[60px] h-[60px] text-3xl', 'bg' => 'bg-yellow-500/30 border-yellow-500/60'],
                ['rating' => 'leuk', 'emoji' => '😊', 'size' => 'w-[60px] h-[60px] text-3xl', 'bg' => 'bg-green-500/30 border-green-500/60'],
                ['rating' => 'super_tof', 'emoji' => '😍', 'size' => 'w-[72px] h-[72px] text-4xl', 'bg' => 'bg-pink-500/30 border-pink-500/60'],
            ] as $btn)
                <form method="POST" action="/kids/swipe" class="inline">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $current->id }}">
                    <input type="hidden" name="image_url" value="{{ $currentPhotos[0] ?? '' }}">
                    <input type="hidden" name="rating" value="{{ $btn['rating'] }}">
                    <button type="submit" class="swipe-btn {{ $btn['size'] }} rounded-full {{ $btn['bg'] }} border-2 flex items-center justify-center">{{ $btn['emoji'] }}</button>
                </form>
            @endforeach
        </div>

        <script>
            let currentPhoto = 0;
            const totalPhotos = {{ count($currentPhotos) }};
            const imgs = document.querySelectorAll('[data-photo-idx]');
            const dots = document.querySelectorAll('[data-dot]');
            const counter = document.getElementById('photo-counter');

            function showPhoto(idx) {
                if (idx < 0 || idx >= totalPhotos) return;
                currentPhoto = idx;
                imgs.forEach((img, i) => { img.style.opacity = i === idx ? '1' : '0'; });
                dots.forEach((dot, i) => { dot.className = 'photo-dot ' + (i === idx ? 'bg-white' : 'bg-white/40'); });
                if (counter) counter.textContent = (idx + 1) + '/' + totalPhotos;
            }

            function nextPhoto() { showPhoto(currentPhoto + 1); }
            function prevPhoto() { showPhoto(currentPhoto - 1); }

            // Keyboard
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') nextPhoto();
                if (e.key === 'ArrowLeft') prevPhoto();
            });
        </script>
    @else
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center px-8">
                <div class="text-7xl mb-4">🎉</div>
                <h2 class="text-white text-3xl font-bold mb-3">Alle woningen gezien!</h2>
                <p class="text-white/60 mb-6">{{ $swipedCount }} woningen beoordeeld</p>
                <a href="/kids/huizen" class="inline-block px-8 py-4 bg-yellow-500 text-black rounded-2xl text-xl font-bold">🏆 Favorieten</a>
            </div>
        </div>
    @endif
</body>
</html>
