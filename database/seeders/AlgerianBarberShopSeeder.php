<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\User;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\BarberPayout;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AlgerianBarberShopSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Appointment::truncate();
        BarberPayout::truncate();
        Bill::truncate();
        Customer::truncate();
        Service::truncate();
        User::where('role', '!=', 'super_admin')->delete();
        Shop::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ensure roles exist
        if (!Role::where('name', 'owner')->exists()) Role::create(['name' => 'owner']);
        if (!Role::where('name', 'barber')->exists()) Role::create(['name' => 'barber']);

        // 1. Create the Elite Algerian Shop
        $shop = Shop::create([
            'name' => "The Royal Barber - Algiers",
            'slug' => 'the-royal-barber',
            'address' => '14 Rue Didouche Mourad, Alger Centre',
            'phone' => '+213 550 99 88 77',
            'subscription_status' => 'active',
        ]);

        // 2. The Owner
        $owner = User::create([
            'shop_id' => $shop->id,
            'name' => 'Karim Aloui',
            'email' => 'owner@royalbarber.dz',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $owner->assignRole('owner');

        // 3. The Barbers
        $barbersData = [
            ['name' => 'Sofiane Lalami', 'email' => 'sofiane@royalbarber.dz', 'comm' => 40],
            ['name' => 'Riad Mansouri', 'email' => 'riad@royalbarber.dz', 'comm' => 45],
            ['name' => 'Yacine Belkaid', 'email' => 'yacine@royalbarber.dz', 'comm' => 50],
            ['name' => 'Amine Kaci', 'email' => 'amine@royalbarber.dz', 'comm' => 35],
        ];

        $barbers = [];
        foreach ($barbersData as $data) {
            $barber = User::create([
                'shop_id' => $shop->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'barber',
                'commission_type' => 'percentage',
                'commission_value' => $data['comm'],
                'is_active' => true,
            ]);
            $barber->assignRole('barber');
            $barbers[] = $barber;
        }

        // 4. Premium Services
        $services = [
            ['name' => 'Royal Haircut', 'price' => 1200, 'duration' => 45],
            ['name' => 'Beard Sculpting (Hot Towel)', 'price' => 800, 'duration' => 30],
            ['name' => 'Executive Package (Hair + Beard)', 'price' => 1800, 'duration' => 75],
            ['name' => 'Deep Cleansing Facial', 'price' => 2500, 'duration' => 60],
            ['name' => 'Hair Coloring', 'price' => 3000, 'duration' => 90],
            ['name' => 'Kids Styling', 'price' => 800, 'duration' => 30],
            ['name' => 'Quick Trim (Sides only)', 'price' => 500, 'duration' => 20],
        ];

        $createdServices = [];
        foreach ($services as $s) {
            $createdServices[] = Service::create([
                'shop_id' => $shop->id,
                'name' => $s['name'],
                'price' => $s['price'],
                'duration_minutes' => $s['duration'],
                'is_active' => true,
            ]);
        }

        // 5. High-Value Customers
        $customerNames = [
            'Omar Bencherif', 'Mehdi Tabet', 'Samir Ould Abbas', 'Tarek Bouzar', 
            'Farid Meziani', 'Hicham Guediri', 'Malik Rahmani', 'Zinedine Charef',
            'Walid Merzougui', 'Nabil Chaouch', 'Adel Benalia', 'Kamal Derradji',
            'Mourad Sahraoui', 'Salim Hamidi', 'Faouzi Guendouz', 'Badis Saoudi',
            'Ilyes Khabet', 'Djalal Medane', 'Anis Ferhani', 'Ryad Kebir',
            'Tewfik Laribi', 'Chafik Gasmi', 'Rachid Messaoudi', 'Abdou Zenati'
        ];

        $customers = [];
        foreach ($customerNames as $name) {
            $customers[] = Customer::create([
                'shop_id' => $shop->id,
                'name' => $name,
                'phone' => '+213 77' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
            ]);
        }

        // 6. Realistic Appointment Generation (Past 30 days + Future week)
        $startDay = now()->subDays(30);
        $endDay = now()->addDays(7);
        
        $currentDate = clone $startDay;
        
        while ($currentDate <= $endDay) {
            if ($currentDate->isSunday()) { // Suppose shop is closed on Sundays
                $currentDate->addDay();
                continue;
            }

            foreach ($barbers as $barber) {
                // Tracking the next available time for this barber today
                // Business hours: 09:00 to 20:00
                $nextSlot = (clone $currentDate)->setHour(9)->setMinute(0)->setSecond(0);
                $closingTime = (clone $currentDate)->setHour(20)->setMinute(0)->setSecond(0);

                // Barbers are busy but they have gaps
                while ($nextSlot < $closingTime) {
                    // Random probability of an appointment (e.g. 70% chance of a booking)
                    if (rand(1, 100) > 30) {
                        $service = $createdServices[array_rand($createdServices)];
                        $customer = $customers[array_rand($customers)];
                        
                        $duration = $service->duration_minutes;
                        $endTime = (clone $nextSlot)->addMinutes($duration);

                        if ($endTime > $closingTime) break;

                        $isPast = $nextSlot->isPast();
                        
                        // Status Logic
                        if ($isPast) {
                            $roll = rand(1, 100);
                            if ($roll > 95) $status = 'cancelled';
                            else $status = 'completed';
                        } else {
                            $status = 'scheduled';
                        }

                        $price = $service->price;
                        $commission = round(($price * $barber->commission_value) / 100);

                        $appt = Appointment::create([
                            'shop_id' => $shop->id,
                            'barber_id' => $barber->id,
                            'customer_id' => $customer->id,
                            'start_time' => $nextSlot,
                            'end_time' => $endTime,
                            'status' => $status,
                            'total_price' => $price,
                            'commission_amount' => $status === 'completed' ? $commission : 0,
                            'payment_status' => $status === 'completed' ? (rand(1, 10) > 1 ? 'paid' : 'unpaid') : 'unpaid',
                            'created_at' => (clone $nextSlot)->subDays(rand(1, 5)),
                        ]);

                        $appt->services()->attach($service->id, ['price_at_time' => $price]);

                        // Advance to next slot + possible cleanup time (5-15 mins)
                        $nextSlot = (clone $endTime)->addMinutes(rand(5, 15));
                    } else {
                        // Just an empty gap of 30-60 mins
                        $nextSlot->addMinutes(rand(30, 60));
                    }
                }
            }
            $currentDate->addDay();
        }

        // 7. Payouts (Matching actual earnings from the past months)
        foreach ($barbers as $barber) {
            // January Payout
            BarberPayout::create([
                'shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'amount' => rand(45000, 65000),
                'date' => Carbon::create(2026, 1, 31),
                'note' => 'Full commission payout for January 2026',
            ]);

            // Mid-February Advance
            BarberPayout::create([
                'shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'amount' => 20000,
                'date' => now()->format('Y-m-d'),
                'note' => 'Mid-month commission advance',
            ]);
        }

        // 8. Recurring Bills
        $monthlyBills = [
            ['type' => 'Rent', 'amount' => 120000, 'note' => 'Monthly Rent - Algiers Center'],
            ['type' => 'Utilities', 'amount' => 15000, 'note' => 'Electricity & Water'],
            ['type' => 'Internet', 'amount' => 4500, 'note' => 'Fiber Optic High Speed'],
            ['type' => 'Supplies', 'amount' => 25000, 'note' => 'Hair Products & Disinfectants'],
            ['type' => 'Marketing', 'amount' => 10000, 'note' => 'Instagram Ads'],
        ];

        foreach ($monthlyBills as $bill) {
            Bill::create([
                'shop_id' => $shop->id,
                'amount' => $bill['amount'],
                'date' => Carbon::create(2026, 2, 1),
                'type' => $bill['type'],
                'note' => $bill['note'],
            ]);
        }
    }
}
