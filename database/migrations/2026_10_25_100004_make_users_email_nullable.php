<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Email is nullable from the base users migration (fresh installs).
        // Kept as a no-op for environments that already ran older migrations.
    }

    public function down(): void
    {
        //
    }
};
