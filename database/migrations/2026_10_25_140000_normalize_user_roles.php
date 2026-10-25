<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove obsolete "manager" role — only admin + cashier remain
        DB::table('users')->where('role', 'manager')->update(['role' => 'cashier']);
    }

    public function down(): void
    {
        //
    }
};
