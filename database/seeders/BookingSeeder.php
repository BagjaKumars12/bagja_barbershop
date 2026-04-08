<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run()
    {
        // Ambil data relasi yang sudah ada
        $customers = Customer::all();
        $services = Service::all();
        $barbers = Barber::all();

        if ($customers->isEmpty() || $services->isEmpty() || $barbers->isEmpty()) {
            $this->command->warn('Pastikan data customer, service, dan barber sudah ada sebelum menjalankan seeder ini.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $paymentMethods = ['cash', 'card', 'transfer'];

        // Buat 15 booking dengan data acak
        for ($i = 1; $i <= 15; $i++) {
            $service = $services->random();
            $bookingTime = Carbon::now()->addDays(rand(-10, 10))->setTime(rand(9, 20), rand(0, 59));
            $status = $statuses[array_rand($statuses)];

            Booking::create([
                'booking_code'   => 'BK' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'customer_id'    => $customers->random()->id,
                'service_id'     => $service->id,
                'barber_id'      => $barbers->random()->id,
                'booking_time'   => $bookingTime,
                'status'         => $status,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'notes'          => $this->getRandomNote(),
                'total_price'    => $service->price,
            ]);
        }

        // Tambahkan booking untuk hari ini (untuk dashboard)
        $today = Carbon::today();
        $todayBookings = [
            ['customer' => 0, 'service' => 0, 'barber' => 0, 'time' => $today->setTime(10, 0), 'status' => 'pending'],
            ['customer' => 1, 'service' => 1, 'barber' => 1, 'time' => $today->setTime(11, 30), 'status' => 'confirmed'],
            ['customer' => 2, 'service' => 2, 'barber' => 2, 'time' => $today->setTime(13, 0), 'status' => 'completed'],
            ['customer' => 3, 'service' => 3, 'barber' => 0, 'time' => $today->setTime(14, 30), 'status' => 'pending'],
            ['customer' => 0, 'service' => 1, 'barber' => 1, 'time' => $today->setTime(16, 0), 'status' => 'confirmed'],
        ];

        foreach ($todayBookings as $index => $data) {
            $service = $services[$data['service']];
            Booking::create([
                'booking_code'   => 'BK' . str_pad(16 + $index, 3, '0', STR_PAD_LEFT),
                'customer_id'    => $customers[$data['customer']]->id,
                'service_id'     => $service->id,
                'barber_id'      => $barbers[$data['barber']]->id,
                'booking_time'   => $data['time'],
                'status'         => $data['status'],
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'notes'          => 'Booking hari ini',
                'total_price'    => $service->price,
            ]);
        }
    }

    private function getRandomNote()
    {
        $notes = [
            'Pelanggan minta potongan pendek',
            'Ingin dipotong dengan clipper nomor 2',
            'Tambahan hair tonic',
            'Booking untuk anak kecil',
            'Minta barber senior',
            'Ingin pangkas kumis juga',
        ];
        return $notes[array_rand($notes)];
    }
}