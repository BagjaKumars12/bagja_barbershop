<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use Illuminate\Http\Request;

class OwnerBarberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $barbers = Barber::when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('specialties', 'LIKE', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('owner.barbers.index', compact('barbers', 'search'));
    }
}