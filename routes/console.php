<?php

use App\Jobs\ScrapePropertiesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ScrapePropertiesJob)->twiceDaily(7, 19)
    ->name('scrape-properties')
    ->withoutOverlapping();
