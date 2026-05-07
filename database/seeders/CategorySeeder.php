<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $userId = $user ? $user->id : null;

        $categories = [
            // Income
            ['name' => 'Gaji', 'type' => 'income', 'icon' => 'wallet', 'color' => '#10b981'],
            ['name' => 'Bonus', 'type' => 'income', 'icon' => 'gift', 'color' => '#34d399'],
            ['name' => 'Investasi', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#059669'],
            ['name' => 'Lain-lain', 'type' => 'income', 'icon' => 'plus-circle', 'color' => '#6b7280'],

            // Expense - General
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'car', 'color' => '#3b82f6'],
            ['name' => 'Tagihan & Utilitas', 'type' => 'expense', 'icon' => 'zap', 'color' => '#f59e0b'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'book', 'color' => '#6366f1'],
            ['name' => 'Hiburan', 'type' => 'expense', 'icon' => 'film', 'color' => '#8b5cf6'],

            // Expense - Supermarket Standard Categories
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#ef4444'],
            ['name' => 'Kesehatan & Kecantikan', 'type' => 'expense', 'icon' => 'heart', 'color' => '#f43f5e'],
            ['name' => 'Kebersihan & Rumah Tangga', 'type' => 'expense', 'icon' => 'home', 'color' => '#d97706'],
            ['name' => 'Bayi & Anak-anak', 'type' => 'expense', 'icon' => 'baby', 'color' => '#ec4899'],
            ['name' => 'Hewan Peliharaan', 'type' => 'expense', 'icon' => 'dog', 'color' => '#8b5cf6'],
            ['name' => 'Elektronik & Elektrik', 'type' => 'expense', 'icon' => 'cpu', 'color' => '#0ea5e9'],
            ['name' => 'Olahraga & Outdoor', 'type' => 'expense', 'icon' => 'mountain', 'color' => '#10b981'],
            ['name' => 'Pakaian & Mode', 'type' => 'expense', 'icon' => 'shirt', 'color' => '#f472b6'],
            ['name' => 'Alat Tulis Kantor', 'type' => 'expense', 'icon' => 'pen', 'color' => '#64748b'],
            ['name' => 'Teknologi & Gadget', 'type' => 'expense', 'icon' => 'smartphone', 'color' => '#4f46e5'],
            ['name' => 'Otomotif', 'type' => 'expense', 'icon' => 'wrench', 'color' => '#475569'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name'], 'user_id' => $userId],
                [
                    'slug' => Str::slug($cat['name']),
                    'type' => $cat['type'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_system' => true,
                ]
            );
        }
    }
}
