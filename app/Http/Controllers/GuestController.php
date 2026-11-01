<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestRequest;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $query = Guest::query();

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('vip_level')) {
            $query->where('vip_level', $request->string('vip_level'));
        }

        $guests = $query->withCount('bookings')
            ->withSum('bookings as actual_spent', 'paid_amount')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('guests.index', [
            'guests' => $guests,
            'filters' => $request->only(['search', 'vip_level']),
        ]);
    }

    public function create(): View
    {
        return view('guests.create');
    }

    public function store(GuestRequest $request): RedirectResponse
    {
        $guest = Guest::create($request->validated());

        return redirect()->route('guests.show', $guest)
            ->with('success', "Guest profile for '{$guest->full_name}' created successfully.");
    }

    public function show(Guest $guest): View
    {
        $guest->load(['bookings' => fn ($q) => $q->with('villa')->latest()]);

        return view('guests.show', [
            'guest' => $guest,
        ]);
    }

    public function edit(Guest $guest): View
    {
        return view('guests.edit', [
            'guest' => $guest,
        ]);
    }

    public function update(GuestRequest $request, Guest $guest): RedirectResponse
    {
        $guest->update($request->validated());

        return redirect()->route('guests.show', $guest)
            ->with('success', "Guest profile for '{$guest->full_name}' updated successfully.");
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        if ($guest->bookings()->exists()) {
            return back()->with('error', "Cannot delete guest '{$guest->full_name}' because they have associated booking and billing history. Consider archiving or updating their profile instead.");
        }

        $name = $guest->full_name;
        $guest->delete();

        return redirect()->route('guests.index')
            ->with('success', "Guest '{$name}' deleted.");
    }
}
