<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Helpers\CurrencyHelper;
use App\Models\Property;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home-modern';
    protected static string | \UnitEnum | null $navigationGroup = 'Woningen';
    protected static ?string $navigationLabel = 'Woningen';
    protected static ?string $modelLabel = 'Woning';
    protected static ?string $pluralModelLabel = 'Woningen';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('Woning')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Overzicht')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            // Photo gallery
                            Forms\Components\Placeholder::make('photo_gallery')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record || empty($record->images)) {
                                        return new \Illuminate\Support\HtmlString("
                                            <div class='flex items-center justify-center h-48 bg-gray-100 dark:bg-gray-800 rounded-xl'>
                                                <span class='text-gray-400 text-lg'>Geen foto's beschikbaar</span>
                                            </div>
                                        ");
                                    }
                                    $first = $record->images[0];
                                    $rest = array_slice($record->images, 1, 5);
                                    $thumbs = collect($rest)->map(fn ($url) =>
                                        "<a href='{$url}' target='_blank' class='block'><img src='{$url}' class='w-full h-full object-cover' loading='lazy'></a>"
                                    )->join('');
                                    $moreCount = max(0, count($record->images) - 6);
                                    $moreHtml = $moreCount > 0 ? "<div class='absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded'>+{$moreCount} meer</div>" : '';
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='grid grid-cols-4 gap-2 rounded-xl overflow-hidden' style='height: 320px'>
                                            <div class='col-span-2 row-span-2 relative'>
                                                <a href='{$first}' target='_blank' class='block h-full'><img src='{$first}' class='w-full h-full object-cover' loading='lazy'></a>
                                            </div>
                                            <div class='grid grid-cols-2 col-span-2 gap-2 relative'>
                                                {$thumbs}
                                                {$moreHtml}
                                            </div>
                                        </div>
                                    ");
                                })
                                ->columnSpanFull(),

                            // Quick links bar
                            Forms\Components\Placeholder::make('quick_links')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record) return '';
                                    $links = [];
                                    if ($record->url) {
                                        $links[] = "<a href='{$record->url}' target='_blank' class='inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition'>🔗 Originele advertentie</a>";
                                    }
                                    $addr = urlencode($record->address ?? $record->city . ', ' . ($record->country?->name ?? ''));
                                    $links[] = "<a href='https://www.google.com/maps/search/?api=1&query={$addr}' target='_blank' class='inline-flex items-center gap-1.5 px-4 py-2 bg-green-50 text-green-700 rounded-lg text-sm font-medium hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300 dark:hover:bg-green-900/50 transition'>📍 Google Maps</a>";
                                    return new \Illuminate\Support\HtmlString('<div class="flex gap-2 flex-wrap mt-2">' . implode('', $links) . '</div>');
                                })
                                ->columnSpanFull(),

                            // Key stats bar
                            Forms\Components\Placeholder::make('key_stats')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record) return '';
                                    $stats = [];
                                    if ($record->asking_price_eur) $stats[] = ['label' => 'Prijs', 'value' => '€' . number_format($record->asking_price_eur, 0, ',', '.'), 'icon' => '💰'];
                                    if ($record->price_per_m2) $stats[] = ['label' => '€/m²', 'value' => '€' . number_format($record->price_per_m2, 0, ',', '.'), 'icon' => '📐'];
                                    if ($record->living_area_m2) $stats[] = ['label' => 'Wonen', 'value' => $record->living_area_m2 . ' m²', 'icon' => '🏠'];
                                    if ($record->plot_area_m2) {
                                        $plot = $record->plot_area_m2 >= 10000 ? number_format($record->plot_area_m2 / 10000, 1, ',', '.') . ' ha' : number_format($record->plot_area_m2, 0, ',', '.') . ' m²';
                                        $stats[] = ['label' => 'Perceel', 'value' => $plot, 'icon' => '🌳'];
                                    }
                                    if ($record->bedrooms) $stats[] = ['label' => 'Slaapk.', 'value' => $record->bedrooms, 'icon' => '🛏️'];
                                    if ($record->bathrooms) $stats[] = ['label' => 'Badk.', 'value' => $record->bathrooms, 'icon' => '🚿'];
                                    if ($record->year_built) $stats[] = ['label' => 'Bouwjaar', 'value' => $record->year_built, 'icon' => '📅'];
                                    if ($record->energy_class) $stats[] = ['label' => 'Energie', 'value' => $record->energy_class, 'icon' => '⚡'];

                                    $html = collect($stats)->map(fn ($s) =>
                                        "<div class='flex flex-col items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-xl min-w-[80px]'>
                                            <span class='text-lg'>{$s['icon']}</span>
                                            <span class='text-xs text-gray-500 mt-0.5'>{$s['label']}</span>
                                            <span class='font-bold text-sm'>{$s['value']}</span>
                                        </div>"
                                    )->join('');
                                    return new \Illuminate\Support\HtmlString("<div class='flex gap-3 flex-wrap mt-2'>{$html}</div>");
                                })
                                ->columnSpanFull(),

                            // Location + Features row
                            Forms\Components\Placeholder::make('location_features')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record) return '';

                                    // Location info
                                    $locParts = array_filter([
                                        $record->country?->flag_emoji . ' ' . $record->country?->name,
                                        $record->region,
                                        $record->city,
                                    ]);
                                    $locHtml = implode(' › ', $locParts);

                                    $waterHtml = '';
                                    if ($record->water_type && $record->water_type !== 'geen') {
                                        $waterIcon = match($record->water_type) { 'meer' => '🏞️', 'zee' => '🌊', 'rivier' => '🏞️', default => '💧' };
                                        $waterLabel = match($record->water_type) { 'meer' => 'Meer', 'zee' => 'Zee', 'rivier' => 'Rivier', default => $record->water_type };
                                        $waterHtml = "<span class='inline-flex items-center gap-1 text-sm'>{$waterIcon} {$waterLabel}" . ($record->water_name ? " ({$record->water_name})" : '') . "</span>";
                                    }

                                    $condHtml = '';
                                    if ($record->condition) {
                                        $condLabel = match($record->condition) { 'turnkey' => 'Instapklaar', 'goed' => 'Goed', 'matig' => 'Matig', 'opknapper' => 'Opknapper', 'slooprijp' => 'Slooprijp', default => $record->condition };
                                        $condColor = match($record->condition) { 'turnkey' => 'bg-green-100 text-green-700', 'goed' => 'bg-blue-100 text-blue-700', 'matig' => 'bg-yellow-100 text-yellow-700', 'opknapper' => 'bg-orange-100 text-orange-700', 'slooprijp' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-700' };
                                        $condHtml = "<span class='px-2 py-0.5 rounded-full text-xs font-medium {$condColor}'>{$condLabel}</span>";
                                    }

                                    // Feature badges
                                    $badges = [];
                                    if ($record->has_sauna) $badges[] = "🧖 Sauna";
                                    if ($record->has_jetty) $badges[] = "⚓ Steiger";
                                    if ($record->has_guest_house) $badges[] = "🏡 Gastenverblijf";
                                    if ($record->year_round_accessible) $badges[] = "❄️ Winterbereikbaar";
                                    if ($record->own_road) $badges[] = "🛤️ Eigen weg";
                                    $badgesHtml = collect($badges)->map(fn ($b) =>
                                        "<span class='px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs font-medium'>{$b}</span>"
                                    )->join('');

                                    return new \Illuminate\Support\HtmlString("
                                        <div class='space-y-3 mt-2'>
                                            <div class='flex items-center gap-3 flex-wrap'>
                                                <span class='text-sm font-medium'>{$locHtml}</span>
                                                {$waterHtml}
                                                {$condHtml}
                                            </div>
                                            " . ($badgesHtml ? "<div class='flex gap-2 flex-wrap'>{$badgesHtml}</div>" : '') . "
                                        </div>
                                    ");
                                })
                                ->columnSpanFull(),

                            // Status + Score row
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'gezien_online' => '👀 Gezien online',
                                    'bezichtigen' => '📅 Bezichtigen',
                                    'bezichtigd' => '✅ Bezichtigd',
                                    'interesse' => '💛 Interesse',
                                    'bod_gedaan' => '💰 Bod gedaan',
                                    'afgewezen' => '❌ Afgewezen',
                                    'gekocht' => '🎉 Gekocht',
                                    'archief' => '📦 Archief',
                                ])
                                ->required()
                                ->default('gezien_online'),
                            Forms\Components\Select::make('my_score')
                                ->label('Mijn score')
                                ->options([
                                    1 => '⭐',
                                    2 => '⭐⭐',
                                    3 => '⭐⭐⭐',
                                    4 => '⭐⭐⭐⭐',
                                    5 => '⭐⭐⭐⭐⭐',
                                ]),

                            // Notes
                            Forms\Components\Textarea::make('notes')
                                ->label('Notities')
                                ->rows(3)
                                ->columnSpanFull(),

                            // AI Analysis
                            Forms\Components\Placeholder::make('ai_section')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record || (!$record->ai_score && !$record->ai_analysis)) return '';
                                    $scoreHtml = $record->ai_score
                                        ? "<div class='flex items-center gap-2 mb-2'><span class='text-2xl font-bold'>{$record->ai_score}</span><span class='text-sm text-gray-500'>/ 100 AI Score</span></div>"
                                        : '';
                                    $analysisHtml = $record->ai_analysis
                                        ? "<div class='text-sm prose prose-sm dark:prose-invert max-w-none'>" . nl2br(e($record->ai_analysis)) . "</div>"
                                        : '';
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl mt-2'>
                                            <h4 class='font-bold text-sm text-indigo-700 dark:text-indigo-300 mb-2'>🤖 AI Analyse</h4>
                                            {$scoreHtml}{$analysisHtml}
                                        </div>
                                    ");
                                })
                                ->columnSpanFull(),

                            // Map embed
                            Forms\Components\Placeholder::make('map_embed')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record) return '';
                                    $query = $record->address ?? $record->city;
                                    if ($record->country) $query .= ', ' . $record->country->name;
                                    $encoded = urlencode($query);
                                    return new \Illuminate\Support\HtmlString("
                                        <iframe width='100%' height='250' style='border:0; border-radius: 12px; margin-top: 8px;'
                                            loading='lazy' referrerpolicy='no-referrer-when-downgrade'
                                            src='https://maps.google.com/maps?q={$encoded}&output=embed'></iframe>
                                    ");
                                })
                                ->columnSpanFull(),
                        ])->columns(2),

                    Tab::make('Gegevens')
                        ->icon('heroicon-o-pencil-square')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Naam')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Select::make('country_id')
                                ->label('Land')
                                ->relationship('country', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('source_id')
                                ->label('Bron')
                                ->relationship('source', 'name')
                                ->searchable()
                                ->preload(),
                            Forms\Components\TextInput::make('url')
                                ->label('Listing URL')
                                ->url(),
                            Forms\Components\TextInput::make('external_id')
                                ->label('Extern ID'),
                            Forms\Components\DateTimePicker::make('added_at')
                                ->label('Toegevoegd op')
                                ->default(now()),
                            Forms\Components\TextInput::make('address')
                                ->label('Adres')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('city')
                                ->label('Stad'),
                            Forms\Components\TextInput::make('region')
                                ->label('Regio'),
                            Forms\Components\TextInput::make('latitude')
                                ->label('Breedtegraad')
                                ->numeric(),
                            Forms\Components\TextInput::make('longitude')
                                ->label('Lengtegraad')
                                ->numeric(),
                            Forms\Components\TextInput::make('asking_price')
                                ->label('Vraagprijs')
                                ->numeric(),
                            Forms\Components\Select::make('currency')
                                ->label('Valuta')
                                ->options(collect(CurrencyHelper::RATES)->keys()->mapWithKeys(fn ($k) => [$k => $k])->toArray())
                                ->default('EUR'),
                            Forms\Components\TextInput::make('asking_price_eur')
                                ->label('Vraagprijs EUR')
                                ->numeric()
                                ->prefix('€'),
                            Forms\Components\TextInput::make('price_per_m2')
                                ->label('Prijs per m²')
                                ->numeric()
                                ->prefix('€'),
                            Forms\Components\TextInput::make('year_built')
                                ->label('Bouwjaar')
                                ->numeric(),
                            Forms\Components\TextInput::make('living_area_m2')
                                ->label('Woonoppervlak m²')
                                ->numeric(),
                            Forms\Components\TextInput::make('plot_area_m2')
                                ->label('Perceeloppervlak m²')
                                ->numeric(),
                            Forms\Components\TextInput::make('bedrooms')
                                ->label('Slaapkamers')
                                ->numeric(),
                            Forms\Components\TextInput::make('bathrooms')
                                ->label('Badkamers')
                                ->numeric(),
                            Forms\Components\TextInput::make('energy_class')
                                ->label('Energieklasse'),
                            Forms\Components\Select::make('condition')
                                ->label('Staat')
                                ->options([
                                    'turnkey' => 'Instapklaar',
                                    'goed' => 'Goed',
                                    'matig' => 'Matig',
                                    'opknapper' => 'Opknapper',
                                    'slooprijp' => 'Slooprijp',
                                ]),
                            Forms\Components\Select::make('water_type')
                                ->label('Water')
                                ->options([
                                    'meer' => '🏞️ Meer',
                                    'zee' => '🌊 Zee',
                                    'rivier' => '🏞️ Rivier',
                                    'geen' => 'Geen',
                                ]),
                            Forms\Components\TextInput::make('water_name')
                                ->label('Naam water'),
                            Forms\Components\Toggle::make('has_sauna')->label('Sauna'),
                            Forms\Components\Toggle::make('has_jetty')->label('Steiger'),
                            Forms\Components\Toggle::make('has_guest_house')->label('Gastenverblijf'),
                            Forms\Components\Toggle::make('year_round_accessible')->label('Jaarrond bereikbaar')->default(true),
                            Forms\Components\Toggle::make('own_road')->label('Eigen weg'),
                            Forms\Components\DatePicker::make('viewing_date')
                                ->label('Bezichtigingsdatum'),
                        ])->columns(2),

                    Tab::make("Foto's")
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Forms\Components\Placeholder::make('full_gallery')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record || empty($record->images)) {
                                        return "Nog geen foto's.";
                                    }
                                    $imgs = collect($record->images)->map(fn ($url, $i) =>
                                        "<a href='{$url}' target='_blank' class='block rounded-lg overflow-hidden hover:opacity-90 transition'>
                                            <img src='{$url}' alt='Foto " . ($i+1) . "' class='w-full h-48 object-cover' loading='lazy'>
                                        </a>"
                                    )->join('');
                                    return new \Illuminate\Support\HtmlString("<div class='grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3'>{$imgs}</div>");
                                })
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('images_input')
                                ->label('Afbeelding URLs (één per regel)')
                                ->rows(4)
                                ->columnSpanFull()
                                ->dehydrated(false)
                                ->afterStateHydrated(function (Forms\Components\Textarea $component, ?Property $record) {
                                    if ($record && $record->images) {
                                        $component->state(implode("\n", $record->images));
                                    }
                                })
                                ->helperText("Plak afbeelding-URL's, één per regel."),
                        ]),

                    Tab::make('Prijsgeschiedenis')
                        ->icon('heroicon-o-arrow-trending-down')
                        ->schema([
                            Forms\Components\Repeater::make('priceHistory')
                                ->label('')
                                ->relationship()
                                ->schema([
                                    Forms\Components\DatePicker::make('recorded_date')
                                        ->label('Datum')
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Prijs')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('price_eur')
                                        ->label('Prijs EUR')
                                        ->numeric()
                                        ->prefix('€'),
                                    Forms\Components\TextInput::make('note')
                                        ->label('Notitie'),
                                    Forms\Components\Select::make('source')
                                        ->label('Bron')
                                        ->options([
                                            'scrape' => 'Scrape',
                                            'handmatig' => 'Handmatig',
                                        ])
                                        ->default('handmatig'),
                                ])
                                ->columns(5)
                                ->defaultItems(0)
                                ->addActionLabel('Prijswijziging toevoegen')
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Swiper')
                        ->icon('heroicon-o-hand-thumb-up')
                        ->schema([
                            Forms\Components\Placeholder::make('swipe_results')
                                ->label('')
                                ->content(function (?Property $record) {
                                    if (!$record) return 'Sla eerst op.';
                                    $swipes = \App\Models\PhotoSwipe::where('property_id', $record->id)->get();
                                    if ($swipes->isEmpty()) return 'Nog geen swipe data voor deze woning.';

                                    $byKid = $swipes->groupBy('kid_name');
                                    $ratingEmojis = ['super_tof' => '😍', 'leuk' => '😊', 'gaat_wel' => '😐', 'niet_leuk' => '😕', 'bah' => '👎'];
                                    $ratingValues = ['super_tof' => 5, 'leuk' => 4, 'gaat_wel' => 3, 'niet_leuk' => 2, 'bah' => 1];

                                    $rows = $byKid->map(function ($kidSwipes, $name) use ($ratingEmojis, $ratingValues) {
                                        $avg = round($kidSwipes->avg(fn ($s) => $ratingValues[$s->rating] ?? 3), 1);
                                        $liked = $kidSwipes->filter(fn ($s) => in_array($s->rating, ['super_tof', 'leuk']))->count();
                                        $total = $kidSwipes->count();
                                        $breakdown = $kidSwipes->groupBy('rating')->map(fn ($g, $r) => ($ratingEmojis[$r] ?? $r) . ' ' . $g->count())->join(' · ');
                                        $color = $avg >= 4 ? '#22c55e' : ($avg >= 3 ? '#f59e0b' : '#ef4444');
                                        return "<tr>
                                            <td class='px-3 py-2 font-medium'>{$name}</td>
                                            <td class='px-3 py-2 text-center'><strong style='color:{$color}'>{$avg}</strong> / 5</td>
                                            <td class='px-3 py-2 text-center'>{$liked} / {$total}</td>
                                            <td class='px-3 py-2 text-sm'>{$breakdown}</td>
                                        </tr>";
                                    })->join('');

                                    $totalAvg = round($swipes->avg(fn ($s) => $ratingValues[$s->rating] ?? 3), 1);

                                    return new \Illuminate\Support\HtmlString("
                                        <div class='mb-3 text-center'>
                                            <span class='text-4xl font-bold'>{$totalAvg}</span>
                                            <span class='text-gray-500'>/ 5 gemiddeld</span>
                                        </div>
                                        <table class='w-full text-sm'>
                                            <thead><tr class='border-b'><th class='px-3 py-2 text-left'>Naam</th><th class='px-3 py-2 text-center'>Score</th><th class='px-3 py-2 text-center'>Liked</th><th class='px-3 py-2 text-left'>Verdeling</th></tr></thead>
                                            <tbody>{$rows}</tbody>
                                        </table>
                                    ");
                                })
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('')
                    ->width(60)
                    ->height(45)
                    ->getStateUsing(fn (Property $record) => $record->images[0] ?? null)
                    ->defaultImageUrl('https://placehold.co/60x45/f3f4f6/9ca3af?text=📷'),
                Tables\Columns\TextColumn::make('photo_count')
                    ->label('📷')
                    ->getStateUsing(fn (Property $record) => is_array($record->images) ? count($record->images) : 0)
                    ->sortable(query: fn ($query, string $direction) => $query->orderByRaw("jsonb_array_length(COALESCE(images, '[]'::jsonb)) {$direction}"))
                    ->color(fn ($state) => $state >= 5 ? 'success' : ($state >= 2 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Property $record) => $record->url, shouldOpenInNewTab: true)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('country.flag_emoji')
                    ->label('Land')
                    ->formatStateUsing(fn ($state, Property $record) => ($state ?? '') . ' ' . ($record->country?->name ?? '')),
                Tables\Columns\TextColumn::make('city')
                    ->label('Stad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region')
                    ->label('Provincie')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('asking_price_eur')
                    ->label('Prijs')
                    ->formatStateUsing(fn ($state) => $state ? '€ ' . number_format($state, 0, ',', '.') : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('local_price')
                    ->label('Lokaal')
                    ->getStateUsing(function (Property $record) {
                        if (!$record->asking_price_eur || !$record->country) return null;
                        $currencyMap = [
                            'SE' => 'SEK', 'NO' => 'NOK', 'PL' => 'PLN', 'HU' => 'HUF',
                            'JP' => 'JPY', 'AR' => 'ARS', 'PY' => 'PYG', 'RS' => 'RSD',
                            'GE' => 'GEL', 'MK' => 'MKD', 'HR' => 'HRK',
                        ];
                        $code = $record->country->code ?? '';
                        $cur = $currencyMap[$code] ?? null;
                        if (!$cur) return null; // EUR countries don't need conversion
                        $rate = CurrencyHelper::RATES[$cur] ?? null;
                        if (!$rate || $rate == 1.0) return null;
                        $local = (int) round($record->asking_price_eur / $rate);
                        return $cur . ' ' . number_format($local, 0, ',', '.');
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('living_area_m2')
                    ->label('m²')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plot_area_m2')
                    ->label('Perceel')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        return $state >= 10000
                            ? number_format($state / 10000, 1, ',', '.') . ' ha'
                            : number_format($state, 0, ',', '.') . ' m²';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('Kamers')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_m2')
                    ->label('€/m²')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('my_score')
                    ->label('Score')
                    ->formatStateUsing(fn (?int $state) => $state ? str_repeat('⭐', $state) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('swipe_score')
                    ->label('👨‍👩‍👧')
                    ->getStateUsing(function (Property $record) {
                        $swipes = \App\Models\PhotoSwipe::where('property_id', $record->id)->get();
                        if ($swipes->isEmpty()) return '-';
                        $ratingValues = ['super_tof' => 5, 'leuk' => 4, 'gaat_wel' => 3, 'niet_leuk' => 2, 'bah' => 1];
                        $avg = round($swipes->avg(fn ($s) => $ratingValues[$s->rating] ?? 3), 1);
                        $kids = $swipes->groupBy('kid_name')->map(fn ($g) => round($g->avg(fn ($s) => $ratingValues[$s->rating] ?? 3), 1));
                        $emojis = $kids->map(fn ($score, $name) => ($score >= 4 ? '😍' : ($score >= 3 ? '😐' : '👎')))->join('');
                        return $avg . ' ' . $emojis;
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw("(SELECT AVG(CASE rating WHEN 'super_tof' THEN 5 WHEN 'leuk' THEN 4 WHEN 'gaat_wel' THEN 3 WHEN 'niet_leuk' THEN 2 WHEN 'bah' THEN 1 ELSE 3 END) FROM photo_swipes WHERE photo_swipes.property_id = properties.id) {$direction} NULLS LAST");
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'gezien_online' => 'gray',
                        'bezichtigen' => 'warning',
                        'bezichtigd' => 'info',
                        'interesse' => 'success',
                        'bod_gedaan' => 'primary',
                        'afgewezen' => 'danger',
                        'gekocht' => 'success',
                        'archief' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'gezien_online' => '👀 Online',
                        'bezichtigen' => '📅 Bezichtigen',
                        'bezichtigd' => '✅ Bezichtigd',
                        'interesse' => '💛 Interesse',
                        'bod_gedaan' => '💰 Bod',
                        'afgewezen' => '❌ Afgewezen',
                        'gekocht' => '🎉 Gekocht',
                        'archief' => '📦 Archief',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('added_at')
                    ->label('Toegevoegd')
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->defaultSort('asking_price_eur', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->where('status', '!=', 'archief'))
            ->filters([
                Tables\Filters\TernaryFilter::make('show_archived')
                    ->label('Archief tonen')
                    ->trueLabel('Alleen archief')
                    ->falseLabel('Verberg archief')
                    ->queries(
                        true: fn ($query) => $query->withoutGlobalScopes()->where('status', 'archief'),
                        false: fn ($query) => $query,
                        blank: fn ($query) => $query,
                    ),
                Tables\Filters\SelectFilter::make('country_id')
                    ->label('Land')
                    ->multiple()
                    ->relationship('country', 'name'),
                Tables\Filters\SelectFilter::make('region')
                    ->label('Provincie')
                    ->multiple()
                    ->options(fn () => Property::whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region', 'region')->toArray())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('city')
                    ->label('Stad')
                    ->multiple()
                    ->options(fn () => Property::whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city', 'city')->toArray())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('kid_likes')
                    ->label('Kind likes')
                    ->options(fn () => \App\Models\KidsAccount::where('is_active', true)->pluck('name', 'name')->toArray())
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) return;
                        $query->whereIn('id', function ($sub) use ($data) {
                            $sub->select('property_id')
                                ->from('photo_swipes')
                                ->where('kid_name', $data['value'])
                                ->whereIn('rating', ['super_tof', 'leuk'])
                                ->distinct();
                        });
                    }),
                Tables\Filters\SelectFilter::make('kid_dislikes')
                    ->label('Kind dislikes')
                    ->options(fn () => \App\Models\KidsAccount::where('is_active', true)->pluck('name', 'name')->toArray())
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) return;
                        $query->whereIn('id', function ($sub) use ($data) {
                            $sub->select('property_id')
                                ->from('photo_swipes')
                                ->where('kid_name', $data['value'])
                                ->whereIn('rating', ['niet_leuk', 'bah'])
                                ->distinct();
                        });
                    }),
                Tables\Filters\Filter::make('price_min')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Min prijs €')
                            ->numeric()
                            ->placeholder('0'),
                    ])
                    ->query(fn ($query, array $data) => ($data['value'] ?? null) ? $query->where('asking_price_eur', '>=', $data['value']) : $query),
                Tables\Filters\Filter::make('price_max')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Max prijs €')
                            ->numeric()
                            ->placeholder('60000'),
                    ])
                    ->query(fn ($query, array $data) => ($data['value'] ?? null) ? $query->where('asking_price_eur', '<=', $data['value']) : $query),
                Tables\Filters\Filter::make('area_min')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label('Min m²')
                            ->numeric()
                            ->placeholder('0'),
                    ])
                    ->query(fn ($query, array $data) => ($data['value'] ?? null) ? $query->where('living_area_m2', '>=', $data['value']) : $query),
                Tables\Filters\SelectFilter::make('bedrooms_min')
                    ->label('Min kamers')
                    ->options(['1' => '1+', '2' => '2+', '3' => '3+', '4' => '4+', '5' => '5+'])
                    ->query(fn ($query, $data) => ($data['value'] ?? null) ? $query->where('bedrooms', '>=', $data['value']) : $query),
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Staat')
                    ->options([
                        'turnkey' => 'Instapklaar',
                        'goed' => 'Goed',
                        'matig' => 'Matig',
                        'opknapper' => 'Opknapper',
                    ]),
                Tables\Filters\SelectFilter::make('water_type')
                    ->label('Water')
                    ->options(['meer' => 'Meer', 'zee' => 'Zee', 'rivier' => 'Rivier']),
                Tables\Filters\TernaryFilter::make('has_sauna')
                    ->label('Sauna'),
                Tables\Filters\TernaryFilter::make('has_guest_house')
                    ->label('Gastenverblijf'),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(5)
            ->actions([
                Actions\Action::make('archive')
                    ->label('Archiveer')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (Property $record) => $record->status !== 'archief')
                    ->action(fn (Property $record) => $record->update(['status' => 'archief'])),
                Actions\Action::make('unarchive')
                    ->label('Herstel')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->visible(fn (Property $record) => $record->status === 'archief')
                    ->action(fn (Property $record) => $record->update(['status' => 'gezien_online'])),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('bulk_archive')
                        ->label('📦 Archiveren')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn ($r) => $r->update(['status' => 'archief'])))
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('bulk_unarchive')
                        ->label('📦 Uit archief halen')
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->action(fn ($records) => $records->each(fn ($r) => $r->update(['status' => 'gezien_online'])))
                        ->deselectRecordsAfterCompletion(),
                    Actions\BulkAction::make('bulk_interesse')
                        ->label('💛 Interesse')
                        ->icon('heroicon-o-heart')
                        ->action(fn ($records) => $records->each(fn ($r) => $r->update(['status' => 'interesse'])))
                        ->deselectRecordsAfterCompletion(),
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
