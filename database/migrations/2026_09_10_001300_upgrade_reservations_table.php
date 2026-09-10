<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * File d'attente des réservations : exemplaire affecté, notification et
 * délai de retrait.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'exemplaire_id')) {
                $table->foreignId('exemplaire_id')->nullable()->after('livre_id')
                    ->constrained('exemplaires')->nullOnDelete();
            }
            if (! Schema::hasColumn('reservations', 'date_notification')) {
                $table->timestamp('date_notification')->nullable()->after('date_expiration');
            }
            if (! Schema::hasColumn('reservations', 'date_limite_retrait')) {
                $table->date('date_limite_retrait')->nullable()->after('date_notification');
            }
            if (! Schema::hasColumn('reservations', 'notes')) {
                $table->text('notes')->nullable()->after('position_file_attente');
            }
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index(['livre_id', 'statut'], 'reservations_livre_statut_index');
            $table->index(['user_id', 'statut'], 'reservations_user_statut_index');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('reservations_livre_statut_index');
            $table->dropIndex('reservations_user_statut_index');
        });

        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'exemplaire_id')) {
                $table->dropConstrainedForeignId('exemplaire_id');
            }
            $table->dropColumn(array_values(array_filter(
                ['date_notification', 'date_limite_retrait', 'notes'],
                fn ($c) => Schema::hasColumn('reservations', $c)
            )));
        });
    }
};
