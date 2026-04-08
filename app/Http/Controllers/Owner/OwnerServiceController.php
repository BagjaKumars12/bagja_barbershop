<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class OwnerServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $services = Service::when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('owner.services.index', compact('services', 'search'));
    }
}