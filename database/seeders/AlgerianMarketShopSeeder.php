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
use App\Models\CashDrawer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AlgerianMarketShopSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the Algerian Market Shop
        $shop = Shop::create([
            'name' => "Boutique El Bahdja - Oran",
            'slug' => 'boutique-el-bahdja',
            'address' => 'Avenue de l\'ALN, Front de Mer, Oran, Algérie',
            'phone' => '+213 41 22 33 44',
            'subscription_status' => 'active',
        ]);

        // 2. The Owner
        $owner = User::create([
            'shop_id' => $shop->id,
            'name' => 'Mustafa Benali',
            'email' => 'mustafa@elbahdja.dz',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $owner->assignRole('owner');

        // 3. The Barbers
        $barbersData = [
            ['name' => 'Ahmed Mansour', 'email' => 'ahmed@elbahdja.dz', 'comm' => 35],
            ['name' => 'Khaled Ziri', 'email' => 'khaled@elbahdja.dz', 'comm' => 40],
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

        // 4. Services (Coupe simple, Coupe enfant, Brushing)
        $services = [
            ['name' => 'Coupe Simple', 'price' => 600, 'duration' => 30],
            ['name' => 'Coupe Enfant', 'price' => 400, 'duration' => 20],
            ['name' => 'Brushing & Finition', 'price' => 800, 'duration' => 25],
            ['name' => 'Barbe Traditionnelle', 'price' => 300, 'duration' => 15],
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

        // 5. Customers
        $customerNames = [
            'Rachid Bouras', 'Sidali Hamdan', 'Yacine Taleb', 'Fouad Belkaid',
            'Mourad Drif', 'Tarek Gaci', 'Abdou Zenir', 'Samir Khelil'
        ];

        $customers = [];
        foreach ($customerNames as $name) {
            $customers[] = Customer::create([
                'shop_id' => $shop->id,
                'name' => $name,
                'phone' => '+213 5 ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
            ]);
        }

        // 6. Appointments for March 2026
        $startDay = Carbon::create(2026, 3, 1);
        $endDay = Carbon::create(2026, 3, 9);

        $currentDate = clone $startDay;
        
        while ($currentDate <= $endDay) {
            if ($currentDate->isFriday()) { // Closed on Fridays in Algeria usually
                $currentDate->addDay();
                continue;
            }

            foreach ($barbers as $barber) {
                $nextSlot = (clone $currentDate)->setHour(9)->setMinute(30)->setSecond(0);
                $closingTime = (clone $currentDate)->setHour(21)->setMinute(0)->setSecond(0);

                while ($nextSlot < $closingTime) {
                    if (rand(1, 100) > 30) { // 70% occupancy
                        $service = $createdServices[array_rand($createdServices)];
                        $customer = $customers[array_rand($customers)];
                        
                        $endTime = (clone $nextSlot)->addMinutes($service->duration_minutes);
                        if ($endTime > $closingTime) break;

                        $price = $service->price;
                        $commission = round(($price * $barber->commission_value) / 100);

                        $appt = Appointment::create([
                            'shop_id' => $shop->id,
                            'barber_id' => $barber->id,
                            'customer_id' => $customer->id,
                            'start_time' => $nextSlot,
                            'end_time' => $endTime,
                            'status' => 'completed',
                            'total_price' => $price,
                            'commission_amount' => $commission,
                            'payment_status' => 'paid',
                        ]);

                        $appt->services()->attach($service->id, ['price_at_time' => $price]);
                        $nextSlot = (clone $endTime)->addMinutes(rand(5, 10));
                    } else {
                        $nextSlot->addMinutes(30);
                    }
                }
            }
            $currentDate->addDay();
        }

        // 7. Bills
        $bills = [
            ['type' => 'Kira (Loyer)', 'amount' => 65000, 'date' => '2026-03-01'],
            ['type' => 'Internet & Phone', 'amount' => 4500, 'date' => '2026-03-02'],
        ];

        foreach ($bills as $b) {
            Bill::create([
                'shop_id' => $shop->id,
                'amount' => $b['amount'],
                'date' => $b['date'],
                'type' => $b['type'],
                'note' => 'Charge fixe mensuelle - Oran',
            ]);
        }

        // 8. Cash Drawer
        $currentDate = clone $startDay;
        while ($currentDate <= $endDay) {
            if ($currentDate->isFriday()) {
                $currentDate->addDay();
                continue;
            }

            CashDrawer::create([
                'shop_id' => $shop->id,
                'date' => $currentDate->toDateString(),
                'starting_cash' => 5000,
                'closed_at' => (clone $currentDate)->setHour(21)->setMinute(15),
                'closing_cash' => 5000 + rand(8000, 15000),
                'notes' => 'Hamdoulilah, journée chargée.',
            ]);
            $currentDate->addDay();
        }
    }
}
