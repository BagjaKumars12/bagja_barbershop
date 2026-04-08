<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $barbers = Barber::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('specialties', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.barbers.index', compact('barbers', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialties'      => 'nullable|string',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'jobs_count'       => 'nullable|integer|min:0',
            'experience_years' => 'nullable|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('barbers', 'public');
            $imagePath = basename($imagePath);
        }

        Barber::create(array_merge($validated, ['image' => $imagePath]));

        return redirect()->route('admin.barbers.index')
            ->with('success', 'Barber berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $barber = Barber::findOrFail($id);
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialties'      => 'nullable|string',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'jobs_count'       => 'nullable|integer|min:0',
            'experience_years' => 'nullable|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        $imagePath = $barber->image;
        if ($request->hasFile('image')) {
            // Hapus foto lama jika ada
            if ($imagePath && Storage::disk('public')->exists('barbers/' . $imagePath)) {
                Storage::disk('public')->delete('barbers/' . $imagePath);
            }
            $imagePath = $request->file('image')->store('barbers', 'public');
            $imagePath = basename($imagePath);
        }

        $barber->update(array_merge($validated, ['image' => $imagePath]));

        return redirect()->route('admin.barbers.index')
            ->with('success', 'Barber berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $barber = Barber::findOrFail($id);
        if ($barber->image && Storage::disk('public')->exists('barbers/' . $barber->image)) {
            Storage::disk('public')->delete('barbers/' . $barber->image);
        }
        $barber->delete();

        return redirect()->route('admin.barbers.index')
            ->with('success', 'Barber berhasil dihapus.');
    }
}