<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Necessário para o lockForUpdate do BookingService funcionar com row-level
            // lock em vez de full-table lock no MySQL
            $table->index(
                ['barber_id', 'booking_date', 'booking_time'],
                'bookings_barber_date_time_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_barber_date_time_idx');
        });
    }
};
