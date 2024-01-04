<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Get the migration connection name.
     */
    public function getConnection(): string|null
    {
        return config('telescope.storage.database.connection');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection($this->getConnection());

        $schema->create('telescope_admins', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->uuid('uuid');
            $table->string('email');
            $table->string('password');
            $table->enum('role', ['superadmin', 'admin']);
            $table->string('allowed_applications')->default('[]');
            $table->datetime('signedin_at')->nullable();
            $table->timestamps();

            $table->unique('uuid');
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection($this->getConnection());

        $schema->dropIfExists('telescope_admins');
    }
};
