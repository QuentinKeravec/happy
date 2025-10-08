<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Habit;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class HabitsIndex extends Component
{
    // UI state
    public string $name = '';
    public string $type = 'positive'; // positive | stop
    public string $scope = 'active';  // active | archived | all
    public string $q = '';            // recherche (optionnel)

    // Actions: créer
    public function addHabit(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'type' => 'in:positive,stop',
        ]);

        $habit = Habit::create([
            'user_id'   => Auth::id(),
            'name'      => $this->name,
            'type'      => $this->type,
            'is_active' => true,
        ]);

        // démarrer directement une période en cours
        $habit->start();

        $this->reset(['name']);
        $this->type = 'positive';
    }

    // Arrêter (clôturer la période en cours)
    public function stopHabit(int $habitId): void
    {
        $habit = Habit::where('id',$habitId)->where('user_id',Auth::id())->firstOrFail();
        $habit->stop();
    }

    // Reprendre (créer une nouvelle période ouverte)
    public function startHabit(int $habitId): void
    {
        $habit = Habit::where('id',$habitId)->where('user_id',Auth::id())->firstOrFail();
        $habit->start();
    }

    // Archiver / Restaurer / Supprimer
    public function archiveHabit(int $habitId): void
    {
        Habit::where('id',$habitId)->where('user_id',Auth::id())->update(['is_active'=>false]);
    }

    public function restoreHabit(int $habitId): void
    {
        Habit::where('id',$habitId)->where('user_id',Auth::id())->update(['is_active'=>true]);
    }

    public function deleteHabit(int $habitId): void
    {
        $habit = Habit::where('id',$habitId)->where('user_id',Auth::id())->firstOrFail();
        $habit->delete(); // cascade supprime les périodes
    }

    public function setScope(string $scope): void { $this->scope = $scope; }

    public function render()
    {
        $query = Habit::with('periods')
            ->where('user_id', Auth::id());

        if ($this->scope === 'active')   $query->where('is_active', true);
        if ($this->scope === 'archived') $query->where('is_active', false);

        if (trim($this->q) !== '') {
            $query->where('name', 'like', '%'.trim($this->q).'%');
        }

        $habits = $query->orderByDesc('is_active')->get();

        return view('livewire.habits-index', compact('habits'));
    }
}
