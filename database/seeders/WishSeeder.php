<?php

namespace Database\Seeders;

use App\Models\Wish;
use Illuminate\Database\Seeder;

class WishSeeder extends Seeder
{
    public function run(): void
    {
        $wishes = [
            ['category' => 'natuur', 'label' => 'Aan meer of rivier', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'natuur', 'label' => 'Privé zwemmogelijkheid', 'weight' => 'must_have', 'sort_order' => 2],
            ['category' => 'natuur', 'label' => 'Geen buren direct zichtbaar', 'weight' => 'nice_to_have', 'sort_order' => 3],
            ['category' => 'natuur', 'label' => 'Bos in de buurt', 'weight' => 'nice_to_have', 'sort_order' => 4],
            ['category' => 'natuur', 'label' => 'Min. 1000m² eigen grond', 'weight' => 'nice_to_have', 'sort_order' => 5],
            ['category' => 'woning', 'label' => 'Min. 3 slaapkamers', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'woning', 'label' => 'Sauna aanwezig of toevoegbaar', 'weight' => 'nice_to_have', 'sort_order' => 2],
            ['category' => 'woning', 'label' => 'Eigen steiger of aanlegplek', 'weight' => 'nice_to_have', 'sort_order' => 3],
            ['category' => 'woning', 'label' => 'Bijgebouw/schuur', 'weight' => 'bonus', 'sort_order' => 4],
            ['category' => 'woning', 'label' => 'Winterbereikbaar', 'weight' => 'nice_to_have', 'sort_order' => 5],
            ['category' => 'bereikbaarheid', 'label' => 'Max 5u vliegen Dubai', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'bereikbaarheid', 'label' => 'Vliegveld < 2u rijden', 'weight' => 'nice_to_have', 'sort_order' => 2],
            ['category' => 'bereikbaarheid', 'label' => 'Stad < 30 min', 'weight' => 'nice_to_have', 'sort_order' => 3],
            ['category' => 'remote_werk', 'label' => 'Stabiel internet min 25 Mbps', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'remote_werk', 'label' => 'Rustige werkplek', 'weight' => 'nice_to_have', 'sort_order' => 2],
            ['category' => 'financieel', 'label' => 'Totaal < €60k', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'financieel', 'label' => 'Max €3k jaarlijkse kosten', 'weight' => 'nice_to_have', 'sort_order' => 2],
            ['category' => 'financieel', 'label' => 'Verhuurpotentieel', 'weight' => 'bonus', 'sort_order' => 3],
            ['category' => 'kinderen', 'label' => 'Veilig zwemwater', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'kinderen', 'label' => 'Natuur om in te spelen', 'weight' => 'must_have', 'sort_order' => 2],
            ['category' => 'kinderen', 'label' => 'Geen drukke weg bij huis', 'weight' => 'nice_to_have', 'sort_order' => 3],
        ];

        foreach ($wishes as $wish) {
            Wish::updateOrCreate(
                ['category' => $wish['category'], 'label' => $wish['label']],
                $wish
            );
        }
    }
}
