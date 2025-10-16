<?php

namespace App\Livewire\Admin\Habits;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Habit;
use App\Models\User;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public ?int $editId = null;
    public ?int $user_id = null;
    public string $name = '';
    public string $type = 'good_habit';
    public ?float $amount_per_day = null;
    public bool $is_active = true;
    public bool $modalOpen = false;

    protected function rules()
    {
        return [
            'user_id'        => 'required|exists:users,id',
            'name'           => 'required|string|min:2|max:100',
            'type'           => 'required|in:good_habit,bad_habit',
            'amount_per_day' => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
        ];
    }

    public function updatingQ() { $this->resetPage(); }

    public function openCreate()
    {
        $this->reset(['editId','user_id','name','type','amount_per_day','is_active']);
        $this->type = 'good_habit';
        $this->is_active = true;
        $this->modalOpen = true;
    }

    public function openEdit(int $id)
    {
        $h = Habit::findOrFail($id);
        $this->editId        = $h->id;
        $this->user_id       = $h->user_id;
        $this->name          = $h->name;
        $this->type          = $h->type;
        $this->amount_per_day= $h->amount_per_day;
        $this->is_active     = (bool)$h->is_active;
        $this->modalOpen     = true;
    }

    public function save()
    {
        $data = $this->validate();

        if ($this->editId) {
            Habit::whereKey($this->editId)->update($data);
            $message = __('messages.habit_updated');
            session()->flash('ok', $message);
        } else {
            Habit::create($data);
            $message = __('messages.habit_created');
            session()->flash('ok', $message);
        }

        $this->modalOpen = false;
    }

    public function delete(int $id)
    {
        Habit::whereKey($id)->delete();
        $message = __('messages.habit_deleted');
        session()->flash('ok', $message);
    }

    public function render()
    {
        $query = Habit::query()
            ->with('user')
            ->when(trim($this->q) !== '', fn($q) =>
                $q->where('name','like','%'.trim($this->q).'%')
                  ->orWhereHas('user', fn($uq)=>$uq->where('name','like','%'.trim($this->q).'%'))
            )
            ->orderByDesc('id');

        $habits = $query->paginate(10);
        $users  = User::orderBy('name')->pluck('name','id');

        return view('livewire.admin.habits.index', compact('habits','users'));
    }
}
