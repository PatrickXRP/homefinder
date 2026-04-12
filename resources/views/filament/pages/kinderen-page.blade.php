<x-filament-panels::page>
    {{-- Kind selectie --}}
    <div class="flex gap-4 justify-center mb-8">
        @foreach($children as $index => $child)
            <button
                wire:click="selectKid({{ $index }})"
                class="flex flex-col items-center p-6 rounded-2xl transition-all cursor-pointer border-2
                    {{ $selectedKid === $index ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 scale-105 shadow-lg' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md' }}"
            >
                <span class="text-5xl mb-2">{{ $child['emoji'] }}</span>
                <span class="font-bold text-lg">{{ $child['name'] }}</span>
                <span class="text-sm text-gray-500">{{ $child['age'] }} jaar</span>
            </button>
        @endforeach
    </div>

    @if($currentChild)
        {{-- Woningen beoordelen --}}
        @if($properties->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">🏠 Wat vind je van deze huizen?</h2>
            <div class="space-y-4">
                @foreach($properties as $property)
                    <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                        <div class="flex-1">
                            <div class="font-bold">{{ $property->name }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $property->country?->flag_emoji }} {{ $property->city ?? $property->country?->name }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            @foreach($ratingOptions as $value => $emoji)
                                <button
                                    wire:click="rate({{ $property->id }}, '{{ $value }}')"
                                    class="text-3xl p-2 rounded-xl transition-all hover:scale-125
                                        {{ ($ratings[$property->id] ?? '') === $value ? 'bg-primary-100 dark:bg-primary-900 scale-110 ring-2 ring-primary-500' : 'hover:bg-gray-100 dark:hover:bg-gray-600' }}"
                                    title="{{ $value }}"
                                >
                                    {{ $emoji }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6 text-center text-gray-500">
            <p class="text-4xl mb-2">🏠</p>
            <p>Er zijn nog geen huizen om te beoordelen.</p>
            <p class="text-sm">Woningen met status "Bezichtigen" of hoger verschijnen hier.</p>
        </div>
        @endif

        {{-- Wensenlijst --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">⭐ Wensenlijst van {{ $currentChild['name'] }}</h2>
            <div class="flex gap-2 mb-4">
                <input
                    type="text"
                    wire:model="newWish"
                    wire:keydown.enter="addWish"
                    placeholder="Wat wil jij graag bij het huis? 🏡"
                    class="flex-1 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-3 text-lg focus:ring-primary-500 focus:border-primary-500"
                >
                <button
                    wire:click="addWish"
                    class="px-6 py-3 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition"
                >
                    Toevoegen
                </button>
            </div>
            @if($wishes->count() > 0)
                <div class="space-y-2">
                    @foreach($wishes as $wish)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span>{{ $currentChild['emoji'] }} {{ $wish->wish }}</span>
                            <button wire:click="deleteWish({{ $wish->id }})" class="text-red-400 hover:text-red-600 text-xl">&times;</button>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-center py-4">Nog geen wensen. Typ hierboven wat je graag wilt!</p>
            @endif
        </div>
    @endif

    {{-- Gezamenlijke favorieten --}}
    @if($favorites->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">🏆 Favorieten van alle kinderen</h2>
        <div class="space-y-3">
            @foreach($favorites as $fav)
                @if($fav['property'])
                <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <span class="text-2xl">{{ $loop->iteration <= 3 ? ['🥇','🥈','🥉'][$loop->index] : '🏠' }}</span>
                    <div class="flex-1">
                        <div class="font-bold">{{ $fav['property']->name }}</div>
                        <div class="text-sm text-gray-500">{{ $fav['property']->country?->flag_emoji }} {{ $fav['property']->city ?? '' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-lg">{{ $fav['avg'] }} / 5</div>
                        <div class="text-xs text-gray-500">{{ $fav['count'] }} stem{{ $fav['count'] > 1 ? 'men' : '' }}</div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif
</x-filament-panels::page>
