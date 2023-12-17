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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expense_id');
            $table->string('title', 100);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->decimal('amount', 8, 2);
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('category_id')->on('categories');
            $table->date('entry_date');
            $table->timestamp('created_at')->useCurrent();
        });
    
        DB::statement("
            CREATE OR REPLACE FUNCTION expenses_audit_trigger()
            RETURNS TRIGGER AS $$
            BEGIN
                INSERT INTO audit_log (table_name, action, user_id, table_id)
                VALUES ('expenses', TG_OP, NEW.user_id, NEW.expense_id);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
    
        DB::statement("
            CREATE TRIGGER expenses_audit
            AFTER INSERT ON expenses
            FOR EACH ROW
            EXECUTE FUNCTION expenses_audit_trigger();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['category_id']);
        });
    
        Schema::dropIfExists('expenses');
    }
};
