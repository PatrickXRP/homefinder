<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>{{ $property->city ?? 'Woning' }} - HomeFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #111; min-height: 100dvh; }
        .photo-grid img { transition: transform 0.2s; cursor: pointer; }
        .photo-grid img:hover { transform: scale(1.02); }
        #lightbox { display: none; }
        #lightbox.active { display: flex; }
    </style>
</head>
<body>
    {{-- Top bar --}}
    <div class="flex items-center justify-between px-4 py-3 bg-black/80 sticky top-0 z-20 backdrop-blur">
        <a href="/kids/huizen" class="text-white/60 text-sm flex items-center gap-1">← Terug</a>
        <div class="flex items-center gap-2">
            <span>{{ $account->emoji }}</span>
            @if($avg)
                <span class="text-yellow-400 font-bold">{{ $avg }}/5</span>
            @endif
        </div>
        <div class="flex gap-2">
            @if($property->status !== 'archief')
                <form method="POST" action="/kids/woning/{{ $property->id }}/archive" class="inline">
                    @csrf
                    <button type="submit" class="text-white/40 text-sm hover:text-red-400">📦</button>
                </form>
            @else
                <form method="POST" action="/kids/woning/{{ $property->id }}/unarchive" class="inline">
                    @csrf
                    <button type="submit" class="text-green-400 text-sm">📦 Herstel</button>
                </form>
            @endif
            @if($property->url)
                <a href="{{ $property->url }}" target="_blank" class="text-blue-400 text-sm">🔗</a>
            @endif
        </div>
    </div>

    <div class="max-w-5xl mx-auto">
        {{-- Hero photo --}}
        @php $images = is_array($property->images) ? $property->images : []; @endphp
        @if(count($images) > 0)
            <div class="relative cursor-pointer" onclick="openLightbox(0)">
                <img src="{{ $images[0] }}" class="w-full max-h-[50vh] object-cover">
                <div class="absolute bottom-3 right-3 bg-black/60 text-white text-sm px-3 py-1 rounded-full">
                    📷 {{ count($images) }} foto's
                </div>
            </div>
        @endif

        {{-- Key stats --}}
        <div class="grid grid-cols-4 gap-0 bg-white/5">
            <div class="text-center py-4 border-r border-white/10">
                <div class="text-2xl font-bold text-white">€{{ number_format($property->asking_price_eur ?? 0, 0, ',', '.') }}</div>
                <div class="text-white/40 text-xs">Prijs</div>
            </div>
            <div class="text-center py-4 border-r border-white/10">
                <div class="text-2xl font-bold text-white">{{ $property->living_area_m2 ?? '?' }}<span class="text-sm">m²</span></div>
                <div class="text-white/40 text-xs">Wonen</div>
            </div>
            <div class="text-center py-4 border-r border-white/10">
                @php $p = $property->plot_area_m2; $pd = $p ? ($p >= 10000 ? number_format($p/10000,1).' ha' : number_format($p,0,',','.').' m²') : '?'; @endphp
                <div class="text-2xl font-bold text-white">{{ $pd }}</div>
                <div class="text-white/40 text-xs">Perceel</div>
            </div>
            <div class="text-center py-4">
                <div class="text-2xl font-bold text-white">{{ $property->bedrooms ?? '?' }}</div>
                <div class="text-white/40 text-xs">Kamers</div>
            </div>
        </div>

        {{-- Location --}}
        <div class="px-4 py-4 bg-white/5 border-t border-white/10">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-2xl">{{ $property->country?->flag_emoji }}</span>
                <div>
                    <div class="text-white font-bold">{{ $property->city }}{{ $property->region ? ', ' . $property->region : '' }}</div>
                    <div class="text-white/50 text-sm">{{ $property->country?->name }}</div>
                </div>
            </div>
            @if($property->address)
                <div class="text-white/40 text-sm">📍 {{ $property->address }}</div>
            @endif
        </div>

        {{-- Details grid --}}
        <div class="grid grid-cols-2 gap-px bg-white/10">
            @foreach([
                ['label' => 'Bouwjaar', 'value' => $property->year_built, 'icon' => '📅'],
                ['label' => 'Badkamers', 'value' => $property->bathrooms, 'icon' => '🚿'],
                ['label' => 'Energieklasse', 'value' => $property->energy_class, 'icon' => '⚡'],
                ['label' => 'Staat', 'value' => match($property->condition) { 'turnkey' => 'Instapklaar', 'goed' => 'Goed', 'matig' => 'Matig', 'opknapper' => 'Opknapper', default => $property->condition }, 'icon' => '🔧'],
                ['label' => 'Water', 'value' => $property->water_type ? (match($property->water_type) { 'meer' => '🏞️ Meer', 'zee' => '🌊 Zee', 'rivier' => '🏞️ Rivier', default => null } . ($property->water_name ? ' ('.$property->water_name.')' : '')) : null, 'icon' => '💧'],
                ['label' => 'Prijs/m²', 'value' => $property->price_per_m2 ? '€'.number_format($property->price_per_m2, 0, ',', '.') : null, 'icon' => '📐'],
            ] as $detail)
                @if($detail['value'])
                    <div class="bg-black/50 px-4 py-3">
                        <div class="text-white/40 text-xs">{{ $detail['icon'] }} {{ $detail['label'] }}</div>
                        <div class="text-white font-medium">{{ $detail['value'] }}</div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Features --}}
        @php
            $features = array_filter([
                $property->has_sauna ? '🧖 Sauna' : null,
                $property->has_jetty ? '⚓ Steiger' : null,
                $property->has_guest_house ? '🏡 Gastenverblijf' : null,
                $property->year_round_accessible ? '❄️ Winterbereikbaar' : null,
                $property->own_road ? '🛤️ Eigen weg' : null,
            ]);
        @endphp
        @if(count($features) > 0)
            <div class="flex gap-2 flex-wrap px-4 py-3 bg-white/5">
                @foreach($features as $feature)
                    <span class="px-3 py-1.5 bg-white/10 rounded-full text-white text-sm">{{ $feature }}</span>
                @endforeach
            </div>
        @endif

        {{-- Notes --}}
        @if($property->notes)
            <div class="px-4 py-3 bg-white/5 border-t border-white/10">
                <div class="text-white/40 text-xs mb-1">Notities</div>
                <div class="text-white/80 text-sm">{{ $property->notes }}</div>
            </div>
        @endif

        {{-- Google Maps --}}
        @if($property->address || $property->city)
            @php $q = urlencode(($property->address ?? $property->city) . ', ' . ($property->country?->name ?? '')); @endphp
            <div class="px-4 py-3">
                <a href="https://www.google.com/maps/search/?api=1&query={{ $q }}" target="_blank"
                   class="block w-full py-3 bg-blue-600 text-white text-center rounded-xl font-medium">
                    📍 Open in Google Maps
                </a>
            </div>
        @endif

        {{-- Photo grid --}}
        @if(count($images) > 1)
            <div class="px-4 py-4">
                <h3 class="text-white font-bold mb-3">📷 Alle foto's ({{ count($images) }})</h3>
                <div class="photo-grid grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($images as $idx => $img)
                        <div class="aspect-[4/3] rounded-xl overflow-hidden" onclick="openLightbox({{ $idx }})">
                            <img src="{{ $img }}" class="w-full h-full object-cover" loading="lazy">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Link --}}
        @if($property->url)
            <div class="px-4 py-4 pb-8">
                <a href="{{ $property->url }}" target="_blank"
                   class="block w-full py-3 bg-white/10 text-white text-center rounded-xl font-medium">
                    🔗 Bekijk originele advertentie
                </a>
            </div>
        @endif
    </div>

    {{-- Lightbox --}}
    <div id="lightbox" class="fixed inset-0 bg-black/95 z-50 hidden flex-col items-center justify-center">
        {{-- Close button --}}
        <button onclick="closeLightbox()" class="absolute top-4 right-4 z-60 w-12 h-12 flex items-center justify-center bg-white/20 rounded-full text-white text-2xl">✕</button>

        {{-- Counter --}}
        <div class="absolute top-4 left-4 text-white/60 text-sm z-60" id="lb-counter"></div>

        {{-- Image --}}
        <img id="lb-img" src="" class="max-w-[90vw] max-h-[80vh] object-contain">

        {{-- Nav buttons --}}
        <button onclick="lbPrev()" id="lb-prev" class="absolute left-2 top-1/2 -translate-y-1/2 w-14 h-14 flex items-center justify-center bg-white/10 rounded-full text-white text-3xl hover:bg-white/20">‹</button>
        <button onclick="lbNext()" id="lb-next" class="absolute right-2 top-1/2 -translate-y-1/2 w-14 h-14 flex items-center justify-center bg-white/10 rounded-full text-white text-3xl hover:bg-white/20">›</button>
    </div>

    <script>
        const images = @json($images);
        let lbIdx = 0;
        const lb = document.getElementById('lightbox');

        function openLightbox(idx) {
            lbIdx = idx;
            document.getElementById('lb-img').src = images[idx];
            document.getElementById('lb-counter').textContent = (idx + 1) + ' / ' + images.length;
            document.getElementById('lb-prev').style.visibility = idx === 0 ? 'hidden' : 'visible';
            document.getElementById('lb-next').style.visibility = idx === images.length - 1 ? 'hidden' : 'visible';
            lb.classList.remove('hidden');
            lb.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lb.classList.add('hidden');
            lb.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function lbNext() {
            if (lbIdx < images.length - 1) openLightbox(lbIdx + 1);
        }

        function lbPrev() {
            if (lbIdx > 0) openLightbox(lbIdx - 1);
        }

        // Close on background click (not on image or buttons)
        lb.addEventListener('click', (e) => {
            if (e.target === lb || e.target === document.getElementById('lb-img')) {
                if (e.target === lb) closeLightbox();
            }
        });

        // Touch swipe in lightbox
        let lbStartX = 0;
        lb.addEventListener('touchstart', (e) => { lbStartX = e.touches[0].clientX; }, { passive: true });
        lb.addEventListener('touchend', (e) => {
            const diff = e.changedTouches[0].clientX - lbStartX;
            if (diff > 50) lbPrev();
            else if (diff < -50) lbNext();
        });

        document.addEventListener('keydown', (e) => {
            if (lb.classList.contains('hidden')) return;
            if (e.key === 'ArrowRight') lbNext();
            else if (e.key === 'ArrowLeft') lbPrev();
            else if (e.key === 'Escape') closeLightbox();
        });
    </script>
</body>
</html>
