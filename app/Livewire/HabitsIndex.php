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
    public string $type = 'good_habit'; // good_habit | bad_habit
    public ?float $amount_per_day = null;
    public string $scope = 'active';  // active | archived | all
    public string $q = '';            // recherche (optionnel)
    public string $sort = 'recent'; // recent | name | streak | best

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
            'type' => 'in:good_habit,bad_habit',
            'started_at' => 'required|date|before_or_equal:today',
        ]);

        $habit = Habit::create([
            'user_id'   => Auth::id(),
            'name'      => $this->name,
            'type'      => $this->type,
            'is_active' => true,
            'amount_per_day' => $this->amount_per_day,
        ]);

        // démarrer directement une période en cours
        $habit->start($this->started_at);

        // reset propre
        $this->reset(['name', 'type']);
        $this->type = 'good_habit';
        $this->started_at = Carbon::today()->toDateString();
        $this->resetValidation();
        $this->formKey++;
        $this->dispatch('$refresh');

        $message = __('messages.habit_added');

        $this->dispatch('toast', message: $message, type: 'success');
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

        $message = __('messages.habit_deleted');
        $this->dispatch('toast', message: $message, type: 'error');
    }

    public function setScope(string $scope): void { $this->scope = $scope; }


    public function render()
    {
        $query = Habit::with('periods')->where('user_id', Auth::id());

        // Filtres
        if ($this->scope !== 'all') {
            $query->where('is_active', $this->scope === 'active');
        }
        if (trim($this->q) !== '') {
            $q = trim($this->q);
            $query->where('name', 'like', "%{$q}%");
        }

        // Tri SQL quand possible
        if ($this->sort === 'name') {
            $query->orderBy('name');
        } elseif ($this->sort === 'recent') {
            $query->orderByDesc('created_at');
        }

        $habits = $query->get();

        // Tri collection pour valeurs calculées
        if ($this->sort === 'streak') {
            $habits = $habits->sortByDesc(fn($h) => $h->currentStreakDays())->values();
        } elseif ($this->sort === 'best') {
            $habits = $habits->sortByDesc(fn($h) => $h->bestStreakDays())->values();
        }

        // Si tu veux garder "Actives d'abord" quand scope=all
        if ($this->scope === 'all') {
            $habits = $habits->sortByDesc('is_active')->values();
        }

        // données du calendrier (si ouvert)
        $calendar = null;
        if ($this->calendarOpen && $this->calendarHabitId) {
            $habit = $habits->firstWhere('id', $this->calendarHabitId)
                  ?? Habit::with('periods')->find($this->calendarHabitId);

            if ($habit) {
                $locale = app()->getLocale();              // 'ja', 'en', ...
                $month  = Carbon::parse($this->calendarMonth)->locale($locale);
                $start   = $month->copy()->startOfMonth();
                $end     = $month->copy()->endOfMonth();
                $days    = [];
                $cursor  = $start->copy();

                $weekStart = match ($locale) {
                    'fr' => 1,        // Lundi
                    default => 0,     // Dimanche (en, ja, etc.)
                };

                $fmt = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, null, null, 'EEE');
                $weekdayLabels = [];
                for ($i = 0; $i < 7; $i++) {
                    // jour i à partir du weekStart : 0=dim ... 6=sam pour Carbon
                    $dow = ($weekStart + $i) % 7;
                    $date = (new \DateTimeImmutable('2025-01-05')) // dimanche
                        ->modify("+{$dow} day");
                    $weekdayLabels[] = $fmt->format($date); // ex: ['Sun','Mon',...] ou ['日','月',...]
                }

                $monthStart = $start->copy();
                $monthEnd   = $end->copy();
                $today = Carbon::today();

                // 1) Prépare une map de tous les jours du mois
                $days = [];
                for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
                    $key = $d->toDateString();
                    $days[$key] = [
                        'date'    => $key,
                        'day'     => $d->day,
                        'active'  => false,
                        'isStop'  => false,
                        'isToday' => $d->isToday(),
                    ];
                }

                // 2) Trie les périodes
                $periods = $habit->periods->sortBy('started_at')->values();

                // 3) Marque les jours ACTIFS (vert)
                foreach ($periods as $p) {
                    $pStart = $p->started_at->copy()->startOfDay();
                    $pEnd   = ($p->ended_at ?? $today)->copy()->startOfDay();

                    // clip aux bornes du mois
                    $from = $pStart->max($monthStart);
                    $to   = $pEnd->min($monthEnd);

                    for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                        $key = $d->toDateString();
                        if (isset($days[$key])) {
                            $days[$key]['active'] = true; // priorité au vert
                        }
                    }
                }

                // 4) Marque les jours d’ARRÊT (rouge) entre les périodes fermées
                for ($i = 0; $i < $periods->count(); $i++) {
                    /** @var \App\Models\HabitPeriod $cur */
                    $cur = $periods[$i];

                    // si la période est encore ouverte => pas d'intervalle d'arrêt derrière
                    if (is_null($cur->ended_at)) {
                        continue;
                    }

                    $curEnd = $cur->ended_at->copy()->startOfDay(); // inclus
                    // borne haute = veille du prochain start SI prochain existe, SINON aujourd'hui
                    $nextStartMinus1 = $periods->get($i + 1)?->started_at
                        ? $periods[$i + 1]->started_at->copy()->startOfDay()->subDay()
                        : $today;

                    // clip aux bornes du mois
                    $from = $curEnd->max($monthStart);
                    $to   = $nextStartMinus1->min($monthEnd);

                    if ($from->lte($to)) {
                        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                            $key = $d->toDateString();
                            // ne colorie en rouge que si NON actif
                            if (isset($days[$key]) && $days[$key]['active'] === false) {
                                $days[$key]['isStop'] = true;
                            }
                        }
                    }
                }

                // 5) Transforme en liste pour la vue
                $days = array_values($days);

                $start = $month->copy()->startOfMonth();

                $lead = ($start->dayOfWeek - $weekStart + 7) % 7;

                $monthLabel = $month->isoFormat('MMMM YYYY'); // respecte $locale

                $calendar = [
                    'habit'   => $habit,
                    'monthLabel'    => $monthLabel,
                    'weekdayLabels' => $weekdayLabels,
                    'days'    => $days,
                    'lead'    => $lead,
                    'canPrev' => $start->gt($habit->periods->min('started_at')->copy()->startOfMonth()),
                    'canNext' => $start->lt(Carbon::now()->startOfMonth()),
                ];
            }
        }

        $activeCount = $habits->where('is_active', true)->count();

        $totalSavedRaw = $habits->sum(function ($h) {
            $per = $h->amount_per_day ?? 0;
            return $per * $h->currentStreakDays();
        });

        $bestRecordDays = $habits->map(fn($h) => $h->bestStreakDays())->max() ?? 0;


        return view('livewire.habits-index', compact('habits','calendar', 'activeCount', 'totalSavedRaw', 'bestRecordDays'));
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
