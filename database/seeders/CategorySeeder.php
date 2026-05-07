<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Income
            ['name' => 'Gaji', 'type' => 'income', 'icon' => 'wallet', 'color' => '#10b981'],
            ['name' => 'Bonus', 'type' => 'income', 'icon' => 'gift', 'color' => '#34d399'],
            ['name' => 'Investasi', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#059669'],
            ['name' => 'Lain-lain', 'type' => 'income', 'icon' => 'plus-circle', 'color' => '#6b7280'],

            // Expense
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#ef4444'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'car', 'color' => '#3b82f6'],
            ['name' => 'Belanja', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#ec4899'],
            ['name' => 'Hiburan', 'type' => 'expense', 'icon' => 'film', 'color' => '#8b5cf6'],
            ['name' => 'Tagihan & Utilitas', 'type' => 'expense', 'icon' => 'zap', 'color' => '#f59e0b'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'heart', 'color' => '#f43f5e'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'book', 'color' => '#6366f1'],
            ['name' => 'Tempat Tinggal', 'type' => 'expense', 'icon' => 'home', 'color' => '#d97706'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'type' => $cat['type'],
                'icon' => $cat['icon'],
                'color' => $cat['color'],
                'is_system' => true,
            ]);
        }
    }
}
