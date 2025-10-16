<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $q = '';
    public ?int $editId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public bool $is_admin = false;
    public bool $modalOpen = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|unique:users,email,' . $this->editId,
            'password' => $this->editId ? 'nullable|min:6' : 'required|min:6',
            'is_admin' => 'boolean',
        ];
    }

    public function updatingQ() { $this->resetPage(); }

    public function openCreate()
    {
        $this->reset(['editId','name','email','password','is_admin']);
        $this->modalOpen = true;
    }

    public function openEdit(int $id)
    {
        $u = User::findOrFail($id);
        $this->editId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->is_admin = (bool)$u->is_admin;
        $this->password = '';
        $this->modalOpen = true;
    }

    public function save()
    {
        $data = $this->validate();

        if (filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($this->editId) {
            User::whereKey($this->editId)->update($data);
            $message = __('messages.user_updated');
            session()->flash('ok', $message);
        } else {
            User::create($data);
            $message = __('messages.user_created');
            session()->flash('ok', $message);
        }

        $this->modalOpen = false;
    }

    public function delete(int $id)
    {
        if (auth()->id() === $id) return;
        User::whereKey($id)->delete();
        $message = __('messages.user_deleted');
        session()->flash('ok', $message);
    }

    public function render()
    {
        $users = User::query()
            ->when(trim($this->q) !== '', fn($q) =>
                $q->where('name', 'like', '%' . trim($this->q) . '%')
                  ->orWhere('email', 'like', '%' . trim($this->q) . '%')
            )
            ->orderBy('id','desc')
            ->paginate(10);

        return view('livewire.admin.users.index', compact('users'));
    }
}
