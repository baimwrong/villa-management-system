<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $villa1 = Villa::where('code', 'VIL-001')->first();
        $villa6 = Villa::where('code', 'VIL-006')->first();

        $now = Carbon::now();

        $expenses = [
            [
                'villa_id' => $villa1?->id,
                'title' => 'Oceanfront Saltwater Pool Desalination & Chemical Balancing',
                'category' => Expense::CATEGORY_MAINTENANCE,
                'amount' => 450.00,
                'expense_date' => $now->copy()->subDays(5)->toDateString(),
                'vendor' => 'PureBlue Aquatic Services',
                'payment_method' => 'Bank Transfer',
                'invoice_number' => 'PB-9021',
                'notes' => 'Weekly pool maintenance and automated salinity sensor calibration.',
            ],
            [
                'villa_id' => null,
                'title' => 'Resort Dedicated Dedicated 10Gbps Fiber Backbone',
                'category' => Expense::CATEGORY_UTILITIES,
                'amount' => 1200.00,
                'expense_date' => $now->copy()->subDays(12)->toDateString(),
                'vendor' => 'Aura Global Telecom',
                'payment_method' => 'Direct Debit',
                'invoice_number' => 'AGT-88291',
                'notes' => 'Monthly dedicated optical fiber connection for entire resort estate.',
            ],
            [
                'villa_id' => $villa6?->id,
                'title' => 'Caviar & Grand Cru Wine Restock for Royal Pavilion',
                'category' => Expense::CATEGORY_FOOD_BEVERAGE,
                'amount' => 2800.00,
                'expense_date' => $now->copy()->subDays(4)->toDateString(),
                'vendor' => 'Gourmet Imports & Cellars',
                'payment_method' => 'Credit Card',
                'invoice_number' => 'GIC-1102',
                'notes' => 'Requested by VVIP guest concierge.',
            ],
            [
                'villa_id' => null,
                'title' => 'Monthly Landscaping & Botanical Garden Grounds Keeping',
                'category' => Expense::CATEGORY_MAINTENANCE,
                'amount' => 1850.00,
                'expense_date' => $now->copy()->subDays(8)->toDateString(),
                'vendor' => 'Verdant Paradise Landscaping',
                'payment_method' => 'Bank Transfer',
                'invoice_number' => 'VPL-4432',
                'notes' => 'Pruning palms, orchid nursery maintenance, and pathway lighting checks.',
            ],
        ];

        foreach ($expenses as $expenseData) {
            Expense::create($expenseData);
        }
    }
}
