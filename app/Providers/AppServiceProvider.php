<?php

namespace App\Providers;

use App\Models\CafeTable;
use App\Models\WaitingList;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('ar');
        Paginator::useTailwind();

        Route::bind('table', fn ($value) => CafeTable::findOrFail($value));
        Route::bind('waiting', fn ($value) => WaitingList::findOrFail($value));
    }
}
