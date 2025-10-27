<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Habit;
use App\Models\HabitPeriod;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisateur de démo
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'), // login de démo
            'theme' => 'valentine',
        ]);

        // Set fixe d'habitudes pour la démo
        $defs = [
            ['七万歩数', 'good_habit'],
            ['水の2ℓを飲む', 'good_habit'],
            ['10分読む', 'good_habit'],
            ['懸垂を10回やる', 'good_habit'],
            ['タバコを止める', 'bad_habit'],
        ];

        foreach ($defs as [$name, $type]) {
            $data = [
                'user_id'   => $user->id,
                'name'      => $name,
                'type'      => $type,
                'is_active' => true,
            ];

            if ($name === 'タバコを止める') {
                $data['amount_per_day'] = 600;
            }

            $habit = Habit::create($data);

            // 1 à 2 périodes fermées passées
            $closedCount = rand(1, 2);
            $cursor = now()->clone()->subDays(120);

            for ($i = 0; $i < $closedCount; $i++) {
                $start = $cursor->clone()->addDays(rand(1, 10));
                $end   = $start->clone()->addDays(rand(5, 25));
                HabitPeriod::create([
                    'habit_id'   => $habit->id,
                    'started_at' => $start->toDateString(),
                    'ended_at'   => $end->toDateString(),
                ]);
                $cursor = $end->clone()->addDays(rand(3, 10));
            }

            // 50% de chances d'avoir une période ouverte actuelle
            if (rand(0, 1)) {
                $start = now()->clone()->subDays(rand(5, 40));
                HabitPeriod::create([
                    'habit_id'   => $habit->id,
                    'started_at' => $start->toDateString(),
                    'ended_at'   => null,
                ]);
            }
        }
    }
}
