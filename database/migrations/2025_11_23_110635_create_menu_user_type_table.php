<?php

use App\Models\Menu;
use App\Models\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_user_type', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Menu::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(UserType::class)->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination of menu and user_type
            $table->unique(['menu_id', 'user_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_user_type');
    }
};
