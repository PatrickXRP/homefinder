<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Mijn Huizen - HomeFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #111; min-height: 100dvh; }
    </style>
</head>
<body>
    {{-- Top bar --}}
    <div class="flex items-center justify-between px-4 py-3 bg-black/50 sticky top-0 z-10">
        <div class="flex items-center gap-2">
            <span class="text-2xl">{{ session('kid_emoji') }}</span>
            <span class="text-white font-bold">{{ session('kid_name') }}'s huizen</span>
        </div>
        <div class="flex gap-2">
            <a href="/kids/swipe" class="px-4 py-2 rounded-full bg-white/20 text-white text-sm font-medium">📸 Swipen</a>
            <a href="/kids/logout" class="text-white/40 text-sm py-2">Uit</a>
        </div>
    </div>

    <div class="p-4 max-w-2xl mx-auto">
        @if($properties->count() > 0)
            @php $liked = $properties->filter(fn($p) => $p['avg'] >= 3.5); $disliked = $properties->filter(fn($p) => $p['avg'] < 3.5); @endphp

            @if($liked->count() > 0)
                <h2 class="text-white text-xl font-bold mb-4">❤️ Favorieten ({{ $liked->count() }})</h2>
                <div class="space-y-4 mb-8">
                    @foreach($liked as $item)
                        @php $prop = $item['property']; @endphp
                        @if($prop)
                            <div class="bg-white/10 rounded-2xl overflow-hidden">
                                @if(!empty($prop->images))
                                    <img src="{{ $prop->images[0] }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-yellow-400 font-bold text-lg">{{ $item['avg'] }} / 5</span>
                                        <span class="text-white font-bold">€{{ number_format($prop->asking_price_eur ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-white/80 font-medium">{{ $prop->country?->flag_emoji }} {{ $prop->city }}{{ $prop->region ? ', ' . $prop->region : '' }}</div>
                                    <div class="flex gap-3 mt-2 text-sm text-white/50">
                                        @if($prop->living_area_m2)<span>{{ $prop->living_area_m2 }}m²</span>@endif
                                        @if($prop->bedrooms)<span>{{ $prop->bedrooms }} kamers</span>@endif
                                        @if($prop->plot_area_m2)
                                            <span>{{ $prop->plot_area_m2 >= 10000 ? number_format($prop->plot_area_m2/10000,1) . ' ha' : $prop->plot_area_m2 . 'm²' }}</span>
                                        @endif
                                    </div>
                                    <div class="text-white/30 text-xs mt-2">{{ $item['liked_photos'] }} van {{ $item['total_photos'] }} foto's leuk gevonden</div>
                                    @if($prop->url)
                                        <a href="{{ $prop->url }}" target="_blank" class="inline-block mt-2 text-sm text-blue-400 hover:underline">🔗 Bekijk</a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($disliked->count() > 0)
                <h2 class="text-white/50 text-lg font-bold mb-3">👎 Niet zo leuk ({{ $disliked->count() }})</h2>
                <div class="space-y-2 mb-8">
                    @foreach($disliked->take(10) as $item)
                        @php $prop = $item['property']; @endphp
                        @if($prop)
                            <div class="flex items-center gap-3 bg-white/5 rounded-xl p-3">
                                @if(!empty($prop->images))
                                    <img src="{{ $prop->images[0] }}" class="w-16 h-16 rounded-lg object-cover">
                                @endif
                                <div class="flex-1">
                                    <div class="text-white/60 text-sm">{{ $prop->country?->flag_emoji }} {{ $prop->city }}</div>
                                    <div class="text-white/30 text-xs">Score: {{ $item['avg'] }} / 5</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @else
            <div class="text-center py-20">
                <div class="text-6xl mb-4">📸</div>
                <p class="text-white/60 text-lg mb-6">Je hebt nog geen foto's beoordeeld</p>
                <a href="/kids/swipe" class="inline-block px-8 py-4 bg-green-500 text-white rounded-2xl text-xl font-bold">Start met swipen!</a>
            </div>
        @endif
    </div>
</body>
</html>
