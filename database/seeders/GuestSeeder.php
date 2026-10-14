<?php

namespace Database\Seeders;

use App\Models\Guest;
use Illuminate\Database\Seeder;

class GuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guests = [
            [
                'first_name' => 'Sophia',
                'last_name' => 'Montgomery',
                'email' => 'sophia.montgomery@montecarloconsulting.com',
                'phone' => '+44 20 7946 0912',
                'nationality' => 'United Kingdom',
                'id_or_passport' => 'GB9920148',
                'vip_level' => Guest::VIP_VVIP,
                'special_requests' => 'Requires organic oat milk, hypoallergenic goose down pillows, and private yacht charter on day 3.',
                'total_stays' => 4,
                'total_spent' => 28500.00,
            ],
            [
                'first_name' => 'Hiroshi',
                'last_name' => 'Tanaka',
                'email' => 'hiroshi.tanaka@tokyo-ventures.jp',
                'phone' => '+81 3 5555 0143',
                'nationality' => 'Japan',
                'id_or_passport' => 'JP4482910',
                'vip_level' => Guest::VIP_VIP,
                'special_requests' => 'Prefers daily Japanese breakfast and high-speed fiber internet for board meetings.',
                'total_stays' => 2,
                'total_spent' => 14200.00,
            ],
            [
                'first_name' => 'Claire',
                'last_name' => 'Dubois',
                'email' => 'claire.dubois@haute-joaillerie.fr',
                'phone' => '+33 1 42 68 55 00',
                'nationality' => 'France',
                'id_or_passport' => 'FR8819203',
                'vip_level' => Guest::VIP_VIP,
                'special_requests' => 'Champagne upon arrival (Dom Pérignon preferred), late checkout requested.',
                'total_stays' => 3,
                'total_spent' => 19800.00,
            ],
            [
                'first_name' => 'Lucas',
                'last_name' => 'Silva',
                'email' => 'lucas.silva@rio-capital.br',
                'phone' => '+55 21 98765 4321',
                'nationality' => 'Brazil',
                'id_or_passport' => 'BR1199283',
                'vip_level' => Guest::VIP_STANDARD,
                'special_requests' => 'Surfing guide and paddle board gear prepared at villa waterfront.',
                'total_stays' => 1,
                'total_spent' => 6900.00,
            ],
            [
                'first_name' => 'Amira',
                'last_name' => 'Al-Maktoum',
                'email' => 'amira.almaktoum@dubai-estates.ae',
                'phone' => '+971 4 300 1234',
                'nationality' => 'United Arab Emirates',
                'id_or_passport' => 'AE7740291',
                'vip_level' => Guest::VIP_VVIP,
                'special_requests' => 'Strict halal menu by private in-villa chef, complete privacy screening around pool.',
                'total_stays' => 5,
                'total_spent' => 45000.00,
            ],
        ];

        foreach ($guests as $guestData) {
            Guest::updateOrCreate(['email' => $guestData['email']], $guestData);
        }
    }
}
