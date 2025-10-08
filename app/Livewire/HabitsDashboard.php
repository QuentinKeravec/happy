<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Habit;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class HabitsDashboard extends Component
{
    public string $display = 'orbit'; // orbit | timeline | heatmap
    public string $name = '';
    public string $type = 'positive';

    public function addHabit()
    {
        $this->validate([
            'name' => 'required|min:2',
            'type' => 'in:positive,stop',
        ]);

        $habit = Habit::create([
            'user_id' => Auth::id(),
            'name'    => $this->name,
            'type'    => $this->type,
            'is_active' => true,
        ]);

        // démarre une période immédiatement si tu veux :
        $habit->start();

        $this->reset(['name']); $this->type = 'positive';
    }

    public function setDisplay(string $mode) { $this->display = $mode; }

    public function render()
    {
        // une seule requête avec eager-loading
        $habits = Habit::with('periods')
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('livewire.habits-dashboard', compact('habits'));
    }
}

