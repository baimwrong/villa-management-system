<?php

namespace App\Http\Controllers;

use App\Http\Requests\VillaRequest;
use App\Models\Booking;
use App\Models\Villa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VillaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Villa::query();

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('category')) {
            $query->category($request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', (int) $request->input('bedrooms'));
        }

        $villas = $query->orderBy('code')->paginate(9)->withQueryString();

        $categories = Villa::select('category')->distinct()->pluck('category');

        return view('villas.index', [
            'villas' => $villas,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status', 'bedrooms']),
        ]);
    }

    public function create(): View
    {
        $standardAmenities = [
            'Private Pool', 'Ocean View', 'Private Beach', 'Sunset Deck', 'Butler Service',
            'Personal Chef', 'Gym & Wellness', 'Cinema Room', 'Wine Cellar', 'Jacuzzi',
            'Helipad Access', 'Smart Home System', 'Snorkel & Kayak Gear', 'Outdoor Kitchen',
            'Wi-Fi 6', 'Sauna / Steam Room',
        ];

        return view('villas.create', [
            'standardAmenities' => $standardAmenities,
        ]);
    }

    public function store(VillaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']).'-'.strtolower($validated['code']);
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('villas', 'public');
            $validated['featured_image'] = $path;
        }

        $villa = Villa::create($validated);

        return redirect()->route('villas.show', $villa)
            ->with('success', "Villa '{$villa->name}' created successfully.");
    }

    public function show(Villa $villa): View
    {
        $villa->load([
            'bookings' => fn ($q) => $q->with('guest')->latest()->take(5),
            'maintenanceTasks' => fn ($q) => $q->with('assignedStaff')->latest()->take(5),
            'expenses' => fn ($q) => $q->latest()->take(5),
        ]);

        $activeBooking = $villa->bookings()
            ->whereIn('status', [Booking::STATUS_CHECKED_IN, Booking::STATUS_CONFIRMED])
            ->where('check_in', '<=', now()->toDateString())
            ->where('check_out', '>=', now()->toDateString())
            ->with('guest')
            ->first();

        return view('villas.show', [
            'villa' => $villa,
            'activeBooking' => $activeBooking,
        ]);
    }

    public function edit(Villa $villa): View
    {
        $standardAmenities = [
            'Private Pool', 'Ocean View', 'Private Beach', 'Sunset Deck', 'Butler Service',
            'Personal Chef', 'Gym & Wellness', 'Cinema Room', 'Wine Cellar', 'Jacuzzi',
            'Helipad Access', 'Smart Home System', 'Snorkel & Kayak Gear', 'Outdoor Kitchen',
            'Wi-Fi 6', 'Sauna / Steam Room',
        ];

        return view('villas.edit', [
            'villa' => $villa,
            'standardAmenities' => $standardAmenities,
        ]);
    }

    public function update(VillaRequest $request, Villa $villa): RedirectResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']).'-'.strtolower($validated['code']);
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('featured_image')) {
            if ($villa->featured_image && Storage::disk('public')->exists($villa->featured_image)) {
                Storage::disk('public')->delete($villa->featured_image);
            }
            $path = $request->file('featured_image')->store('villas', 'public');
            $validated['featured_image'] = $path;
        }

        $villa->update($validated);

        return redirect()->route('villas.show', $villa)
            ->with('success', "Villa '{$villa->name}' updated successfully.");
    }

    public function updateStatus(Request $request, Villa $villa): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:available,occupied,cleaning,maintenance,reserved'],
        ]);

        $villa->update(['status' => $request->string('status')]);

        return back()->with('success', "Status for '{$villa->name}' updated to ".ucfirst($villa->status).'.');
    }

    public function destroy(Villa $villa): RedirectResponse
    {
        $hasBookings = $villa->bookings()->exists();

        if ($hasBookings) {
            return back()->with('error', "Cannot delete '{$villa->name}' because historical or active reservations are linked to this property.");
        }

        $villa->delete();

        return redirect()->route('villas.index')
            ->with('success', "Villa '{$villa->name}' removed successfully.");
    }
}
