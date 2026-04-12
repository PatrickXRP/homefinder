<x-filament-panels::page>
    {{-- Kind selectie --}}
    <div class="flex gap-4 justify-center mb-6">
        @foreach($children as $index => $child)
            <button
                wire:click="selectKid({{ $index }})"
                class="flex flex-col items-center p-4 rounded-2xl transition-all cursor-pointer border-2
                    {{ $selectedKid === $index ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 scale-105 shadow-lg' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md' }}"
            >
                <span class="text-4xl mb-1">{{ $child['emoji'] }}</span>
                <span class="font-bold">{{ $child['name'] }}</span>
                <span class="text-xs text-gray-500">{{ $child['age'] }}j</span>
            </button>
        @endforeach
    </div>

    @if($currentChild)
        {{-- Mode toggle --}}
        <div class="flex justify-center gap-2 mb-6">
            <button wire:click="setMode('photo')"
                class="px-5 py-2.5 rounded-xl font-medium text-sm transition-all
                    {{ $mode === 'photo' ? 'bg-primary-600 text-white shadow-lg' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200' }}">
                📸 Alleen foto's
            </button>
            <button wire:click="setMode('specs')"
                class="px-5 py-2.5 rounded-xl font-medium text-sm transition-all
                    {{ $mode === 'specs' ? 'bg-primary-600 text-white shadow-lg' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200' }}">
                📊 Foto + specs
            </button>
            <button wire:click="toggleResults"
                class="px-5 py-2.5 rounded-xl font-medium text-sm transition-all
                    {{ $showResults ? 'bg-yellow-500 text-white shadow-lg' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200' }}">
                🏆 Resultaten ({{ count($ratings) }})
            </button>
        </div>

        {{-- Progress --}}
        @php $total = $allProperties->count(); $done = count($ratings); $pct = $total > 0 ? round(($done / $total) * 100) : 0; @endphp
        <div class="max-w-2xl mx-auto mb-6">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>{{ $done }} / {{ $total }} beoordeeld</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                <div class="h-2 rounded-full bg-primary-500 transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>

        @if(!$showResults)
            {{-- SWIPE CARD --}}
            @if($currentProperty)
                <div class="max-w-lg mx-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden">
                        {{-- Photo with navigation --}}
                        @php
                            $images = $currentProperty->images ?? [];
                            $photoIdx = min($currentPhotoIndex, max(0, count($images) - 1));
                            $currentImg = $images[$photoIdx] ?? null;
                        @endphp
                        @if($currentImg)
                            <div class="relative" style="height: 400px;">
                                <img src="{{ $currentImg }}" class="w-full h-full object-cover" loading="eager">
                                @if(count($images) > 1)
                                    <div class="absolute top-0 left-0 right-0 flex justify-center gap-1 p-3">
                                        @foreach($images as $i => $img)
                                            <div class="h-1 rounded-full flex-1 max-w-[40px] {{ $i === $photoIdx ? 'bg-white' : 'bg-white/40' }}"></div>
                                        @endforeach
                                    </div>
                                    @if($photoIdx > 0)
                                        <button wire:click="prevPhoto" class="absolute left-0 top-0 bottom-0 w-1/3 cursor-pointer"></button>
                                    @endif
                                    @if($photoIdx < count($images) - 1)
                                        <button wire:click="nextPhoto" class="absolute right-0 top-0 bottom-0 w-1/3 cursor-pointer"></button>
                                    @endif
                                    <div class="absolute bottom-3 right-3 bg-black/50 text-white text-xs px-2 py-1 rounded-full">
                                        {{ $photoIdx + 1 }} / {{ count($images) }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="h-64 bg-gray-200 flex items-center justify-center">
                                <span class="text-6xl">🏠</span>
                            </div>
                        @endif

                        {{-- Specs (only in specs mode) --}}
                        @if($mode === 'specs')
                            <div class="p-5">
                                <div class="grid grid-cols-4 gap-3 text-center">
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                                        <div class="text-xl font-bold">€{{ number_format($currentProperty->asking_price_eur ?? 0, 0, ',', '.') }}</div>
                                        <div class="text-xs text-gray-500">Prijs</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                                        <div class="text-xl font-bold">{{ $currentProperty->living_area_m2 ?? '?' }}</div>
                                        <div class="text-xs text-gray-500">m²</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                                        @php
                                            $plot = $currentProperty->plot_area_m2;
                                            $plotDisplay = $plot ? ($plot >= 10000 ? number_format($plot / 10000, 1) . ' ha' : number_format($plot, 0, ',', '.')) : '?';
                                        @endphp
                                        <div class="text-xl font-bold">{{ $plotDisplay }}</div>
                                        <div class="text-xs text-gray-500">Perceel</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3">
                                        <div class="text-xl font-bold">{{ $currentProperty->bedrooms ?? '?' }}</div>
                                        <div class="text-xs text-gray-500">Kamers</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Swipe buttons --}}
                        <div class="flex justify-center gap-4 p-5 {{ $mode === 'specs' ? 'pt-0' : '' }}">
                            <button wire:click="swipe({{ $currentProperty->id }}, 'bah')"
                                class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-3xl hover:scale-125 hover:bg-red-200 transition-all shadow-lg active:scale-95">
                                👎
                            </button>
                            <button wire:click="swipe({{ $currentProperty->id }}, 'niet_leuk')"
                                class="w-14 h-14 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-2xl hover:scale-125 hover:bg-orange-200 transition-all shadow-md active:scale-95">
                                😕
                            </button>
                            <button wire:click="swipe({{ $currentProperty->id }}, 'gaat_wel')"
                                class="w-14 h-14 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-2xl hover:scale-125 hover:bg-yellow-200 transition-all shadow-md active:scale-95">
                                😐
                            </button>
                            <button wire:click="swipe({{ $currentProperty->id }}, 'leuk')"
                                class="w-14 h-14 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-2xl hover:scale-125 hover:bg-green-200 transition-all shadow-md active:scale-95">
                                😊
                            </button>
                            <button wire:click="swipe({{ $currentProperty->id }}, 'super_tof')"
                                class="w-16 h-16 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center text-3xl hover:scale-125 hover:bg-pink-200 transition-all shadow-lg active:scale-95">
                                😍
                            </button>
                        </div>
                    </div>

                    <p class="text-center text-sm text-gray-400 mt-3">
                        Tik links op foto = vorige · rechts = volgende foto
                    </p>
                </div>
            @else
                {{-- All done --}}
                <div class="max-w-lg mx-auto text-center py-12">
                    <div class="text-6xl mb-4">🎉</div>
                    <h2 class="text-2xl font-bold mb-2">Alles beoordeeld!</h2>
                    <p class="text-gray-500 mb-6">{{ $currentChild['name'] }} heeft {{ count($ratings) }} huizen bekeken</p>
                    <div class="flex gap-3 justify-center">
                        <button wire:click="toggleResults" class="px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition">
                            🏆 Bekijk resultaten
                        </button>
                        <button wire:click="resetRatings" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 rounded-xl font-medium hover:bg-gray-300 transition"
                            onclick="return confirm('Weet je het zeker? Alle beoordelingen worden gewist.')">
                            🔄 Opnieuw
                        </button>
                    </div>
                </div>
            @endif
        @else
            {{-- RESULTATEN --}}
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">{{ $currentChild['emoji'] }} {{ $currentChild['name'] }}'s favorieten</h2>
                    <span class="text-sm text-gray-500">{{ $likedProperties->count() }} ❤️ · {{ $dislikedCount }} 👎</span>
                </div>

                @if($likedProperties->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                        @foreach($likedProperties as $item)
                            @php $prop = $item['property']; @endphp
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                                @if(!empty($prop->images))
                                    <img src="{{ $prop->images[0] }}" class="w-full h-48 object-cover">
                                @endif
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-2xl">{{ $item['rating'] === 'super_tof' ? '😍' : '😊' }}</span>
                                        <span class="text-lg font-bold">€{{ number_format($prop->asking_price_eur ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-sm font-medium">{{ $prop->country?->flag_emoji }} {{ $prop->city }}{{ $prop->region ? ', ' . $prop->region : '' }}</div>
                                    <div class="flex gap-3 mt-2 text-xs text-gray-500">
                                        @if($prop->living_area_m2)<span>{{ $prop->living_area_m2 }}m²</span>@endif
                                        @if($prop->bedrooms)<span>{{ $prop->bedrooms }} kamers</span>@endif
                                        @if($prop->plot_area_m2)
                                            <span>{{ $prop->plot_area_m2 >= 10000 ? number_format($prop->plot_area_m2/10000,1) . ' ha' : $prop->plot_area_m2 . 'm²' }} perceel</span>
                                        @endif
                                    </div>
                                    @if($prop->url)
                                        <a href="{{ $prop->url }}" target="_blank" class="inline-block mt-2 text-xs text-primary-600 hover:underline">🔗 Bekijk advertentie</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-400">
                        <p class="text-4xl mb-2">🤷</p>
                        <p>Nog geen favorieten. Ga terug en swipe!</p>
                    </div>
                @endif

                {{-- Gezamenlijke favorieten --}}
                @if($favorites->count() > 0)
                    <h3 class="text-xl font-bold mb-4">🏆 Gezamenlijke top (alle kinderen)</h3>
                    <div class="space-y-3 mb-6">
                        @foreach($favorites as $fav)
                            @if($fav['property'])
                                <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl shadow">
                                    @if(!empty($fav['property']->images))
                                        <img src="{{ $fav['property']->images[0] }}" class="w-20 h-20 rounded-lg object-cover">
                                    @endif
                                    <div class="flex-1">
                                        <div class="font-bold">{{ $fav['property']->country?->flag_emoji }} {{ $fav['property']->city }}{{ $fav['property']->region ? ', ' . $fav['property']->region : '' }}</div>
                                        <div class="text-sm text-gray-500">€{{ number_format($fav['property']->asking_price_eur ?? 0, 0, ',', '.') }} · {{ $fav['property']->living_area_m2 ?? '?' }}m²</div>
                                        <div class="flex gap-1 mt-1">
                                            @foreach($fav['kids'] as $kid)
                                                <span title="{{ $kid['name'] }}">
                                                    {{ $kid['emoji'] }}{{ match($kid['rating']) { 'super_tof' => '😍', 'leuk' => '😊', 'gaat_wel' => '😐', 'niet_leuk' => '😕', 'bah' => '😤', default => '' } }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold">{{ $fav['avg'] }}</div>
                                        <div class="text-xs text-gray-500">/ 5</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="text-center mt-6">
                    <button wire:click="toggleResults" class="px-6 py-3 bg-gray-200 dark:bg-gray-700 rounded-xl font-medium hover:bg-gray-300 transition">
                        ← Terug naar swipen
                    </button>
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
