<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();
        
        if (!$user) {
            $user = \App\Models\User::factory()->create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
            ]);
        }

        // Create 15 Billion Account
        \App\Models\Account::updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Bank BCA'],
            [
                'type' => 'bank',
                'balance' => 15000000000, // 15 Milyar
                'initial_balance' => 15000000000,
                'currency' => 'IDR',
                'color' => '#3b82f6',
                'icon' => 'bank',
            ]
        );

        // Create Categories
        $categories = ['Belanja', 'Makanan & Minuman', 'Transportasi', 'Tagihan', 'Hiburan'];
        
        foreach ($categories as $cat) {
            $createdCategories[] = \App\Models\Category::firstOrCreate([
                'user_id' => $user->id,
                'name' => $cat,
            ], [
                'slug' => \Illuminate\Support\Str::slug($cat),
                'type' => 'expense',
                'color' => '#' . substr(md5($cat), 0, 6),
                'icon' => 'tag',
            ]);
        }

        // Create Fake Transactions over the last 30 days
        $account = \App\Models\Account::where('user_id', $user->id)->first();
        if ($account && \App\Models\Transaction::where('user_id', $user->id)->count() < 10) {
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays(rand(0, 30))->subHours(rand(1, 23));
                $cat = $createdCategories[array_rand($createdCategories)];
                $amount = rand(10000, 500000);
                
                \App\Models\Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'category_id' => $cat->id,
                    'type' => 'expense',
                    'amount' => $amount,
                    'base_amount' => $amount,
                    'currency' => 'IDR',
                    'description' => 'Pembelian ' . $cat->name,
                    'date' => $date,
                    'transacted_at' => $date,
                ]);

                // Update balance
                $account->decrement('balance', $amount);
            }

            // Create some potential subscriptions (recurring patterns)
            $subData = [
                ['name' => 'Netflix', 'amount' => 186000, 'cat' => 'Hiburan'],
                ['name' => 'Spotify', 'amount' => 54990, 'cat' => 'Hiburan'],
                ['name' => 'Indihome', 'amount' => 450000, 'cat' => 'Tagihan'],
            ];

            foreach ($subData as $sub) {
                $category = \App\Models\Category::where('name', $sub['cat'])->first();
                // Create 3 occurrences for each
                for ($m = 0; $m < 3; $m++) {
                    $date = now()->subMonths($m)->setDay(15);
                    \App\Models\Transaction::create([
                        'user_id' => $user->id,
                        'account_id' => $account->id,
                        'category_id' => $category->id,
                        'type' => 'expense',
                        'amount' => $sub['amount'],
                        'base_amount' => $sub['amount'],
                        'currency' => 'IDR',
                        'merchant' => $sub['name'],
                        'description' => 'Pembayaran ' . $sub['name'],
                        'date' => $date,
                        'transacted_at' => $date,
                    ]);
                    $account->decrement('balance', $sub['amount']);
                }
            }
        }
    }
}
