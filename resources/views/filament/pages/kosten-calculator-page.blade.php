<x-filament-panels::page>
    {{-- Input --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">📊 Bereken je kosten</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Land</label>
                <select wire:model.live="selectedCountryId" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->flag_emoji }} {{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Aankoopprijs (€)</label>
                <input type="number" wire:model.live.debounce.500ms="purchasePrice" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" min="0" step="1000">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Verbouwingsbudget (€)</label>
                <input type="number" wire:model.live.debounce.500ms="renovationBudget" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" min="0" step="1000">
            </div>
        </div>
    </div>

    {{-- Berekening --}}
    @if($calculation && $selectedCountry)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Eenmalige kosten --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="font-bold text-lg mb-4">🏠 Eenmalige kosten — {{ $selectedCountry->flag_emoji }} {{ $selectedCountry->name }}</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Aankoopprijs</td>
                        <td class="py-2 text-right font-medium">€{{ number_format($calculation['purchase_price'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Aankoopkosten ({{ $calculation['purchase_costs_pct'] }}%)</td>
                        <td class="py-2 text-right font-medium">€{{ number_format($calculation['purchase_costs'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Inspectie / keuring</td>
                        <td class="py-2 text-right font-medium">€{{ number_format($calculation['inspection'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Verbouwingsbudget</td>
                        <td class="py-2 text-right font-medium">€{{ number_format($calculation['renovation'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t-2 border-gray-900 dark:border-white">
                        <td class="py-3 font-bold text-lg">Totaal</td>
                        <td class="py-3 text-right font-bold text-lg {{ $calculation['total_purchase'] > 60000 ? 'text-red-600' : 'text-green-600' }}">
                            €{{ number_format($calculation['total_purchase'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Jaarlijkse kosten --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="font-bold text-lg mb-4">📅 Jaarlijkse kosten (schatting)</h3>
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Onroerendgoedbelasting ({{ $calculation['annual_tax_pct'] }}%)</td>
                        <td class="py-2 text-right font-medium">€{{ number_format($calculation['annual_tax'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Verzekering</td>
                        <td class="py-2 text-right font-medium">€{{ number_format($calculation['annual_insurance'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">Onderhoud</td>
                        <td class="py-2 text-right font-medium">€{{ number_format($calculation['annual_maintenance'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t-2 border-gray-900 dark:border-white">
                        <td class="py-3 font-bold text-lg">Totaal per jaar</td>
                        <td class="py-3 text-right font-bold text-lg {{ $calculation['annual_total'] > 3000 ? 'text-red-600' : 'text-green-600' }}">
                            €{{ number_format($calculation['annual_total'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
            @if($calculation['notes'])
                <div class="mt-3 text-xs text-gray-500 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                    ℹ️ {{ $calculation['notes'] }}
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Vergelijkingstabel --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h3 class="font-bold text-lg mb-4">🌍 Vergelijking alle landen</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="py-2 text-left">Land</th>
                        <th class="py-2 text-right">Aankoopkosten</th>
                        <th class="py-2 text-right">Totaal aankoop</th>
                        <th class="py-2 text-right">Jaarlast (est.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparison as $row)
                        <tr class="border-b dark:border-gray-700 {{ $row['country']->id === $selectedCountryId ? 'bg-primary-50 dark:bg-primary-900/20 font-medium' : '' }}">
                            <td class="py-2">{{ $row['country']->flag_emoji }} {{ $row['country']->name }}</td>
                            <td class="py-2 text-right">€{{ number_format($row['purchase_costs'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right {{ $row['total'] > 60000 ? 'text-red-600' : 'text-green-600' }}">
                                €{{ number_format($row['total'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 text-right {{ $row['annual'] > 3000 ? 'text-red-600' : '' }}">
                                €{{ number_format($row['annual'], 0, ',', '.') }}/jr
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
