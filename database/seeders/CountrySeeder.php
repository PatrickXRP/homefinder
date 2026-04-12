<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            // Scandinavië & Baltisch
            ['name' => 'Zweden', 'name_local' => 'Sverige', 'code' => 'SE', 'flag_emoji' => '🇸🇪', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Finland', 'name_local' => 'Suomi', 'code' => 'FI', 'flag_emoji' => '🇫🇮', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Noorwegen', 'name_local' => 'Norge', 'code' => 'NO', 'flag_emoji' => '🇳🇴', 'continent' => 'Europa', 'eu_member' => false],
            ['name' => 'Estland', 'name_local' => 'Eesti', 'code' => 'EE', 'flag_emoji' => '🇪🇪', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Letland', 'name_local' => 'Latvija', 'code' => 'LV', 'flag_emoji' => '🇱🇻', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Litouwen', 'name_local' => 'Lietuva', 'code' => 'LT', 'flag_emoji' => '🇱🇹', 'continent' => 'Europa', 'eu_member' => true],
            // Midden-Europa
            ['name' => 'Polen', 'name_local' => 'Polska', 'code' => 'PL', 'flag_emoji' => '🇵🇱', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Hongarije', 'name_local' => 'Magyarország', 'code' => 'HU', 'flag_emoji' => '🇭🇺', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Slowakije', 'name_local' => 'Slovensko', 'code' => 'SK', 'flag_emoji' => '🇸🇰', 'continent' => 'Europa', 'eu_member' => true],
            // Zuid-Europa
            ['name' => 'Portugal', 'name_local' => 'Portugal', 'code' => 'PT', 'flag_emoji' => '🇵🇹', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Spanje', 'name_local' => 'España', 'code' => 'ES', 'flag_emoji' => '🇪🇸', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Italië', 'name_local' => 'Italia', 'code' => 'IT', 'flag_emoji' => '🇮🇹', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Griekenland', 'name_local' => 'Ελλάδα', 'code' => 'GR', 'flag_emoji' => '🇬🇷', 'continent' => 'Europa', 'eu_member' => true],
            ['name' => 'Kroatië', 'name_local' => 'Hrvatska', 'code' => 'HR', 'flag_emoji' => '🇭🇷', 'continent' => 'Europa', 'eu_member' => true],
            // Balkan & Kaukasus
            ['name' => 'Servië', 'name_local' => 'Србија', 'code' => 'RS', 'flag_emoji' => '🇷🇸', 'continent' => 'Europa', 'eu_member' => false],
            ['name' => 'Noord-Macedonië', 'name_local' => 'Северна Македонија', 'code' => 'MK', 'flag_emoji' => '🇲🇰', 'continent' => 'Europa', 'eu_member' => false],
            ['name' => 'Georgië', 'name_local' => 'საქართველო', 'code' => 'GE', 'flag_emoji' => '🇬🇪', 'continent' => 'Europa', 'eu_member' => false],
            // Azië
            ['name' => 'Japan', 'name_local' => '日本', 'code' => 'JP', 'flag_emoji' => '🇯🇵', 'continent' => 'Azië', 'eu_member' => false],
            // Zuid-Amerika
            ['name' => 'Argentinië', 'name_local' => 'Argentina', 'code' => 'AR', 'flag_emoji' => '🇦🇷', 'continent' => 'Zuid-Amerika', 'eu_member' => false],
            ['name' => 'Paraguay', 'name_local' => 'Paraguay', 'code' => 'PY', 'flag_emoji' => '🇵🇾', 'continent' => 'Zuid-Amerika', 'eu_member' => false],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(['code' => $country['code']], $country);
        }
    }
}
