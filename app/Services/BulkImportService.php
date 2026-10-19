<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\ImportLog;
use App\Models\Payment;
use App\Models\User;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkImportService
{
    /**
     * Parse and import a CSV file based on the requested entity type.
     *
     * @return array{total: int, imported: int, failed: int, errors: array<string>}
     */
    public function import(string $type, UploadedFile $file, ?User $user = null): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to read uploaded file.');
        }

        // Detect BOM and read header
        $header = fgetcsv($handle);
        if ($header === false || empty($header)) {
            fclose($handle);
            throw new \InvalidArgumentException('Uploaded CSV file is empty or missing headers.');
        }

        // Clean UTF-8 BOM from first header column if present
        $header[0] = preg_replace('/[\x{EF}\x{BB}\x{BF}]/u', '', $header[0]);
        $header = array_map(fn ($col) => strtolower(trim((string) $col)), $header);

        $total = 0;
        $imported = 0;
        $failed = 0;
        $errors = [];

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue;
            }

            $total++;

            if (count($header) !== count($row)) {
                $failed++;
                $errors[] = "Row {$rowNumber}: Column count does not match header count.";

                continue;
            }

            $data = array_combine($header, array_map('trim', $row));

            try {
                DB::beginTransaction();

                match ($type) {
                    ImportLog::TYPE_VILLAS => $this->importVilla($data),
                    ImportLog::TYPE_GUESTS => $this->importGuest($data),
                    ImportLog::TYPE_BOOKINGS => $this->importBooking($data, $user),
                    ImportLog::TYPE_EXPENSES => $this->importExpense($data),
                    default => throw new \InvalidArgumentException("Unsupported import type: {$type}"),
                };

                DB::commit();
                $imported++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $failed++;
                $errors[] = "Row {$rowNumber}: ".$e->getMessage();
            }
        }

        fclose($handle);

        // Record import log
        ImportLog::create([
            'user_id' => $user?->id,
            'import_type' => $type,
            'file_name' => $file->getClientOriginalName(),
            'total_rows' => $total,
            'imported_rows' => $imported,
            'failed_rows' => $failed,
            'status' => $failed === 0 ? ImportLog::STATUS_COMPLETED : ($imported > 0 ? ImportLog::STATUS_PARTIAL : ImportLog::STATUS_FAILED),
            'errors' => empty($errors) ? null : array_slice($errors, 0, 50),
        ]);

        return [
            'total' => $total,
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function importVilla(array $data): void
    {
        if (empty($data['code']) || empty($data['name'])) {
            throw new \InvalidArgumentException('Villa code and name are required.');
        }

        $amenities = [];
        if (! empty($data['amenities'])) {
            if (str_starts_with($data['amenities'], '[')) {
                $amenities = json_decode($data['amenities'], true) ?? [];
            } else {
                $amenities = array_values(array_filter(array_map('trim', explode(',', $data['amenities']))));
            }
        }

        Villa::updateOrCreate(
            ['code' => $data['code']],
            [
                'name' => $data['name'],
                'slug' => Str::slug($data['name']).'-'.strtolower($data['code']),
                'category' => ! empty($data['category']) ? $data['category'] : 'Beachfront',
                'bedrooms' => max(1, (int) ($data['bedrooms'] ?? 1)),
                'bathrooms' => max(1, (int) ($data['bathrooms'] ?? 1)),
                'max_guests' => max(1, (int) ($data['max_guests'] ?? 2)),
                'area_sqm' => ! empty($data['area_sqm']) ? (int) $data['area_sqm'] : null,
                'price_per_night' => (float) ($data['price_per_night'] ?? 100),
                'weekend_price_per_night' => ! empty($data['weekend_price_per_night']) ? (float) $data['weekend_price_per_night'] : null,
                'cleaning_fee' => (float) ($data['cleaning_fee'] ?? 0),
                'security_deposit' => (float) ($data['security_deposit'] ?? 0),
                'status' => in_array($data['status'] ?? '', [Villa::STATUS_AVAILABLE, Villa::STATUS_OCCUPIED, Villa::STATUS_CLEANING, Villa::STATUS_MAINTENANCE, Villa::STATUS_RESERVED], true)
                    ? $data['status']
                    : Villa::STATUS_AVAILABLE,
                'location' => $data['location'] ?? 'Resort Grounds',
                'address' => $data['address'] ?? null,
                'description' => $data['description'] ?? null,
                'amenities' => $amenities,
                'is_featured' => filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function importGuest(array $data): void
    {
        if (empty($data['email']) || empty($data['first_name'])) {
            throw new \InvalidArgumentException('First name and valid email are required.');
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format: {$data['email']}");
        }

        Guest::updateOrCreate(
            ['email' => $data['email']],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'phone' => $data['phone'] ?? null,
                'nationality' => $data['nationality'] ?? null,
                'id_or_passport' => $data['id_or_passport'] ?? null,
                'vip_level' => in_array($data['vip_level'] ?? '', [Guest::VIP_STANDARD, Guest::VIP_VIP, Guest::VIP_VVIP], true)
                    ? $data['vip_level']
                    : Guest::VIP_STANDARD,
                'special_requests' => $data['special_requests'] ?? null,
            ]
        );
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function importBooking(array $data, ?User $user): void
    {
        if (empty($data['villa_code']) || empty($data['guest_email']) || empty($data['check_in']) || empty($data['check_out'])) {
            throw new \InvalidArgumentException('villa_code, guest_email, check_in, and check_out are required.');
        }

        $villa = Villa::where('code', $data['villa_code'])->first();
        if (! $villa) {
            throw new \InvalidArgumentException("Villa with code '{$data['villa_code']}' not found.");
        }

        $guest = Guest::where('email', $data['guest_email'])->first();
        if (! $guest) {
            // Auto create guest if name is provided or default
            $nameParts = ! empty($data['guest_name']) ? explode(' ', trim($data['guest_name']), 2) : ['Guest', ''];
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $guest = Guest::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $data['guest_email'],
            ]);
        }

        $checkIn = Carbon::parse($data['check_in'])->startOfDay();
        $checkOut = Carbon::parse($data['check_out'])->startOfDay();

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            throw new \InvalidArgumentException('check_out must be after check_in.');
        }

        $bookingCode = ! empty($data['booking_code'])
            ? $data['booking_code']
            : 'BK-'.strtoupper(Str::random(8));

        $existingBooking = Booking::where('booking_code', $bookingCode)->first();

        // Check for double booking conflicts
        $hasOverlap = Booking::overlapping(
            $villa->id,
            $checkIn->toDateString(),
            $checkOut->toDateString(),
            $existingBooking?->id
        )->exists();

        if ($hasOverlap) {
            throw new \InvalidArgumentException("Villa '{$villa->code}' is already booked or occupied between {$checkIn->toDateString()} and {$checkOut->toDateString()}.");
        }

        $nights = $checkIn->diffInDays($checkOut);
        $nightlyRate = ! empty($data['nightly_rate']) ? (float) $data['nightly_rate'] : (float) $villa->price_per_night;
        $cleaningFee = ! empty($data['cleaning_fee']) ? (float) $data['cleaning_fee'] : (float) $villa->cleaning_fee;
        $serviceFee = (float) ($data['service_fee'] ?? 0);
        $taxAmount = (float) ($data['tax_amount'] ?? 0);

        $totalAmount = ! empty($data['total_amount'])
            ? (float) $data['total_amount']
            : ($nights * $nightlyRate + $cleaningFee + $serviceFee + $taxAmount);

        $paidAmount = (float) ($data['paid_amount'] ?? 0);
        $balanceDue = max(0, $totalAmount - $paidAmount);

        $booking = Booking::updateOrCreate(
            ['booking_code' => $bookingCode],
            [
                'villa_id' => $villa->id,
                'guest_id' => $guest->id,
                'user_id' => $user?->id,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'adults' => max(1, (int) ($data['adults'] ?? 2)),
                'children' => max(0, (int) ($data['children'] ?? 0)),
                'total_nights' => $nights,
                'nightly_rate' => $nightlyRate,
                'cleaning_fee' => $cleaningFee,
                'service_fee' => $serviceFee,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
                'payment_status' => $balanceDue <= 0 ? Booking::PAYMENT_PAID : ($paidAmount > 0 ? Booking::PAYMENT_PARTIAL : Booking::PAYMENT_PENDING),
                'status' => in_array($data['status'] ?? '', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT, Booking::STATUS_CANCELLED], true)
                    ? $data['status']
                    : Booking::STATUS_CONFIRMED,
                'special_notes' => $data['special_notes'] ?? null,
            ]
        );

        if ($paidAmount > 0 && $booking->wasRecentlyCreated) {
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_reference' => 'TXN-'.strtoupper(Str::random(10)),
                'amount' => $paidAmount,
                'payment_method' => $data['payment_method'] ?? Payment::METHOD_CREDIT_CARD,
                'payment_date' => now(),
                'status' => Payment::STATUS_COMPLETED,
                'notes' => 'Imported opening balance payment',
            ]);
        }

        $guest->syncLifetimeMetrics();
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function importExpense(array $data): void
    {
        if (empty($data['title']) || empty($data['amount']) || empty($data['expense_date'])) {
            throw new \InvalidArgumentException('title, amount, and expense_date are required.');
        }

        $villaId = null;
        if (! empty($data['villa_code'])) {
            $villa = Villa::where('code', $data['villa_code'])->first();
            if (! $villa) {
                throw new \InvalidArgumentException("Villa with code '{$data['villa_code']}' not found.");
            }
            $villaId = $villa->id;
        }

        Expense::create([
            'villa_id' => $villaId,
            'title' => $data['title'],
            'category' => ! empty($data['category']) ? $data['category'] : Expense::CATEGORY_OTHER,
            'amount' => (float) $data['amount'],
            'expense_date' => Carbon::parse($data['expense_date'])->toDateString(),
            'vendor' => $data['vendor'] ?? null,
            'payment_method' => $data['payment_method'] ?? 'Bank Transfer',
            'invoice_number' => $data['invoice_number'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Generate sample CSV data for template downloads.
     */
    public function getSampleCsv(string $type): string
    {
        return match ($type) {
            ImportLog::TYPE_VILLAS => "code,name,category,bedrooms,bathrooms,max_guests,area_sqm,price_per_night,weekend_price_per_night,cleaning_fee,security_deposit,status,location,address,description,amenities,is_featured\n".
                "VIL-101,Villa Azure Horizons,Beachfront,4,5,8,450,1250.00,1450.00,120.00,500.00,available,North Shore Beach,101 Ocean Drive,Stunning luxury oceanfront villa with infinity pool and private chef kitchen,\"Private Pool, Ocean View, Wi-Fi, Chef Service, Butler, BBQ\",true\n".
                "VIL-102,Emerald Cliff Sanctuary,Cliffside,5,6,10,600,1800.00,2100.00,150.00,750.00,available,Sunset Cliffs,55 Panoramic Way,Dramatic cliffside estate with 180-degree sunset views and helipad access,\"Private Pool, Helipad, Jacuzzi, Cinema Room, Wine Cellar\",true\n".
                "VIL-103,Boutique Palm Villa,Garden Estate,3,3,6,320,850.00,950.00,90.00,300.00,available,Tropical Gardens,12 Palm Grove,Secluded tropical haven surrounded by landscaped palms and zen water features,\"Private Pool, Wi-Fi, Spa, Smart Home\",false\n",

            ImportLog::TYPE_GUESTS => "first_name,last_name,email,phone,nationality,id_or_passport,vip_level,special_requests\n".
                "Alexander,Sterling,sterling.alexander@luxurytravel.com,+1 (555) 234-5678,United States,US987654321,vvip,Prefers early check-in and sparkling mineral water on arrival\n".
                "Charlotte,Laurent,charlotte.laurent@parisien.fr,+33 6 12 34 56 78,France,FR554433221,vip,Gluten-free breakfast requested and organic bath products\n".
                "Kenji,Takahashi,takahashi.k@tokyo-holdings.jp,+81 90 8765 4321,Japan,JP123456789,standard,Quiet villa location preferred, king bed configuration\n",

            ImportLog::TYPE_BOOKINGS => "booking_code,villa_code,guest_email,guest_name,check_in,check_out,adults,children,nightly_rate,cleaning_fee,service_fee,tax_amount,total_amount,paid_amount,status,payment_method,special_notes\n".
                "BK-2026-101,VIL-101,sterling.alexander@luxurytravel.com,Alexander Sterling,2026-11-01,2026-11-06,4,2,1250.00,120.00,150.00,200.00,6720.00,6720.00,confirmed,credit_card,VIP airport luxury limousine pickup confirmed\n".
                "BK-2026-102,VIL-102,charlotte.laurent@parisien.fr,Charlotte Laurent,2026-11-10,2026-11-15,6,0,1800.00,150.00,200.00,300.00,9650.00,4825.00,confirmed,bank_transfer,Celebrating 10th wedding anniversary - champagne upon arrival\n",

            ImportLog::TYPE_EXPENSES => "title,category,villa_code,amount,expense_date,vendor,payment_method,invoice_number,notes\n".
                "Monthly Pool Maintenance & Chemical Balancing,maintenance,VIL-101,450.00,2026-10-01,Crystal Waters Pool Co,Bank Transfer,INV-8891,Regular weekly pool cleaning and filter maintenance\n".
                "High-Speed Fiber Resort Internet,utilities,,850.00,2026-10-05,Global Fiber Telecom,Direct Debit,INV-7732,Dedicated resort wide fiber optic line\n".
                "Luxury Egyptian Cotton Linens & Towels Restock,supplies,VIL-102,1250.00,2026-10-08,Artisan Linens Luxury,Corporate Card,INV-4412,800-thread count sheets and luxury bath robes\n",

            default => "col1,col2\nval1,val2\n",
        };
    }
}
