<?php

namespace Database\Seeders;

use App\Models\KidsAccount;
use Illuminate\Database\Seeder;

class KidsAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Naomi', 'pin' => '1111', 'emoji' => '👧', 'color' => '#ec4899', 'age' => 11, 'module_photo_swiper' => true, 'module_property_swiper' => true, 'module_property_overview' => false],
            ['name' => 'Sam', 'pin' => '2222', 'emoji' => '🧒', 'color' => '#3b82f6', 'age' => 10, 'module_photo_swiper' => true, 'module_property_swiper' => true, 'module_property_overview' => false],
            ['name' => 'Zoe', 'pin' => '3333', 'emoji' => '👧', 'color' => '#a855f7', 'age' => 8, 'module_photo_swiper' => true, 'module_property_swiper' => false, 'module_property_overview' => false],
            ['name' => 'Nathalie', 'pin' => '4444', 'emoji' => '👩', 'color' => '#f59e0b', 'age' => null, 'module_photo_swiper' => true, 'module_property_swiper' => true, 'module_property_overview' => true],
            ['name' => 'Patrick', 'pin' => '5555', 'emoji' => '👨', 'color' => '#10b981', 'age' => null, 'module_photo_swiper' => true, 'module_property_swiper' => true, 'module_property_overview' => true],
        ];

        foreach ($accounts as $account) {
            KidsAccount::updateOrCreate(['name' => $account['name']], $account);
        }
    }
}
