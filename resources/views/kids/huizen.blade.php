<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Huizen - HomeFinder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #111; min-height: 100dvh; }
        .tile { transition: transform 0.15s; }
        .tile:active { transform: scale(0.97); }
    </style>
</head>
<body>
    {{-- Top bar --}}
    <div class="flex items-center justify-between px-4 py-3 bg-black/80 sticky top-0 z-10 backdrop-blur">
        <div class="flex items-center gap-2">
            <span class="text-2xl">{{ $account->emoji }}</span>
            <span class="text-white font-bold">Huizen</span>
        </div>
        <div class="flex gap-2">
            @if($account->module_photo_swiper || $account->module_property_swiper)
                <a href="/kids/swipe" class="px-4 py-2 rounded-full bg-white/20 text-white text-sm font-medium">📸 Swipen</a>
            @endif
            <a href="/kids/logout" class="text-white/40 text-sm py-2">Uit</a>
        </div>
    </div>

    <div class="p-3 max-w-6xl mx-auto">
        {{-- Tab bar --}}
        @php $tab = request('tab', 'favorieten'); @endphp
        <div class="flex gap-2 mb-4 overflow-x-auto">
            <a href="?tab=favorieten" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $tab === 'favorieten' ? 'bg-yellow-500 text-black' : 'bg-white/10 text-white' }}">
                ❤️ Favorieten ({{ $properties->filter(fn($p) => $p['avg'] >= 3.5)->count() }})
            </a>
            @if($account->module_property_overview && $allProperties->count() > 0)
                <a href="?tab=alle" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $tab === 'alle' ? 'bg-blue-500 text-white' : 'bg-white/10 text-white' }}">
                    🏠 Alle woningen ({{ $allProperties->count() }})
                </a>
            @endif
            @if($properties->filter(fn($p) => $p['avg'] < 3.5)->count() > 0)
                <a href="?tab=nee" class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap {{ $tab === 'nee' ? 'bg-red-500/50 text-white' : 'bg-white/10 text-white/50' }}">
                    👎 Niet leuk
                </a>
            @endif
        </div>

        @if($tab === 'favorieten')
            @php $liked = $properties->filter(fn($p) => $p['avg'] >= 3.5); @endphp
            @if($liked->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($liked as $item)
                        @php $prop = $item['property']; @endphp
                        @if($prop)
                            <a href="/kids/woning/{{ $prop->id }}" class="tile bg-white/5 rounded-2xl overflow-hidden block">
                                @if(!empty($prop->images))
                                    <div class="aspect-[4/3] overflow-hidden">
                                        <img src="{{ $prop->images[0] }}" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-yellow-400 font-bold">{{ $item['avg'] }}/5</span>
                                        <span class="text-white font-bold text-sm">€{{ number_format($prop->asking_price_eur ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-white/70 text-xs">{{ $prop->country?->flag_emoji }} {{ $prop->city }}</div>
                                    <div class="text-white/40 text-[10px] mt-0.5">
                                        {{ $prop->living_area_m2 ?? '?' }}m²
                                        @if($prop->bedrooms) · {{ $prop->bedrooms }}k @endif
                                        @if($prop->plot_area_m2) · {{ $prop->plot_area_m2 >= 10000 ? number_format($prop->plot_area_m2/10000,1).'ha' : $prop->plot_area_m2.'m²' }} @endif
                                    </div>
                                    <div class="text-white/20 text-[10px]">{{ is_array($prop->images) ? count($prop->images) : 0 }} foto's</div>
                                </div>
                            </a>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <div class="text-5xl mb-3">📸</div>
                    <p class="text-white/50">Nog geen favorieten. Ga swipen!</p>
                </div>
            @endif

        @elseif($tab === 'alle' && $account->module_property_overview)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($allProperties->sortBy('asking_price_eur') as $prop)
                    <a href="/kids/woning/{{ $prop->id }}" class="tile bg-white/5 rounded-2xl overflow-hidden block">
                        @if(!empty($prop->images))
                            <div class="aspect-[4/3] overflow-hidden">
                                <img src="{{ $prop->images[0] }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                        @else
                            <div class="aspect-[4/3] bg-white/5 flex items-center justify-center"><span class="text-3xl">🏠</span></div>
                        @endif
                        <div class="p-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-white/40 text-xs">{{ is_array($prop->images) ? count($prop->images) : 0 }}📷</span>
                                <span class="text-white font-bold text-sm">€{{ number_format($prop->asking_price_eur ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="text-white/70 text-xs">{{ $prop->country?->flag_emoji }} {{ $prop->city }}{{ $prop->region ? ', '.$prop->region : '' }}</div>
                            <div class="text-white/40 text-[10px] mt-0.5">
                                {{ $prop->living_area_m2 ?? '?' }}m²
                                @if($prop->bedrooms) · {{ $prop->bedrooms }}k @endif
                                @if($prop->plot_area_m2) · {{ $prop->plot_area_m2 >= 10000 ? number_format($prop->plot_area_m2/10000,1).'ha' : $prop->plot_area_m2.'m²' }} @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        @elseif($tab === 'nee')
            @php $disliked = $properties->filter(fn($p) => $p['avg'] < 3.5); @endphp
            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                @foreach($disliked as $item)
                    @php $prop = $item['property']; @endphp
                    @if($prop && !empty($prop->images))
                        <a href="/kids/woning/{{ $prop->id }}" class="tile rounded-xl overflow-hidden block opacity-50 hover:opacity-80">
                            <div class="aspect-square overflow-hidden">
                                <img src="{{ $prop->images[0] }}" class="w-full h-full object-cover" loading="lazy">
                            </div>
                            <div class="bg-white/5 px-2 py-1 text-center">
                                <span class="text-white/30 text-[10px]">{{ $item['avg'] }}/5</span>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>
