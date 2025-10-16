<?php

namespace App\Livewire\Admin\Periods;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Habit;
use App\Models\HabitPeriod;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public ?int $editId = null;
    public ?int $habit_id = null;
    public ?string $started_at = null;
    public ?string $ended_at = null;
    public bool $modalOpen = false;

    protected function rules()
    {
        return [
            'habit_id' => 'required|exists:habits,id',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
        ];
    }

    public function updatingQ() { $this->resetPage(); }

    public function openCreate()
    {
        $this->reset(['editId','habit_id','started_at','ended_at']);
        $this->modalOpen = true;
    }

    public function openEdit(int $id)
    {
        $p = HabitPeriod::findOrFail($id);
        $this->editId = $p->id;
        $this->habit_id = $p->habit_id;
        $this->started_at = $p->started_at?->format('Y-m-d');
        $this->ended_at = $p->ended_at?->format('Y-m-d');
        $this->modalOpen = true;
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editId) {
            HabitPeriod::whereKey($this->editId)->update($data);
            $message = __('messages.period_updated');
            session()->flash('ok', $message);
        } else {
            HabitPeriod::create($data);
            $message = __('messages.period_updated');
            session()->flash('ok', $message);
        }

        $this->modalOpen = false;
    }

    public function delete(int $id)
    {
        HabitPeriod::whereKey($id)->delete();
        $message = __('messages.period_updated');
        session()->flash('ok', $message);
    }

    public function render()
    {
        $periods = HabitPeriod::query()
            ->with('habit')
            ->when(trim($this->q) !== '', fn($q) =>
                $q->whereHas('habit', fn($h)=>$h->where('name','like','%'.trim($this->q).'%'))
            )
            ->orderByDesc('started_at')
            ->paginate(10);

        $habits = Habit::orderBy('name')->pluck('name','id');

        return view('livewire.admin.periods.index', compact('periods','habits'));
    }
}
