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

class FrenchShopSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the French Shop
        $shop = Shop::create([
            'name' => "Le Salon de Paris",
            'slug' => 'le-salon-de-paris',
            'address' => '45 Avenue des Champs-Élysées, 75008 Paris, France',
            'phone' => '+33 1 42 25 12 34',
            'subscription_status' => 'active',
        ]);

        // 2. The Owner
        $owner = User::create([
            'shop_id' => $shop->id,
            'name' => 'Jean-Pierre Laurent',
            'email' => 'jean.pierre@lesalon.fr',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $owner->assignRole('owner');

        // 3. The Barbers (3 barbers as requested)
        $barbersData = [
            ['name' => 'Lucas Dubois', 'email' => 'lucas@lesalon.fr', 'comm' => 45],
            ['name' => 'Thomas Moreau', 'email' => 'thomas@lesalon.fr', 'comm' => 50],
            ['name' => 'Julien Bernard', 'email' => 'julien@lesalon.fr', 'comm' => 40],
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

        // 4. Services (Coupe simple, Barbe, Nettoyage de peau)
        $services = [
            ['name' => 'Coupe Simple', 'price' => 25, 'duration' => 30],
            ['name' => 'Barbe (Traçage & Soin)', 'price' => 15, 'duration' => 20],
            ['name' => 'Nettoyage de Peau Premium', 'price' => 45, 'duration' => 45],
            ['name' => 'Forfait Complet (Coupe + Barbe)', 'price' => 35, 'duration' => 45],
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
            'Pierre Lefebvre', 'Michel Garcia', 'Nicolas Petit', 'Christophe Roux',
            'Frédéric Simon', 'Benoît Durand', 'Guillaume Leroy', 'Jérôme Moreau',
            'Sébastien Laurent', 'Antoine Lefevre', 'Vincent Mercier', 'Maxime Bertrand'
        ];

        $customers = [];
        foreach ($customerNames as $name) {
            $customers[] = Customer::create([
                'shop_id' => $shop->id,
                'name' => $name,
                'phone' => '+33 6 ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
            ]);
        }

        // 6. Appointments for March 2026
        $startDay = Carbon::create(2026, 3, 1);
        $endDay = Carbon::create(2026, 3, 9); // Current day

        $currentDate = clone $startDay;
        
        while ($currentDate <= $endDay) {
            if ($currentDate->isSunday()) {
                $currentDate->addDay();
                continue;
            }

            foreach ($barbers as $barber) {
                $nextSlot = (clone $currentDate)->setHour(10)->setMinute(0)->setSecond(0);
                $closingTime = (clone $currentDate)->setHour(19)->setMinute(0)->setSecond(0);

                while ($nextSlot < $closingTime) {
                    if (rand(1, 100) > 40) { // 60% occupancy
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

        // 7. Payouts for March (Early March Advance)
        foreach ($barbers as $barber) {
            BarberPayout::create([
                'shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'amount' => 150,
                'date' => Carbon::create(2026, 3, 5),
                'note' => 'Avance sur commission Mars 2026',
            ]);
        }

        // 8. Monthly Bills
        $bills = [
            ['type' => 'Loyer', 'amount' => 2500, 'date' => '2026-03-01'],
            ['type' => 'Électricité', 'amount' => 180, 'date' => '2026-03-05'],
            ['type' => 'Produits', 'amount' => 450, 'date' => '2026-03-07'],
        ];

        foreach ($bills as $b) {
            Bill::create([
                'shop_id' => $shop->id,
                'amount' => $b['amount'],
                'date' => $b['date'],
                'type' => $b['type'],
                'note' => 'Dépense de fonctionnement - Paris',
            ]);
        }

        // 9. Cash Drawer for March
        $currentDate = clone $startDay;
        while ($currentDate <= $endDay) {
            if ($currentDate->isSunday()) {
                $currentDate->addDay();
                continue;
            }

            CashDrawer::create([
                'shop_id' => $shop->id,
                'date' => $currentDate->toDateString(),
                'starting_cash' => 200,
                'closed_at' => (clone $currentDate)->setHour(19)->setMinute(30),
                'closing_cash' => 200 + rand(300, 600),
                'notes' => 'Journée clôturée sans écart.',
            ]);
            $currentDate->addDay();
        }
    }
}
