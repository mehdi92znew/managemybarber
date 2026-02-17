<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop;
use App\Models\User;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Appointment;
use App\Models\Subscription;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data to avoid duplicates (except roles which are handled by RolePermissionSeeder)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Appointment::truncate();
        Bill::truncate();
        Customer::truncate();
        Service::truncate();
        User::where('role', '!=', 'super_admin')->delete();
        Shop::truncate();
        Subscription::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create the Main Shop (Algerian Theme)
        $shop = Shop::create([
            'name' => "L'Art du Ciseau - Alger",
            'slug' => 'art-du-ciseau-alger',
            'address' => '05 Rue Didouche Mourad, Alger Centre',
            'phone' => '+213 21 00 11 22',
            'subscription_status' => 'active',
            'subscription_ends_at' => Carbon::create(2026, 12, 31),
        ]);

        Subscription::create([
            'shop_id' => $shop->id,
            'plan_name' => 'Premium Platinum',
            'price' => 5000.00, // DZD
            'starts_at' => Carbon::create(2026, 1, 1),
            'ends_at' => Carbon::create(2026, 12, 31),
            'status' => 'active',
            'payment_provider_id' => 'ALGERIA_POSTE_' . Str::random(8),
        ]);

        // 2. Create Shop Owner
        $owner = User::create([
            'shop_id' => $shop->id,
            'name' => 'Mohamed Mansouri',
            'email' => 'owner@barber.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $owner->assignRole('owner');

        // 3. Create Barbers (Algerian Names)
        $barberNames = [
            ['name' => 'Sid Ali Brahimi', 'email' => 'sidali@barber.com', 'comm' => 40],
            ['name' => 'Karim Benali', 'email' => 'karim@barber.com', 'comm' => 50],
            ['name' => 'Yacine Hammadi', 'email' => 'barber@barber.com', 'comm' => 45],
            ['name' => 'Mourad Ziane', 'email' => 'mourad@barber.com', 'comm' => 35],
        ];

        $barbers = [];
        foreach ($barberNames as $b) {
            $user = User::create([
                'shop_id' => $shop->id,
                'name' => $b['name'],
                'email' => $b['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'barber',
                'commission_type' => 'percentage',
                'commission_value' => $b['comm'],
                'is_active' => true,
            ]);
            $user->assignRole('barber');
            $barbers[] = $user;
        }

        // 4. Create Services (Algerian Context)
        $services = [
            ['name' => 'Coupe Classique', 'price' => 800, 'duration' => 30, 'extra' => false],
            ['name' => 'Coupe + Barbe', 'price' => 1200, 'duration' => 45, 'extra' => false],
            ['name' => 'Taille de Barbe Sculptée', 'price' => 500, 'duration' => 20, 'extra' => true],
            ['name' => 'Soin Visage (Black Mask)', 'price' => 1000, 'duration' => 30, 'extra' => true],
            ['name' => 'Décoloration / Teinture', 'price' => 2500, 'duration' => 60, 'extra' => false],
            ['name' => 'Coupe Enfant', 'price' => 600, 'duration' => 25, 'extra' => false],
        ];

        $createdServices = [];
        foreach ($services as $s) {
            $createdServices[] = Service::create([
                'shop_id' => $shop->id,
                'name' => $s['name'],
                'price' => $s['price'],
                'duration_minutes' => $s['duration'],
                'is_extra' => $s['extra'],
                'is_active' => true,
            ]);
        }

        // 5. Create Customers (Algerian Famous/Common Names)
        $customerNames = [
            'Riad Mahrez', 'Youcef Belaïli', 'Islam Slimani', 'Sofiane Feghouli', 
            'Aïssa Mandi', 'Ramy Bensebaïni', 'Baghdad Bounedjah', 'Ismaël Bennacer',
            'Nabil Bentaleb', 'Raïs M\'Bolhi', 'Amine Kaci', 'Mustapha Belkaid',
            'Omar Hammadi', 'Fouad Ziane', 'Slimane Brahimi', 'Abdelkader Ghezzal',
            'Hassen Yebda', 'Madjid Bougherra', 'Antar Yahia', 'Karim Ziani'
        ];

        $customers = [];
        foreach ($customerNames as $name) {
            $customers[] = Customer::create([
                'shop_id' => $shop->id,
                'name' => $name,
                'phone' => '+213 55' . rand(1000000, 9999999),
                'last_visit_at' => Carbon::create(2026, 2, rand(1, 14)),
            ]);
        }

        // 6. Create Historical Appointments (January 2026 - Trend visualization)
        for ($i = 0; $i < 40; $i++) {
            $day = rand(1, 31);
            $barber = $barbers[array_rand($barbers)];
            $customer = $customers[array_rand($customers)];
            $service = $createdServices[array_rand($createdServices)];
            
            $start = Carbon::create(2026, 1, $day, rand(9, 19), 0, 0);
            $price = $service->price;
            $commValue = $barber->commission_value;
            $commAmount = ($price * $commValue) / 100;

            $appt = Appointment::create([
                'shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'customer_id' => $customer->id,
                'start_time' => $start,
                'end_time' => (clone $start)->addMinutes($service->duration_minutes),
                'status' => 'completed',
                'total_price' => $price,
                'commission_amount' => $commAmount,
                'payment_status' => 'paid',
            ]);
            $appt->services()->attach($service->id, ['price_at_time' => $price]);
        }

        // 7. Create Recent Appointments (Feb 1 - Feb 14)
        for ($i = 0; $i < 50; $i++) {
            $day = rand(1, 14);
            $barber = $barbers[array_rand($barbers)];
            $customer = $customers[array_rand($customers)];
            $service = $createdServices[array_rand($createdServices)];
            
            $start = Carbon::create(2026, 2, $day, rand(9, 19), 0, 0);
            $price = $service->price;
            $commAmount = ($price * $barber->commission_value) / 100;

            $appt = Appointment::create([
                'shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'customer_id' => $customer->id,
                'start_time' => $start,
                'end_time' => (clone $start)->addMinutes($service->duration_minutes),
                'status' => 'completed',
                'total_price' => $price,
                'commission_amount' => $commAmount,
                'payment_status' => 'paid',
            ]);
            $appt->services()->attach($service->id, ['price_at_time' => $price]);
        }

        // 8. Create Today's & Future Appointments (Feb 15 - Feb 28)
        // Today (Feb 15)
        for ($i = 0; $i < 10; $i++) {
            $barber = $barbers[array_rand($barbers)];
            $customer = $customers[array_rand($customers)];
            $service = $createdServices[array_rand($createdServices)];
            
            $hour = rand(9, 19);
            $status = $hour < 15 ? 'completed' : 'scheduled'; // Assuming current time is around 15:00
            
            $start = Carbon::create(2026, 2, 15, $hour, 0, 0);
            $compAmount = ($service->price * $barber->commission_value) / 100;

            $appt = Appointment::create([
                'shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'customer_id' => $customer->id,
                'start_time' => $start,
                'end_time' => (clone $start)->addMinutes($service->duration_minutes),
                'status' => $status,
                'total_price' => $service->price,
                'commission_amount' => $status === 'completed' ? $compAmount : 0,
                'payment_status' => $status === 'completed' ? 'paid' : 'pending',
            ]);
            $appt->services()->attach($service->id, ['price_at_time' => $service->price]);
        }

        // Future (Next 2 weeks)
        for ($i = 0; $i < 20; $i++) {
            $day = rand(16, 28);
            $barber = $barbers[array_rand($barbers)];
            $customer = $customers[array_rand($customers)];
            $service = $createdServices[array_rand($createdServices)];
            
            $start = Carbon::create(2026, 2, $day, rand(9, 19), 0, 0);

            $appt = Appointment::create([
                'shop_id' => $shop->id,
                'barber_id' => $barber->id,
                'customer_id' => $customer->id,
                'start_time' => $start,
                'end_time' => (clone $start)->addMinutes($service->duration_minutes),
                'status' => 'scheduled',
                'total_price' => $service->price,
                'commission_amount' => 0,
                'payment_status' => 'pending',
            ]);
            $appt->services()->attach($service->id, ['price_at_time' => $service->price]);
        }

        // 9. Create Bills (Charges) for February
        $billTypes = ['Loyer', 'Électricité', 'Eau', 'Produits Coiffure', 'Internet', 'Marketing'];
        foreach ($billTypes as $type) {
            Bill::create([
                'shop_id' => $shop->id,
                'amount' => rand(2000, 15000),
                'date' => Carbon::create(2026, 2, rand(1, 15)),
                'type' => $type,
                'note' => 'Paiement du mois de Février 2026',
            ]);
        }
        
        // Also some bills for January
        foreach ($billTypes as $type) {
            Bill::create([
                'shop_id' => $shop->id,
                'amount' => rand(2000, 15000),
                'date' => Carbon::create(2026, 1, rand(1, 31)),
                'type' => $type,
                'note' => 'Paiement du mois de Janvier 2026',
            ]);
        }
    }
}
