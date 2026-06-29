<?php

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
        //
        Schema::create('businesses', function (Blueprint $table){
            $table->id();
            $table->foreignId('user_id')
            ->constrained()
            ->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('businessName');
            $table->string('category');

            $table->text('description');
            $table->text('about')->nullable();

            $table->string('streetAddress');
            $table->string('city');
            $table->string('parish');

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->decimal('rating',3,2)->default(0);

            $table->boolean('featured')->default(false);

            $table->boolean('verified')->default(false);

            $table->unsignedInteger('reviewCount')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('businesses');
    }
};
