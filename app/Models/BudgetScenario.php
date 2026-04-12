<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetScenario extends Model
{
    protected $fillable = ['name', 'total_budget_eur', 'purchase_budget_eur', 'renovation_budget_eur', 'annual_costs_max_eur', 'is_active', 'notes'];

    protected $casts = [
        'is_active' => 'boolean',
        'total_budget_eur' => 'integer',
        'purchase_budget_eur' => 'integer',
        'renovation_budget_eur' => 'integer',
        'annual_costs_max_eur' => 'integer',
    ];
}
