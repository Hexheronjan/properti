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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['peserta', 'admin_marketing', 'admin_keuangan', 'admin_screening', 'super_admin'])->default('peserta');
            $table->string('phone')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->string('nik')->nullable()->unique();
            $table->date('birth_date')->nullable();
            $table->string('occupation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = ['role', 'phone', 'google_id', 'avatar', 'address', 'nik', 'birth_date', 'occupation'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};