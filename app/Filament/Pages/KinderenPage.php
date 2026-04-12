<?php

namespace App\Filament\Pages;

use App\Models\KidsRating;
use App\Models\Property;
use Filament\Pages\Page;

class KinderenPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-face-smile';
    protected static string | \UnitEnum | null $navigationGroup = 'Gezin';
    protected static ?string $navigationLabel = 'Kinderen';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Huizen Swiper';
    protected static ?string $slug = 'kinderen';

    protected string $view = 'filament.pages.kinderen-page';

    public ?int $selectedKid = null;
    public string $mode = 'photo'; // 'photo' or 'specs'
    public bool $showResults = false;
    public int $currentPhotoIndex = 0;

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
        $this->showResults = false;
        $this->currentPhotoIndex = 0;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
        $this->showResults = false;
        $this->currentPhotoIndex = 0;
    }

    public function swipe(int $propertyId, string $rating): void
    {
        $child = config('homefinder.family.children')[$this->selectedKid] ?? null;
        if (!$child) return;

        KidsRating::updateOrCreate(
            ['property_id' => $propertyId, 'kid_name' => $child['name']],
            ['kid_emoji' => $child['emoji'], 'rating' => $rating]
        );

        $this->currentPhotoIndex = 0;
    }

    public function nextPhoto(): void
    {
        $this->currentPhotoIndex++;
    }

    public function prevPhoto(): void
    {
        if ($this->currentPhotoIndex > 0) $this->currentPhotoIndex--;
    }

    public function toggleResults(): void
    {
        $this->showResults = !$this->showResults;
    }

    public function resetRatings(): void
    {
        $child = config('homefinder.family.children')[$this->selectedKid] ?? null;
        if (!$child) return;

        KidsRating::where('kid_name', $child['name'])->delete();
        $this->showResults = false;
    }

    protected function getViewData(): array
    {
        $children = config('homefinder.family.children', []);
        $currentChild = $children[$this->selectedKid] ?? null;

        // Get all properties with images
        $allProperties = Property::whereNotNull('images')
            ->where('asking_price_eur', '>', 0)
            ->with('country')
            ->inRandomOrder()
            ->get();

        $ratings = [];
        if ($currentChild) {
            $ratings = KidsRating::where('kid_name', $currentChild['name'])
                ->pluck('rating', 'property_id')
                ->toArray();
        }

        // Unrated properties for swiping
        $ratedIds = array_keys($ratings);
        $unrated = $allProperties->reject(fn ($p) => in_array($p->id, $ratedIds));
        $currentProperty = $unrated->first();

        // Results: liked properties with reveal
        $ratingValues = ['super_tof' => 5, 'leuk' => 4, 'gaat_wel' => 3, 'niet_leuk' => 2, 'bah' => 1];
        $likedProperties = collect();
        $dislikedCount = 0;
        if (!empty($ratings)) {
            foreach ($ratings as $propId => $rating) {
                $prop = $allProperties->firstWhere('id', $propId);
                if (!$prop) continue;
                if (in_array($rating, ['super_tof', 'leuk'])) {
                    $likedProperties->push(['property' => $prop, 'rating' => $rating]);
                } else {
                    $dislikedCount++;
                }
            }
            $likedProperties = $likedProperties->sortByDesc(fn ($item) => $ratingValues[$item['rating']] ?? 3);
        }

        // Combined favorites across all kids
        $allKidsRatings = KidsRating::with('property.country')->get()->groupBy('property_id');
        $favorites = $allKidsRatings->map(function ($group) use ($ratingValues, $allProperties) {
            $avg = $group->avg(fn ($r) => $ratingValues[$r->rating] ?? 3);
            $prop = $allProperties->firstWhere('id', $group->first()->property_id) ?? $group->first()->property;
            $kids = $group->map(fn ($r) => [
                'name' => $r->kid_name,
                'emoji' => $r->kid_emoji,
                'rating' => $r->rating,
            ]);
            return ['property' => $prop, 'avg' => round($avg, 1), 'count' => $group->count(), 'kids' => $kids];
        })->filter(fn ($f) => $f['avg'] >= 3.5)->sortByDesc('avg')->take(10);

        return compact(
            'children', 'currentChild', 'currentProperty',
            'ratings', 'unrated', 'likedProperties', 'dislikedCount',
            'favorites', 'allProperties'
        );
    }
}
