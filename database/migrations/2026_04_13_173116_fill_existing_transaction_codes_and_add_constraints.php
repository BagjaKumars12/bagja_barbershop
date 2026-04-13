<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah kolom transaction_code ada
        if (!Schema::hasColumn('transactions', 'transaction_code')) {
            // Jika tidak ada, tambahkan dulu (sebagai nullable)
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('transaction_code')->nullable()->after('id');
            });
        }

        // Isi data yang masih NULL dengan kode transaksi berdasarkan ID
        $transactions = Transaction::whereNull('transaction_code')->get();
        
        foreach ($transactions as $transaction) {
            $transaction->transaction_code = 'TRX' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
            $transaction->save();
        }

        // Pastikan tidak ada NULL lagi, lalu tambahkan unique constraint dan not null
        // Cek apakah sudah ada unique index
        $indexExists = false;
        $indexes = DB::select("SHOW INDEX FROM transactions WHERE Key_name = 'transactions_transaction_code_unique'");
        if (!empty($indexes)) {
            $indexExists = true;
        }

        if (!$indexExists) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unique('transaction_code');
            });
        }

        // Ubah kolom menjadi NOT NULL (jika belum)
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_code')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus unique constraint
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['transaction_code']);
        });

        // Kembalikan ke nullable (tanpa menghapus data)
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_code')->nullable()->change();
        });
    }
};