@extends('layouts.app')

@section('title', 'Guests Directory')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">VIP Guests Directory</h3>
        <p class="text-muted small mb-0">Maintain guest dossiers, VIP classifications, preferences, and historical spend.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('guests.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Register New Guest</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card card-luxury mb-4 p-3">
    <form action="{{ route('guests.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-7 col-sm-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by guest name, email, phone, or passport..." value="{{ $filters['search'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <select name="vip_level" class="form-select form-select-sm">
                <option value="">All VIP Classifications</option>
                <option value="vvip" {{ ($filters['vip_level'] ?? '') === 'vvip' ? 'selected' : '' }}>VVIP Dignitary</option>
                <option value="vip" {{ ($filters['vip_level'] ?? '') === 'vip' ? 'selected' : '' }}>VIP Preferred</option>
                <option value="standard" {{ ($filters['vip_level'] ?? '') === 'standard' ? 'selected' : '' }}>Standard Guest</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-dark w-100" title="Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
            <a href="{{ route('guests.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Guests Table -->
<div class="card card-luxury">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Guest Profile</th>
                    <th>Classification</th>
                    <th>Contact Information</th>
                    <th>Nationality</th>
                    <th>Stays Count</th>
                    <th>Lifetime Value</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guests as $guest)
                    <tr>
                        <td>
                            <a href="{{ route('guests.show', $guest) }}" class="fw-bold text-decoration-none text-dark d-block">
                                {{ $guest->full_name }}
                            </a>
                            <span class="text-muted small" style="font-size: 0.75rem;">Passport: {{ $guest->id_or_passport ?: '—' }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $guest->getVipBadgeClass() }}">
                                {{ strtoupper($guest->vip_level) }}
                            </span>
                        </td>
                        <td class="small">
                            <div><i class="fa-regular fa-envelope me-1 text-muted"></i>{{ $guest->email }}</div>
                            @if($guest->phone)
                                <div class="text-muted"><i class="fa-solid fa-phone me-1 text-muted"></i>{{ $guest->phone }}</div>
                            @endif
                        </td>
                        <td class="small">
                            {{ $guest->nationality ?: 'International' }}
                        </td>
                        <td class="small">
                            <span class="badge bg-light text-dark border">{{ $guest->bookings_count }} Bookings</span>
                        </td>
                        <td class="fw-bold text-dark">
                            ${{ number_format($guest->actual_spent ?: $guest->total_spent, 2) }}
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('guests.show', $guest) }}" class="btn btn-light" title="View Profile">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('guests.edit', $guest) }}" class="btn btn-light" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-slash fs-2 text-muted mb-2 d-block"></i>
                            No guests matching your query.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $guests->links('pagination::bootstrap-5') }}
</div>
@endsection
