<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class OwnerCustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $customers = Customer::when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(6)
            ->withQueryString();

        return view('owner.customers.index', compact('customers', 'search'));
    }
}