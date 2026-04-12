<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Zweden', 'name_local' => 'Sverige', 'code' => 'SE', 'flag_emoji' => '🇸🇪', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Roemenië', 'name_local' => 'România', 'code' => 'RO', 'flag_emoji' => '🇷🇴', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Bulgarije', 'name_local' => 'България', 'code' => 'BG', 'flag_emoji' => '🇧🇬', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Portugal', 'name_local' => 'Portugal', 'code' => 'PT', 'flag_emoji' => '🇵🇹', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Griekenland', 'name_local' => 'Ελλάδα', 'code' => 'GR', 'flag_emoji' => '🇬🇷', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Kroatië', 'name_local' => 'Hrvatska', 'code' => 'HR', 'flag_emoji' => '🇭🇷', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Slowakije', 'name_local' => 'Slovensko', 'code' => 'SK', 'flag_emoji' => '🇸🇰', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Georgië', 'name_local' => 'საქართველო', 'code' => 'GE', 'flag_emoji' => '🇬🇪', 'continent' => 'Europa', 'eu_member' => false],
            ['name' => 'Noord-Macedonië', 'name_local' => 'Северна Македонија', 'code' => 'MK', 'flag_emoji' => '🇲🇰', 'continent' => 'Europa', 'eu_member' => false],
            ['name' => 'Albanië', 'name_local' => 'Shqipëria', 'code' => 'AL', 'flag_emoji' => '🇦🇱', 'continent' => 'Europa', 'eu_member' => false],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(['code' => $country['code']], $country);
        }
    }
}
