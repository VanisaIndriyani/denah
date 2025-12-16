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
        // Cek apakah kolom username sudah ada
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('name');
            });
        }
        
        // Update user yang sudah ada dengan username dari email
        $users = \DB::table('users')->whereNull('username')->orWhere('username', '')->get();
        foreach ($users as $user) {
            $username = explode('@', $user->email)[0]; // Ambil bagian sebelum @ dari email
            $baseUsername = $username;
            $counter = 1;
            
            // Pastikan username unik
            while (\DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            \DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }
        
        // Setelah semua user punya username, buat unique constraint (jika belum ada)
        if (Schema::hasColumn('users', 'username')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('username')->nullable(false)->unique()->change();
                });
            } catch (\Exception $e) {
                // Unique constraint mungkin sudah ada, skip
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
