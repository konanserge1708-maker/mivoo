<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('worker_profiles', 'withdrawals_blocked')) {
                $table->boolean('withdrawals_blocked')->default(false)->after('country');
            }
            if (! Schema::hasColumn('worker_profiles', 'withdrawals_under_examination')) {
                $table->boolean('withdrawals_under_examination')->default(false)->after('withdrawals_blocked');
            }
            if (! Schema::hasColumn('worker_profiles', 'max_withdrawal_xof')) {
                $table->unsignedInteger('max_withdrawal_xof')->nullable()->after('withdrawals_under_examination');
            }
            if (! Schema::hasColumn('worker_profiles', 'withdrawal_block_reason')) {
                $table->string('withdrawal_block_reason')->nullable()->after('max_withdrawal_xof');
            }
        });
    }

    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $columns = [
                'withdrawals_blocked',
                'withdrawals_under_examination',
                'max_withdrawal_xof',
                'withdrawal_block_reason',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('worker_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
