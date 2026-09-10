<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penalites')) {
            Schema::create('penalites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('emprunt_id')->nullable()->constrained('emprunts')->nullOnDelete();
                // retard | perte | degradation | autre
                $table->string('type', 20)->default('retard');
                $table->decimal('montant', 10, 2)->default(0);
                $table->decimal('montant_paye', 10, 2)->default(0);
                // impayee | partiellement_payee | payee | annulee
                $table->string('statut', 25)->default('impayee');
                $table->integer('jours_retard')->nullable();
                $table->text('motif')->nullable();
                $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('date_annulation')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'statut']);
                $table->index('type');
            });
        }

        if (! Schema::hasTable('paiements_penalites')) {
            Schema::create('paiements_penalites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penalite_id')->constrained('penalites')->cascadeOnDelete();
                $table->decimal('montant', 10, 2);
                $table->string('mode_paiement', 30)->default('especes'); // especes|mobile_money|virement|cheque
                $table->string('reference')->nullable();
                $table->foreignId('encaisse_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('date_paiement')->useCurrent();
                $table->text('observation')->nullable();
                $table->timestamps();

                $table->index('penalite_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_penalites');
        Schema::dropIfExists('penalites');
    }
};
