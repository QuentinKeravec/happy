<?php

namespace Database\Factories;

use App\Models\Habit;
use App\Models\HabitPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class HabitPeriodFactory extends Factory
{
    protected $model = HabitPeriod::class;

    public function definition(): array
    {
        // par défaut : une petite période fermée aléatoire dans les 90 derniers jours
        $start = now()->subDays(rand(10, 90));
        $end   = (clone $start)->addDays(rand(3, 25));

        return [
            'habit_id'   => Habit::factory(),
            'started_at' => $start->toDateString(),
            'ended_at'   => $end->toDateString(),
        ];
    }

    // état “période en cours”
    public function open(): static
    {
        $start = now()->subDays(rand(3, 40));
        return $this->state(fn () => [
            'started_at' => $start->toDateString(),
            'ended_at'   => null,
        ]);
    }

    // rattacher à un habit existant
    public function forHabit(Habit $habit): static
    {
        return $this->state(fn () => ['habit_id' => $habit->id]);
    }
}
