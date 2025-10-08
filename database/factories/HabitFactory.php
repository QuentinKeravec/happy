<?php

namespace Database\Factories;

use App\Models\Habit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HabitFactory extends Factory
{
    protected $model = Habit::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'name'      => $this->faker->randomElement([
                '7000 pas', 'Boire 2L d’eau', 'Lire 10 minutes',
                'Pompes x20', 'Étirements 5 min', 'Méditer 5 min', 'Arrêter de fumer'
            ]),
            'type'      => $this->faker->randomElement(['positive','stop']),
            'is_active' => true,
        ];
    }
}
