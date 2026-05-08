<?php

namespace App\Services\Finance;

use App\Models\User;
use App\Models\Achievement;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    public function checkAchievements(User $user)
    {
        $achievements = Achievement::all();
        $unlockedCount = 0;

        foreach ($achievements as $achievement) {
            // Skip if already unlocked
            if ($user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                continue;
            }

            $criteria = $achievement->criteria;
            $isEligible = false;

            switch ($criteria['type']) {
                case 'transaction_count':
                    $count = $user->transactions()->count();
                    $isEligible = $count >= $criteria['value'];
                    break;

                case 'saving_streak':
                    // Logic for logging in 7 days in a row or similar
                    // For now, let's keep it simple
                    break;
            }

            if ($isEligible) {
                $user->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
                $unlockedCount++;
                Log::info("User {$user->email} unlocked achievement: {$achievement->name}");
            }
        }

        return $unlockedCount;
    }

    public function seedDefaultAchievements()
    {
        $defaults = [
            [
                'name' => 'First Steps',
                'slug' => 'first-steps',
                'description' => 'Mencatat transaksi pertama kamu!',
                'icon' => '🌱',
                'color' => '#10b981',
                'criteria' => ['type' => 'transaction_count', 'value' => 1]
            ],
            [
                'name' => 'FinFlow Master',
                'slug' => 'finflow-master',
                'description' => 'Mencatat lebih dari 50 transaksi.',
                'icon' => '👑',
                'color' => '#6366f1',
                'criteria' => ['type' => 'transaction_count', 'value' => 50]
            ],
            [
                'name' => 'Money Tracker',
                'slug' => 'money-tracker',
                'description' => 'Mencatat 10 transaksi pertama.',
                'icon' => '📊',
                'color' => '#f59e0b',
                'criteria' => ['type' => 'transaction_count', 'value' => 10]
            ]
        ];

        foreach ($defaults as $data) {
            Achievement::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
