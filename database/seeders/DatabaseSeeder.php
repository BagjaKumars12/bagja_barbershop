<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Transaction;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Panggil UserSeeder (pastikan ada)
        $this->call(UserSeeder::class);

        // Services
        $services = [
            ['name' => 'Paket Ganteng', 'price' => 40000, 'duration' => 30],
            ['name' => 'Paket Elegan', 'price' => 45000, 'duration' => 45],
            ['name' => 'Grooming', 'price' => 55000, 'duration' => 60],
            ['name' => 'Fade', 'price' => 55000, 'duration' => 30],
            ['name' => 'Haircut', 'price' => 35000, 'duration' => 20],
        ];
        foreach ($services as $s) {
            Service::create($s);
        }

        // Customers
        $customers = [
            ['name' => 'Muhammad Fathir Ardiansyah', 'email' => 'fathir@example.com'],
            ['name' => 'Muhammad Rasya Dwi Yanwar', 'email' => 'rasya@example.com'],
            ['name' => 'Arif M Sinaga', 'email' => 'arif@example.com'],
            ['name' => 'Zulfikar Akbar', 'email' => 'zulfikar@example.com'],
        ];
        $customerModels = [];
        foreach ($customers as $c) {
            $customerModels[] = Customer::create($c);
        }

        // Barbers
        $barbers = [
            ['name' => 'Barber A', 'specialty' => 'Fade'],
            ['name' => 'Barber B', 'specialty' => 'Classic'],
            ['name' => 'Barber C', 'specialty' => 'Beard'],
        ];
        $barberModels = [];
        foreach ($barbers as $b) {
            $barberModels[] = Barber::create($b);
        }

        $today = Carbon::today();

        // Data booking
        $bookingData = [
            ['customer' => 0, 'service' => 0, 'barber' => 0, 'hours_ago' => 3, 'status' => 'completed'],
            ['customer' => 1, 'service' => 1, 'barber' => 1, 'hours_ago' => 4, 'status' => 'completed'],
            ['customer' => 2, 'service' => 2, 'barber' => 2, 'hours_ago' => 5, 'status' => 'completed'],
            ['customer' => 3, 'service' => 3, 'barber' => 0, 'hours_ago' => 6, 'status' => 'completed'],
            ['customer' => 0, 'service' => 1, 'barber' => 1, 'hours_ago' => 2, 'status' => 'pending'],
            ['customer' => 1, 'service' => 2, 'barber' => 2, 'hours_ago' => 1, 'status' => 'confirmed'],
            ['customer' => 2, 'service' => 0, 'barber' => 0, 'hours_ago' => 3, 'status' => 'completed'], // tambahan
            ['customer' => 3, 'service' => 1, 'barber' => 1, 'hours_ago' => 2, 'status' => 'completed'], // tambahan
        ];

        $bookings = [];
        foreach ($bookingData as $data) {
            $booking = Booking::create([
                'customer_id' => $customerModels[$data['customer']]->id,
                'service_id' => Service::find($data['service'] + 1)->id, // ID mulai dari 1
                'barber_id' => $barberModels[$data['barber']]->id,
                'booking_time' => $today->copy()->subHours($data['hours_ago']),
                'status' => $data['status'],
            ]);
            $bookings[] = $booking;
        }

        // Buat transaksi untuk booking yang status completed
        foreach ($bookings as $booking) {
            if ($booking->status === 'completed') {
                $service = $booking->service;
                if ($service) {
                    Transaction::create([
                        'booking_id' => $booking->id,
                        'amount' => $service->price,
                        'status' => 'paid',
                        'paid_at' => $booking->booking_time->copy()->addMinutes($service->duration),
                    ]);
                }
            }
        }
    }
}