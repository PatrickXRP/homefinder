<?php

namespace App\Http\Controllers;

use App\Models\PhotoSwipe;
use App\Models\Property;
use Illuminate\Http\Request;

class KidsController extends Controller
{
    private const KIDS = [
        ['name' => 'Naomi', 'pin' => '1111', 'emoji' => '👧', 'age' => 11, 'color' => '#ec4899'],
        ['name' => 'Sam', 'pin' => '2222', 'emoji' => '🧒', 'age' => 10, 'color' => '#3b82f6'],
        ['name' => 'Zoe', 'pin' => '3333', 'emoji' => '👧', 'age' => 8, 'color' => '#a855f7'],
        ['name' => 'Nathalie', 'pin' => '4444', 'emoji' => '👩', 'age' => null, 'color' => '#f59e0b'],
        ['name' => 'Patrick', 'pin' => '5555', 'emoji' => '👨', 'age' => null, 'color' => '#10b981'],
    ];

    public function login()
    {
        return view('kids.login', ['kids' => self::KIDS]);
    }

    public function authenticate(Request $request)
    {
        $name = $request->input('name');
        $pin = $request->input('pin');

        $kid = collect(self::KIDS)->firstWhere('name', $name);
        if (!$kid || $kid['pin'] !== $pin) {
            return back()->with('error', 'Verkeerde PIN!');
        }

        session(['kid_name' => $kid['name'], 'kid_emoji' => $kid['emoji'], 'kid_pin' => $kid['pin'], 'kid_color' => $kid['color']]);
        return redirect('/kids/swipe');
    }

    public function logout()
    {
        session()->forget(['kid_name', 'kid_emoji', 'kid_pin', 'kid_color']);
        return redirect('/kids');
    }

    public function swipe(Request $request)
    {
        $kidName = session('kid_name');
        if (!$kidName) return redirect('/kids');

        // Get ALL photo URLs from all properties
        $properties = Property::whereNotNull('images')
            ->where('asking_price_eur', '>', 0)
            ->get();

        $allPhotos = [];
        foreach ($properties as $prop) {
            if (!is_array($prop->images)) continue;
            foreach ($prop->images as $idx => $url) {
                $allPhotos[] = [
                    'property_id' => $prop->id,
                    'photo_index' => $idx,
                    'image_url' => $url,
                    'price' => $prop->asking_price_eur,
                    'living_area' => $prop->living_area_m2,
                    'plot_area' => $prop->plot_area_m2,
                    'bedrooms' => $prop->bedrooms,
                ];
            }
        }

        // Shuffle deterministically per kid
        shuffle($allPhotos);

        // Remove already swiped
        $swiped = PhotoSwipe::where('kid_name', $kidName)->pluck('image_url')->toArray();
        $remaining = collect($allPhotos)->reject(fn ($p) => in_array($p['image_url'], $swiped))->values();

        $current = $remaining->first();
        $totalPhotos = count($allPhotos);
        $swipedCount = count($swiped);
        $round = $totalPhotos > 0 ? (int) floor($swipedCount / $totalPhotos) + 1 : 1;

        // If all done, start round 2 (reset)
        if ($remaining->isEmpty() && $totalPhotos > 0) {
            $round++;
            $current = $allPhotos[0] ?? null;
        }

        $mode = $request->query('mode', session('kid_mode', 'photo'));
        session(['kid_mode' => $mode]);

        return view('kids.swipe', compact('current', 'totalPhotos', 'swipedCount', 'round', 'mode'));
    }

    public function doSwipe(Request $request)
    {
        $kidName = session('kid_name');
        if (!$kidName) return redirect('/kids');

        PhotoSwipe::create([
            'property_id' => $request->input('property_id'),
            'kid_name' => $kidName,
            'kid_pin' => session('kid_pin'),
            'photo_index' => $request->input('photo_index', 0),
            'image_url' => $request->input('image_url'),
            'rating' => $request->input('rating'),
        ]);

        return redirect('/kids/swipe?mode=' . session('kid_mode', 'photo'));
    }

    public function huizen()
    {
        $kidName = session('kid_name');
        if (!$kidName) return redirect('/kids');

        // Get liked properties (grouped by property, avg score)
        $ratingValues = ['super_tof' => 5, 'leuk' => 4, 'gaat_wel' => 3, 'niet_leuk' => 2, 'bah' => 1];

        $swipes = PhotoSwipe::where('kid_name', $kidName)
            ->with('property.country')
            ->get()
            ->groupBy('property_id');

        $properties = $swipes->map(function ($group) use ($ratingValues) {
            $avg = $group->avg(fn ($s) => $ratingValues[$s->rating] ?? 3);
            $liked = $group->filter(fn ($s) => in_array($s->rating, ['super_tof', 'leuk']))->count();
            $total = $group->count();
            return [
                'property' => $group->first()->property,
                'avg' => round($avg, 1),
                'liked_photos' => $liked,
                'total_photos' => $total,
            ];
        })->sortByDesc('avg')->values();

        return view('kids.huizen', compact('properties'));
    }
}
