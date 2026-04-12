<?php

namespace Database\Seeders;

use App\Models\Wish;
use Illuminate\Database\Seeder;

class WishSeeder extends Seeder
{
    public function run(): void
    {
        $wishes = [
            // Natuur & Locatie
            ['category' => 'natuur', 'label' => 'Bij een meer of rivier (vissen)', 'weight' => 'must_have', 'value' => 'Viswater op loop-/rijafstand', 'sort_order' => 1],
            ['category' => 'natuur', 'label' => 'Aan bosrand of in het bos', 'weight' => 'must_have', 'sort_order' => 2],
            ['category' => 'natuur', 'label' => 'Privé zwemmogelijkheid', 'weight' => 'nice_to_have', 'sort_order' => 3],
            ['category' => 'natuur', 'label' => 'Geen directe buren zichtbaar', 'weight' => 'nice_to_have', 'sort_order' => 4],
            ['category' => 'natuur', 'label' => 'Wintersportgebied binnen ±1 uur', 'weight' => 'nice_to_have', 'sort_order' => 5],
            ['category' => 'natuur', 'label' => 'Jacht mogelijk (of later met vergunning)', 'weight' => 'nice_to_have', 'notes' => 'Eerst als vakantiehuis, later evt. inschrijven voor jachtvergunning', 'sort_order' => 6],

            // Woning
            ['category' => 'woning', 'label' => 'Min. 5 slaapplekken', 'weight' => 'must_have', 'value' => '5', 'sort_order' => 1],
            ['category' => 'woning', 'label' => 'Woonoppervlak min. 90m²', 'weight' => 'must_have', 'value' => '90m²', 'sort_order' => 2],
            ['category' => 'woning', 'label' => 'Perceel min. 600m²', 'weight' => 'must_have', 'value' => '600m²', 'sort_order' => 3],
            ['category' => 'woning', 'label' => 'Woonkamer + keuken + kantoorruimte', 'weight' => 'must_have', 'sort_order' => 4],
            ['category' => 'woning', 'label' => 'Mag opknapper zijn (zelf klussen)', 'weight' => 'bonus', 'notes' => 'Patrick kan zelf verbouwen, ouder huis is prima', 'sort_order' => 5],
            ['category' => 'woning', 'label' => 'Sauna (of ruimte om te bouwen)', 'weight' => 'bonus', 'sort_order' => 6],
            ['category' => 'woning', 'label' => 'Bijgebouw/schuur', 'weight' => 'nice_to_have', 'sort_order' => 7],

            // Zelfvoorzienend
            ['category' => 'zelfvoorzienend', 'label' => 'Ruimte voor moestuin', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'zelfvoorzienend', 'label' => 'Ruimte voor kippen/dieren', 'weight' => 'nice_to_have', 'sort_order' => 2],
            ['category' => 'zelfvoorzienend', 'label' => 'Vismogelijkheid nabij', 'weight' => 'must_have', 'sort_order' => 3],
            ['category' => 'zelfvoorzienend', 'label' => 'Eigen waterput of bron mogelijk', 'weight' => 'bonus', 'sort_order' => 4],

            // Bereikbaarheid
            ['category' => 'bereikbaarheid', 'label' => 'Vliegveld binnen 2 uur rijden', 'weight' => 'nice_to_have', 'sort_order' => 1],
            ['category' => 'bereikbaarheid', 'label' => 'Bereikbaar met budget airlines vanuit EU', 'weight' => 'nice_to_have', 'sort_order' => 2],
            ['category' => 'bereikbaarheid', 'label' => 'Supermarkt/dorp binnen 45 min', 'weight' => 'nice_to_have', 'value' => '45 minuten', 'sort_order' => 3],

            // Remote werk
            ['category' => 'remote_werk', 'label' => 'Internet niet nodig (Starlink)', 'weight' => 'bonus', 'notes' => 'Hebben eigen Starlink, lokaal internet is bonus', 'sort_order' => 1],
            ['category' => 'remote_werk', 'label' => 'Rustige werkplek/kantoor in huis', 'weight' => 'must_have', 'sort_order' => 2],

            // Financieel
            ['category' => 'financieel', 'label' => 'Aankoopprijs max €60k', 'weight' => 'must_have', 'value' => '60000', 'sort_order' => 1],
            ['category' => 'financieel', 'label' => 'Cash koop (geen hypotheek)', 'weight' => 'must_have', 'notes' => 'Alles cash betalen', 'sort_order' => 2],
            ['category' => 'financieel', 'label' => 'Max €3k jaarlijkse kosten', 'weight' => 'nice_to_have', 'value' => '3000', 'sort_order' => 3],
            ['category' => 'financieel', 'label' => 'Buitenlanders mogen kopen zonder inschrijving', 'weight' => 'must_have', 'notes' => 'Eerst als vakantiehuis, later evt. inschrijven', 'sort_order' => 4],

            // Kinderen
            ['category' => 'kinderen', 'label' => 'Veilige omgeving om te spelen', 'weight' => 'must_have', 'sort_order' => 1],
            ['category' => 'kinderen', 'label' => 'Natuur om te ontdekken', 'weight' => 'must_have', 'sort_order' => 2],
            ['category' => 'kinderen', 'label' => 'Geen drukke weg bij huis', 'weight' => 'nice_to_have', 'sort_order' => 3],
            ['category' => 'kinderen', 'label' => 'Homeschooling (AI-gestuurd)', 'weight' => 'must_have', 'notes' => 'Geen school nodig, kinderen krijgen les via AI', 'sort_order' => 4],

            // Taal & Cultuur
            ['category' => 'taal_cultuur', 'label' => 'Engels gangbaar in de regio', 'weight' => 'nice_to_have', 'sort_order' => 1],
            ['category' => 'taal_cultuur', 'label' => 'Expat/buitenlander-vriendelijk', 'weight' => 'nice_to_have', 'sort_order' => 2],
        ];

        foreach ($wishes as $wish) {
            Wish::updateOrCreate(
                ['category' => $wish['category'], 'label' => $wish['label']],
                $wish
            );
        }
    }
}
