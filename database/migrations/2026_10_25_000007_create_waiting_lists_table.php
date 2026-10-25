<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiting_lists', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->unsignedTinyInteger('party_size')->default(1);
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('waiting'); // waiting|seated|cancelled
            $table->foreignId('cafe_table_id')->nullable()->constrained('cafe_tables')->nullOnDelete();
            $table->timestamp('seated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiting_lists');
    }
};
