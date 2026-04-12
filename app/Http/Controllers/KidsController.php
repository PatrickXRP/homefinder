<?php

namespace App\Http\Controllers;

use App\Models\KidsAccount;
use App\Models\PhotoSwipe;
use App\Models\Property;
use Illuminate\Http\Request;

class KidsController extends Controller
{
    public function login()
    {
        $kids = KidsAccount::where('is_active', true)->orderBy('name')->get();
        return view('kids.login', ['kids' => $kids]);
    }

    public function authenticate(Request $request)
    {
        $account = KidsAccount::where('name', $request->input('name'))
            ->where('pin', $request->input('pin'))
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return back()->with('error', 'Verkeerde PIN!');
        }

        session([
            'kid_id' => $account->id,
            'kid_name' => $account->name,
            'kid_emoji' => $account->emoji,
            'kid_pin' => $account->pin,
            'kid_color' => $account->color,
        ]);

        // Redirect to first available module
        if ($account->module_photo_swiper) return redirect('/kids/swipe?mode=photo');
        if ($account->module_property_swiper) return redirect('/kids/swipe?mode=property');
        if ($account->module_property_overview) return redirect('/kids/huizen');
        return redirect('/kids');
    }

    public function logout()
    {
        session()->forget(['kid_id', 'kid_name', 'kid_emoji', 'kid_pin', 'kid_color']);
        return redirect('/kids');
    }

    public function swipe(Request $request)
    {
        $account = $this->getAccount();
        if (!$account) return redirect('/kids');

        $mode = $request->query('mode', session('kid_mode', 'photo'));

        // Check module access
        if ($mode === 'photo' && !$account->module_photo_swiper) $mode = 'property';
        if ($mode === 'property' && !$account->module_property_swiper) $mode = 'photo';
        session(['kid_mode' => $mode]);

        // Get filtered properties
        $properties = $account->filteredProperties()->get();

        if ($mode === 'photo') {
            return $this->photoSwipe($account, $properties, $mode);
        } else {
            return $this->propertySwipe($account, $properties, $mode);
        }
    }

    private function photoSwipe(KidsAccount $account, $properties, string $mode)
    {
        // Build all photos list
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

        shuffle($allPhotos);

        $swiped = PhotoSwipe::where('kid_name', $account->name)->pluck('image_url')->toArray();
        $remaining = collect($allPhotos)->reject(fn ($p) => in_array($p['image_url'], $swiped))->values();

        $current = $remaining->first();
        $totalPhotos = count($allPhotos);
        $swipedCount = count($swiped);
        $round = $totalPhotos > 0 ? (int) floor($swipedCount / $totalPhotos) + 1 : 1;

        if ($remaining->isEmpty() && $totalPhotos > 0) {
            $round++;
            $current = $allPhotos[0] ?? null;
        }

        return view('kids.swipe', compact('current', 'totalPhotos', 'swipedCount', 'round', 'mode', 'account'));
    }

    private function propertySwipe(KidsAccount $account, $properties, string $mode)
    {
        $swiped = PhotoSwipe::where('kid_name', $account->name)
            ->distinct('property_id')
            ->pluck('property_id')
            ->toArray();

        $remaining = $properties->reject(fn ($p) => in_array($p->id, $swiped))->shuffle();
        $current = $remaining->first();
        $totalProps = $properties->count();
        $swipedCount = count(array_unique($swiped));

        $currentPhotos = [];
        if ($current && is_array($current->images)) {
            $currentPhotos = $current->images;
        }

        return view('kids.swipe-property', [
            'current' => $current,
            'currentPhotos' => $currentPhotos,
            'totalProps' => $totalProps,
            'swipedCount' => $swipedCount,
            'mode' => $mode,
            'account' => $account,
        ]);
    }

    public function doSwipe(Request $request)
    {
        $account = $this->getAccount();
        if (!$account) return redirect('/kids');

        PhotoSwipe::create([
            'property_id' => $request->input('property_id'),
            'kid_name' => $account->name,
            'kid_pin' => $account->pin,
            'photo_index' => $request->input('photo_index', 0),
            'image_url' => $request->input('image_url', ''),
            'rating' => $request->input('rating'),
        ]);

        return redirect('/kids/swipe?mode=' . session('kid_mode', 'photo'));
    }

    public function huizen()
    {
        $account = $this->getAccount();
        if (!$account) return redirect('/kids');

        if (!$account->module_property_overview && !$account->module_property_swiper) {
            return redirect('/kids/swipe');
        }

        $ratingValues = ['super_tof' => 5, 'leuk' => 4, 'gaat_wel' => 3, 'niet_leuk' => 2, 'bah' => 1];

        $swipes = PhotoSwipe::where('kid_name', $account->name)
            ->with('property.country')
            ->get()
            ->groupBy('property_id');

        $properties = $swipes->map(function ($group) use ($ratingValues) {
            $avg = $group->avg(fn ($s) => $ratingValues[$s->rating] ?? 3);
            $liked = $group->filter(fn ($s) => in_array($s->rating, ['super_tof', 'leuk']))->count();
            return [
                'property' => $group->first()->property,
                'avg' => round($avg, 1),
                'liked_photos' => $liked,
                'total_photos' => $group->count(),
            ];
        })->sortByDesc('avg')->values();

        // If overview module enabled, also show all filtered properties
        $allProperties = $account->module_property_overview
            ? $account->filteredProperties()->with('country')->get()
            : collect();

        return view('kids.huizen', compact('properties', 'account', 'allProperties'));
    }

    private function getAccount(): ?KidsAccount
    {
        $id = session('kid_id');
        if (!$id) return null;
        return KidsAccount::where('id', $id)->where('is_active', true)->first();
    }
}
