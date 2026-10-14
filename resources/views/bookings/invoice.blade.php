<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folio Invoice {{ $booking->booking_code }} | Villa Management System</title>

    <!-- Local Offline Assets - 100% Internal Network Compatible (No CDN) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: system-ui, -apple-system, sans-serif;
            color: #1e293b;
            padding: 2rem 0;
        }

        .invoice-paper {
            max-width: 850px;
            margin: auto;
            background: #ffffff;
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-top: 6px solid #c29b38;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .invoice-paper {
                box-shadow: none;
                padding: 1.5rem;
                max-width: 100%;
                border-top: none;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 850px; margin: auto;">
            <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Return to Booking
            </a>
            <button onclick="window.print()" class="btn btn-dark btn-sm">
                <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
            </button>
        </div>

        <div class="invoice-paper">
            <!-- Header -->
            <div class="row align-items-start mb-4">
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="p-2 bg-dark text-warning rounded-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-hotel fs-5"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark">Luxury Villa Resort</h4>
                    </div>
                    <div class="text-muted small">
                        Luxury Villa & Resort Operations<br>
                        100 Emerald Coastline Boulevard<br>
                        Phone: +1 (555) 019-2800 &bull; reservations@villamanagement.local
                    </div>
                </div>

                <div class="col-6 text-end">
                    <h3 class="fw-bold text-dark mb-1">GUEST FOLIO</h3>
                    <div class="text-muted small">
                        <strong>Invoice Ref:</strong> FOL-{{ str_replace('BK-', '', $booking->booking_code) }}<br>
                        <strong>Booking Code:</strong> {{ $booking->booking_code }}<br>
                        <strong>Issued Date:</strong> {{ now()->format('F j, Y') }}<br>
                        <strong>Status:</strong> <span class="badge {{ $booking->getPaymentBadgeClass() }}">{{ strtoupper($booking->payment_status) }}</span>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Guest & Property Details -->
            <div class="row mb-4">
                <div class="col-6">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Billed To</span>
                    <h5 class="fw-bold text-dark mb-1">{{ $booking->guest->full_name }}</h5>
                    <div class="text-secondary small">
                        Email: {{ $booking->guest->email }}<br>
                        Phone: {{ $booking->guest->phone ?: '—' }}<br>
                        Passport / ID: {{ $booking->guest->id_or_passport ?: '—' }}<br>
                        Nationality: {{ $booking->guest->nationality ?: '—' }}
                    </div>
                </div>

                <div class="col-6 text-end">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Stay Particulars</span>
                    <h5 class="fw-bold text-dark mb-1">{{ $booking->villa->name }}</h5>
                    <div class="text-secondary small">
                        Villa Code: <strong>{{ $booking->villa->code }}</strong> ({{ $booking->villa->category }})<br>
                        Arrival: {{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }} (14:00)<br>
                        Departure: {{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }} (11:00)<br>
                        Duration: <strong>{{ $booking->total_nights }} Nights</strong> &bull; Guests: {{ $booking->adults }} Adults
                    </div>
                </div>
            </div>

            <!-- Itemized Table -->
            <table class="table table-bordered mb-4">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Rate / Unit</th>
                        <th class="text-center">Qty / Nights</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Luxury Villa Accommodation</strong>
                            <div class="text-muted small">{{ $booking->villa->name }} ({{ $booking->villa->code }})</div>
                        </td>
                        <td class="text-center">${{ number_format($booking->nightly_rate, 2) }}</td>
                        <td class="text-center">{{ $booking->total_nights }}</td>
                        <td class="text-end fw-semibold">${{ number_format($booking->total_nights * $booking->nightly_rate, 2) }}</td>
                    </tr>
                    @if($booking->cleaning_fee > 0)
                        <tr>
                            <td>Turnover & Deep Sanitization Cleaning Service</td>
                            <td class="text-center">${{ number_format($booking->cleaning_fee, 2) }}</td>
                            <td class="text-center">1</td>
                            <td class="text-end fw-semibold">${{ number_format($booking->cleaning_fee, 2) }}</td>
                        </tr>
                    @endif
                    @if($booking->service_fee > 0)
                        <tr>
                            <td>Resort Concierge & Dedicated Amenities Surcharge</td>
                            <td class="text-center">${{ number_format($booking->service_fee, 2) }}</td>
                            <td class="text-center">1</td>
                            <td class="text-end fw-semibold">${{ number_format($booking->service_fee, 2) }}</td>
                        </tr>
                    @endif
                    @if($booking->tax_amount > 0)
                        <tr>
                            <td>Hospitality & Tourism Tax Assessment</td>
                            <td class="text-center">${{ number_format($booking->tax_amount, 2) }}</td>
                            <td class="text-center">1</td>
                            <td class="text-end fw-semibold">${{ number_format($booking->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total Charges:</th>
                        <th class="text-end fs-5 fw-bold">${{ number_format($booking->total_amount, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end text-success">Less Settled Payments:</th>
                        <th class="text-end text-success fs-5 fw-bold">-${{ number_format($booking->paid_amount, 2) }}</th>
                    </tr>
                    <tr class="table-light">
                        <th colspan="3" class="text-end fs-5">Balance Outstanding:</th>
                        <th class="text-end fs-5 fw-bold {{ $booking->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($booking->balance_due, 2) }}
                        </th>
                    </tr>
                </tfoot>
            </table>

            <!-- Payments History -->
            @if($booking->payments->isNotEmpty())
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Payment Receipts</h6>
                    <table class="table table-sm table-striped small">
                        <thead>
                            <tr>
                                <th>Transaction Ref</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->payments as $p)
                                <tr>
                                    <td>{{ $p->transaction_reference }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $p->payment_method) }}</td>
                                    <td>{{ $p->payment_date ? $p->payment_date->format('M d, Y') : $p->created_at->format('M d, Y') }}</td>
                                    <td class="text-end fw-bold">${{ number_format($p->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Footer terms -->
            <div class="pt-4 border-top text-center text-muted small">
                <p class="mb-1">Thank you for staying at our Luxury Resorts & Estates.</p>
                <p class="mb-0">Internal Local Folio Document &bull; Generated from secure resort management network.</p>
            </div>
        </div>
    </div>

</body>
</html>
