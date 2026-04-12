<?php

namespace App\Filament\Pages;

use App\Models\KidsRating;
use App\Models\KidsWish;
use App\Models\Property;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KinderenPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-face-smile';
    protected static string | \UnitEnum | null $navigationGroup = 'Gezin';
    protected static ?string $navigationLabel = 'Kinderen';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Kinderen';
    protected static ?string $slug = 'kinderen';

    protected string $view = 'filament.pages.kinderen-page';

    public ?int $selectedKid = null;
    public string $newWish = '';

    public function mount(): void
    {
        $children = config('homefinder.family.children', []);
        if (count($children) > 0) {
            $this->selectedKid = 0;
        }
    }

    public function selectKid(int $index): void
    {
        $this->selectedKid = $index;
    }

    public function rate(int $propertyId, string $rating): void
    {
        $child = config('homefinder.family.children')[$this->selectedKid] ?? null;
        if (!$child) return;

        KidsRating::updateOrCreate(
            ['property_id' => $propertyId, 'kid_name' => $child['name']],
            ['kid_emoji' => $child['emoji'], 'rating' => $rating]
        );

        Notification::make()->title('Opgeslagen!')->success()->send();
    }

    public function addWish(): void
    {
        if (empty(trim($this->newWish))) return;

        $child = config('homefinder.family.children')[$this->selectedKid] ?? null;
        if (!$child) return;

        KidsWish::create([
            'kid_name' => $child['name'],
            'kid_emoji' => $child['emoji'],
            'wish' => trim($this->newWish),
        ]);

        $this->newWish = '';
        Notification::make()->title('Wens toegevoegd!')->success()->send();
    }

    public function deleteWish(int $wishId): void
    {
        KidsWish::where('id', $wishId)->delete();
    }

    protected function getViewData(): array
    {
        $children = config('homefinder.family.children', []);
        $currentChild = $children[$this->selectedKid] ?? null;

        $properties = Property::whereIn('status', ['bezichtigen', 'bezichtigd', 'interesse', 'bod_gedaan'])
            ->with('country')
            ->get();

        $ratings = [];
        $wishes = collect();
        if ($currentChild) {
            $ratings = KidsRating::where('kid_name', $currentChild['name'])
                ->pluck('rating', 'property_id')
                ->toArray();
            $wishes = KidsWish::where('kid_name', $currentChild['name'])->get();
        }

        // Favorieten: gemiddelde rating per woning
        $allRatings = KidsRating::with('property.country')->get()->groupBy('property_id');
        $ratingValues = ['super_tof' => 5, 'leuk' => 4, 'gaat_wel' => 3, 'niet_leuk' => 2, 'bah' => 1];
        $favorites = $allRatings->map(function ($group) use ($ratingValues) {
            $avg = $group->avg(fn ($r) => $ratingValues[$r->rating] ?? 3);
            return [
                'property' => $group->first()->property,
                'avg' => round($avg, 1),
                'count' => $group->count(),
            ];
        })->sortByDesc('avg')->take(5);

        $ratingOptions = [
            'super_tof' => '😍',
            'leuk' => '😊',
            'gaat_wel' => '😐',
            'niet_leuk' => '😕',
            'bah' => '😤',
        ];

        return compact('children', 'currentChild', 'properties', 'ratings', 'wishes', 'favorites', 'ratingOptions');
    }
}
