<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Barber;

class BarberSeeder extends Seeder
{
    public function run()
    {
        // Kosongkan tabel terlebih dahulu agar data tidak duplikat
        Barber::truncate();

        $barbers = [
            [
                'name'             => 'Andi Pratama',
                'specialties'      => 'Fade, Classic Cut',
                'rating'           => 4.9,
                'jobs_count'       => 312,
                'experience_years' => 5,
                'is_active'        => true,
            ],
            [
                'name'             => 'Bagas Eko',
                'specialties'      => 'Coloring, Modern Cut',
                'rating'           => 4.7,
                'jobs_count'       => 198,
                'experience_years' => 3,
                'is_active'        => true,
            ],
            [
                'name'             => 'Cecep Hidayat',
                'specialties'      => 'Beard Styling, Classic Cut',
                'rating'           => 4.8,
                'jobs_count'       => 445,
                'experience_years' => 7,
                'is_active'        => true,
            ],
            [
                'name'             => 'Dimas Farel',
                'specialties'      => 'Undercut, Pompadour',
                'rating'           => 4.5,
                'jobs_count'       => 89,
                'experience_years' => 2,
                'is_active'        => true,
            ],
        ];

        foreach ($barbers as $barber) {
            Barber::create($barber);
        }
    }
}