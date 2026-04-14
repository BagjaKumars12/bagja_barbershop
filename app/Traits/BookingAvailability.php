<?php

namespace App\Traits;

use App\Models\Booking;
use Carbon\Carbon;

trait BookingAvailability
{
    /**
     * Cek apakah barber tersedia pada waktu yang diminta
     *
     * @param int $barberId
     * @param string $bookingTime (format Y-m-d H:i:s)
     * @param int|null $excludeBookingId (untuk update, kecualikan booking ini sendiri)
     * @return bool
     */
    protected function isBarberAvailable($barberId, $bookingTime, $excludeBookingId = null)
    {
        $start = Carbon::parse($bookingTime);
        // Asumsikan durasi layanan diambil dari service terpanjang? Bisa disederhanakan: 1 slot = 1 jam
        // Atau kita hitung berdasarkan durasi layanan yang dipilih (lebih akurat)
        // Untuk sederhana, kita cek apakah ada booking lain di barber yang sama dalam rentang waktu (misal +/- 1 jam)
        // Tapi lebih baik jika kita simpan durasi di booking. Namun tabel booking tidak punya durasi.
        // Alternatif: kita cek apakah ada booking di barber yang sama pada jam yang sama (tepat).
        // Karena bisnis barber biasanya per customer butuh waktu 30-60 menit, kita asumsikan tidak boleh double di jam yang sama.
        
        // Cek apakah ada booking dengan barber_id dan waktu yang sama (per jam)
        $query = Booking::where('barber_id', $barberId)
            ->where('booking_time', '>=', $start->copy()->subMinutes(30)) // beri toleransi 30 menit sebelumnya
            ->where('booking_time', '<=', $start->copy()->addMinutes(30)) // dan 30 menit setelahnya
            ->whereIn('status', ['pending', 'confirmed']); // hanya booking yang belum selesai

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->doesntExist();
    }

    /**
     * Validasi jam operasional (09:00 - 21:00)
     *
     * @param string $bookingTime
     * @return bool
     */
    protected function isWithinOperatingHours($bookingTime)
    {
        $time = Carbon::parse($bookingTime);
        $hour = (int) $time->format('H');
        $minute = (int) $time->format('i');
        $totalMinutes = $hour * 60 + $minute;

        $open = 9 * 60;      // 09:00
        $close = 21 * 60;    // 21:00

        return ($totalMinutes >= $open && $totalMinutes <= $close);
    }
}