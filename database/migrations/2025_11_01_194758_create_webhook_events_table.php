<?php

use App\Enums\WebhookEvent;
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
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('provider', 16);                // ls | stripe
            $table->enum('type', WebhookEvent::cases())->index();           // event name
            $table->json('payload');                       // raw webhook JSON
            $table->timestamp('processed_at')->nullable(); // set when successfully handled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
