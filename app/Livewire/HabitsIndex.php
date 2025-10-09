<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Habit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

#[Layout('layouts.app')]
class HabitsIndex extends Component
{
    // UI state
    public string $name = '';
    public string $type = 'positive'; // positive | stop
    public string $scope = 'active';  // active | archived | all
    public string $q = '';            // recherche (optionnel)

    public string $started_at = '';

    public int $formKey = 0;

    public bool $calendarOpen = false;
    public ?int $calendarHabitId = null;
    public string $calendarMonth; // "YYYY-MM-01"

    public function mount(): void
    {
        // initialise la date de départ pour le formulaire si tu l'as
        if (!isset($this->started_at)) {
            $this->started_at = Carbon::today()->toDateString();
        }
        // mois courant par défaut pour le calendrier
        $this->calendarMonth = Carbon::now()->startOfMonth()->toDateString();
    }

    // Actions: créer
    public function addHabit(): void
    {
        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'type' => 'in:positive,stop',
            'started_at' => 'required|date|before_or_equal:today',
        ]);

        $habit = Habit::create([
            'user_id'   => Auth::id(),
            'name'      => $this->name,
            'type'      => $this->type,
            'is_active' => true,
        ]);

        // démarrer directement une période en cours
        $habit->start($this->started_at);

        // reset propre
        $this->reset(['name', 'type']);
        $this->type = 'positive';
        $this->started_at = Carbon::today()->toDateString();
        $this->resetValidation();
        $this->formKey++;
        $this->dispatch('$refresh');

        $this->dispatch('toast', message: 'Habitude ajoutée !', type: 'success');
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
        $query = Habit::with('periods')->where('user_id', Auth::id());
        if ($this->scope === 'active')   $query->where('is_active', true);
        if ($this->scope === 'archived') $query->where('is_active', false);
        if (trim($this->q) !== '')       $query->where('name', 'like', '%'.trim($this->q).'%');

        $habits = $query->orderByDesc('is_active')->get();

        // données du calendrier (si ouvert)
        $calendar = null;
        if ($this->calendarOpen && $this->calendarHabitId) {
            $habit = $habits->firstWhere('id', $this->calendarHabitId)
                  ?? Habit::with('periods')->find($this->calendarHabitId);

            if ($habit) {
                $month   = Carbon::parse($this->calendarMonth);
                $start   = $month->copy()->startOfMonth();
                $end     = $month->copy()->endOfMonth();
                $days    = [];
                $cursor  = $start->copy();

                // Pré-calc des segments actifs intersectant le mois
                $segments = $habit->periods->map(function($p) use ($start,$end){
                    $s = $p->started_at->copy();
                    $e = ($p->ended_at ?? Carbon::today())->copy();
                    if ($e->lt($start) || $s->gt($end)) return null; // pas d'intersection
                    return [
                        'from' => $s->max($start)->toDateString(),
                        'to'   => $e->min($end)->toDateString(),
                    ];
                })->filter();

                while ($cursor->lte($end)) {
                    $dateStr = $cursor->toDateString();
                    $active = $segments->first(function($seg) use ($dateStr){
                        return $dateStr >= $seg['from'] && $dateStr <= $seg['to'];
                    }) ? true : false;

                    $days[] = [
                        'date' => $dateStr,
                        'day'  => $cursor->day,
                        'active' => $active,
                        'isToday' => $cursor->isToday(),
                    ];
                    $cursor->addDay();
                }

                // 1er jour de la semaine (Lundi = 1) pour décaler la grille
                $lead = $start->isoWeekday() - 1; // 0..6

                $calendar = [
                    'habit'   => $habit,
                    'monthLabel' => $month->isoFormat('MMMM YYYY'),
                    'days'    => $days,
                    'lead'    => $lead,
                    'canPrev' => $start->gt($habit->periods->min('started_at')->copy()->startOfMonth()),
                    'canNext' => $start->lt(Carbon::now()->startOfMonth()),
                ];
            }
        }

        return view('livewire.habits-index', compact('habits','calendar'));
    }

    public function openCalendar(int $habitId): void
    {
        $this->calendarHabitId = $habitId;
        $this->calendarMonth   = Carbon::now()->startOfMonth()->toDateString();
        $this->calendarOpen    = true;
    }

    public function nextMonth(): void
    {
        if (!$this->calendarHabitId) return;
        $m = Carbon::parse($this->calendarMonth)->addMonth()->startOfMonth();
        // borne max = mois courant
        if ($m->greaterThan(Carbon::now()->startOfMonth())) return;
        $this->calendarMonth = $m->toDateString();
    }

    public function prevMonth(): void
    {
        if (!$this->calendarHabitId) return;
        // borne min = mois de la 1re période de l’habitude
        $habit = \App\Models\Habit::with('periods')->find($this->calendarHabitId);
        if (!$habit || $habit->periods->isEmpty()) return;

        $firstStart = $habit->periods->min('started_at')->copy()->startOfMonth();
        $m = Carbon::parse($this->calendarMonth)->subMonth()->startOfMonth();
        if ($m->lessThan($firstStart)) return;
        $this->calendarMonth = $m->toDateString();
    }
}
