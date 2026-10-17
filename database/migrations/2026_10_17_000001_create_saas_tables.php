<?php

use Database\Seeders\PlanSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_team_id')->nullable()->after('password')->constrained('teams')->nullOnDelete();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('max_members')->nullable();
            $table->unsignedInteger('max_projects')->nullable();
            $table->string('description');
            $table->timestamps();
        });

        (new PlanSeeder)->run();

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('status');
            $table->string('gateway')->default('fake');
            $table->string('gateway_customer_id')->nullable();
            $table->string('gateway_subscription_id')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('role');
            $table->string('token')->unique();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('description');
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('team_invitations');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_team_id');
        });
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
