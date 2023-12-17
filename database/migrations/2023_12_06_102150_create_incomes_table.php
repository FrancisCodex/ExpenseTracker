<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id('income_id');
            $table->string('title', 100);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->decimal('amount', 10, 2);
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('category_id')->on('categories');
            $table->date('entry_date');
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("
            CREATE OR REPLACE FUNCTION incomes_audit_trigger()
            RETURNS TRIGGER AS $$
            BEGIN
                INSERT INTO audit_log (table_name, action, user_id, table_id)
                VALUES ('incomes', TG_OP, NEW.user_id, NEW.income_id);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER incomes_audit
            AFTER INSERT ON incomes
            FOR EACH ROW
            EXECUTE FUNCTION incomes_audit_trigger();
        ");
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['category_id']);
        });
    
        Schema::dropIfExists('incomes');
    }
};
