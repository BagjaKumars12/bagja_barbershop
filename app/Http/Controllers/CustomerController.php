<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Customer::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}