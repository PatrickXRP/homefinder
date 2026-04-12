<x-filament-panels::page>
    {{-- Gezinsprofiel --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-3">👨‍👩‍👧‍👦 Gezin {{ $family['name'] }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @foreach($family['adults'] as $adult)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <div class="text-2xl mb-1">👤</div>
                    <div class="font-medium">{{ $adult }}</div>
                </div>
            @endforeach
            @foreach($family['children'] as $child)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                    <div class="text-2xl mb-1">{{ $child['emoji'] }}</div>
                    <div class="font-medium">{{ $child['name'] }} ({{ $child['age'] }}j)</div>
                </div>
            @endforeach
        </div>
        <div class="mt-3 text-sm text-gray-500">📍 Woonbasis: {{ $family['base_location'] }}</div>
    </div>

    {{-- Profiel volledigheid --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-2">
            <h3 class="font-bold">Profiel volledigheid</h3>
            <span class="text-sm font-medium">{{ $completeness }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
            <div class="h-3 rounded-full transition-all" style="width: {{ $completeness }}%; background-color: {{ $completeness >= 80 ? '#22c55e' : ($completeness >= 50 ? '#f59e0b' : '#ef4444') }}"></div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Must-haves met ingevulde waarde</p>
    </div>

    {{-- Budget samenvatting --}}
    @if($budget)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <h3 class="font-bold mb-3">💰 Actief Budget: {{ $budget->name }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">€{{ number_format($budget->total_budget_eur, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500">Totaal</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold">€{{ number_format($budget->purchase_budget_eur, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500">Aankoop</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold">€{{ number_format($budget->renovation_budget_eur, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500">Verbouwing</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold">€{{ number_format($budget->annual_costs_max_eur ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500">Max jaarlast</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Wensen per categorie --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @foreach($categories as $key => $cat)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                <h3 class="font-bold text-lg mb-3">{{ $cat['icon'] }} {{ $cat['label'] }}</h3>
                @if(isset($wishes[$key]))
                    @foreach($wishes[$key] as $wish)
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <span class="text-sm">{{ $wish->label }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $wish->weight === 'must_have' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : '' }}
                                {{ $wish->weight === 'nice_to_have' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' : '' }}
                                {{ $wish->weight === 'bonus' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : '' }}
                            ">
                                {{ $wish->weight === 'must_have' ? 'Must have' : ($wish->weight === 'nice_to_have' ? 'Nice to have' : 'Bonus') }}
                            </span>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-400">Geen wensen</p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Landen ranking --}}
    @if($countries->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h3 class="font-bold text-lg mb-4">🌍 Landen Ranking</h3>
        <div class="space-y-3">
            @foreach($countries as $country)
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $country->flag_emoji }}</span>
                    <div class="flex-1">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium">{{ $country->name }}</span>
                            <span>{{ $country->match_score }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            @php $color = $country->match_score >= 70 ? '#22c55e' : ($country->match_score >= 40 ? '#f59e0b' : '#ef4444'); @endphp
                            <div class="h-2 rounded-full" style="width: {{ $country->match_score }}%; background-color: {{ $color }}"></div>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $country->status === 'favoriet' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $country->status === 'actief' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $country->status === 'afgewezen' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $country->status === 'onderzoek' ? 'bg-amber-100 text-amber-700' : '' }}
                    ">{{ ucfirst($country->status) }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</x-filament-panels::page>
