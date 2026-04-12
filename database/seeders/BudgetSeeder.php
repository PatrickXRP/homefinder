<?php

namespace Database\Seeders;

use App\Models\BudgetScenario;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        BudgetScenario::updateOrCreate(
            ['name' => 'Standaard budget'],
            [
                'total_budget_eur' => 60000,
                'purchase_budget_eur' => 45000,
                'renovation_budget_eur' => 10000,
                'annual_costs_max_eur' => 3000,
                'is_active' => true,
                'notes' => 'Initieel budget voor vakantiehuis. €45k aankoop + €10k verbouwing + €5k reserve.',
            ]
        );
    }
}
