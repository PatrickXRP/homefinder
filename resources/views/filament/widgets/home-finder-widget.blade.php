<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">🏡 HomeFinder</h2>
                @if($budget)
                    <span class="text-sm bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-3 py-1 rounded-full font-medium">
                        Budget: €{{ number_format($budget->total_budget_eur, 0, ',', '.') }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Top 3 landen --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                    <h3 class="font-bold text-sm mb-3 text-gray-500">TOP LANDEN</h3>
                    @forelse($topCountries as $country)
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl">{{ $country->flag_emoji }}</span>
                            <span class="flex-1 font-medium text-sm">{{ $country->name }}</span>
                            @php $color = $country->match_score >= 70 ? 'text-green-600' : ($country->match_score >= 40 ? 'text-amber-600' : 'text-red-600'); @endphp
                            <span class="font-bold {{ $color }}">{{ $country->match_score }}%</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Nog geen scores berekend</p>
                    @endforelse
                    <a href="{{ route('filament.admin.resources.countries.index') }}" class="text-xs text-primary-600 hover:underline mt-2 inline-block">Alle landen →</a>
                </div>

                {{-- Woningen totaal --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                    <h3 class="font-bold text-sm mb-3 text-gray-500">WONINGEN</h3>
                    <div class="text-4xl font-bold mb-2">{{ $totalProperties }}</div>
                    <div class="space-y-1">
                        @foreach($statusLabels as $status => $label)
                            @if(($statusCounts[$status] ?? 0) > 0)
                                <div class="flex justify-between text-sm">
                                    <span>{{ $label }}</span>
                                    <span class="font-medium">{{ $statusCounts[$status] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @if($totalProperties === 0)
                        <p class="text-sm text-gray-400">Nog geen woningen toegevoegd</p>
                    @endif
                    <a href="{{ route('filament.admin.resources.properties.index') }}" class="text-xs text-primary-600 hover:underline mt-2 inline-block">Alle woningen →</a>
                </div>

                {{-- Per land --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                    <h3 class="font-bold text-sm mb-3 text-gray-500">PER LAND</h3>
                    @forelse($countriesWithProperties as $country)
                        <div class="flex items-center gap-2 mb-2">
                            <span>{{ $country->flag_emoji }}</span>
                            <span class="flex-1 text-sm">{{ $country->name }}</span>
                            <span class="font-medium text-sm">{{ $country->properties_count }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Nog geen woningen per land</p>
                    @endforelse
                </div>
            </div>

            {{-- Quick links --}}
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('filament.admin.pages.wensen-overzicht') }}" class="text-xs bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">📋 Wensen</a>
                <a href="{{ route('filament.admin.pages.kosten-calculator') }}" class="text-xs bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">🧮 Calculator</a>
                <a href="{{ route('filament.admin.pages.kinderen') }}" class="text-xs bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">👨‍👩‍👧 Kinderen</a>
                <a href="{{ route('filament.admin.resources.route-plans.index') }}" class="text-xs bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">🗺️ Routes</a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
