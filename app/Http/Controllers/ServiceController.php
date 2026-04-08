<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $services = Service::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('category', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('admin.services.index', compact('services', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateService($request);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('services', 'public');
            $imagePath = basename($imagePath);
        }

        Service::create([
            'name'      => $validated['name'],
            'image'     => $imagePath,
            'category'  => $validated['category'],
            'duration'  => $validated['duration'],
            'price'     => $validated['price'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $this->validateService($request, $id);

        $imagePath = $service->image;
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists('services/' . $imagePath)) {
                Storage::disk('public')->delete('services/' . $imagePath);
            }
            $imagePath = $request->file('image')->store('services', 'public');
            $imagePath = basename($imagePath);
        }

        $service->update([
            'name'      => $validated['name'],
            'image'     => $imagePath,
            'category'  => $validated['category'],
            'duration'  => $validated['duration'],
            'price'     => $validated['price'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        if ($service->image && Storage::disk('public')->exists('services/' . $service->image)) {
            Storage::disk('public')->delete('services/' . $service->image);
        }
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }

    private function validateService(Request $request, $serviceId = null)
    {
        return $request->validate([
            'name'      => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category'  => 'required|in:potong,grooming,perawatan,warna',
            'duration'  => 'nullable|integer|min:1',
            'price'     => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);
    }
}