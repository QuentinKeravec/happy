<div class="max-w-5xl mx-auto px-4 py-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">{{ __('messages.users') }}</h1>
    <div class="flex gap-2">
      <input type="text" class="input input-bordered input-sm" placeholder="{{ __('messages.search') }}" wire:model.live.debounce.300ms="q">
      <button class="btn btn-primary btn-sm" wire:click="openCreate">+ {{ __('messages.add') }}</button>
    </div>
  </div>

  @if(session('ok'))
    <div class="alert alert-success mb-4">{{ session('ok') }}</div>
  @endif

  <div class="overflow-x-auto">
    <table class="table table-sm">
      <thead>
        <tr>
          <th>ID</th><th>{{ __('messages.name') }}</th><th>{{ __('messages.email') }}</th><th>{{ __('messages.administrator') }}</th><th>{{ __('messages.created_at') }}</th><th></th>
        </tr>
      </thead>
      <tbody>
      @foreach($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td>@if($u->is_admin)<span class="badge badge-success">{{ __('messages.yes') }}</span>@else<span class="badge">{{ __('messages.no') }}</span>@endif</td>
          <td>{{ $u->created_at?->format('Y-m-d') }}</td>
          <td class="text-right">
            <button class="btn btn-xs" wire:click="openEdit({{ $u->id }})">{{ __('messages.edit') }}</button>
            <button class="btn btn-xs btn-ghost" wire:click="delete({{ $u->id }})">{{ __('messages.delete') }}</button>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $users->links() }}</div>

  <dialog class="modal" @if($modalOpen) open @endif>
    <div class="modal-box">
      <h3 class="font-bold text-lg">{{ __('messages.user_action') }} {{ $editId ? __('messages.edit') : __('messages.add') }}</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="label-text">{{ __('messages.name') }} </label>
          <input class="input input-bordered w-full" wire:model="name">
          @error('name')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="label-text">{{ __('messages.email') }} </label>
          <input class="input input-bordered w-full" wire:model="email">
          @error('email')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="label-text">{{ __('messages.password') }} </label>
          <input type="password" class="input input-bordered w-full" wire:model="password">
          @error('password')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="label cursor-pointer justify-start gap-3 mt-6">
            <input type="checkbox" class="checkbox" wire:model="is_admin">
            <span class="label-text">{{ __('messages.administrator') }} </span>
          </label>
        </div>
      </div>

      <div class="modal-action">
        <button class="btn" wire:click="$set('modalOpen', false)">{{ __('messages.cancel') }} </button>
        <button class="btn btn-primary" wire:click="save">{{ __('messages.save') }} </button>
      </div>
    </div>
  </dialog>
</div>
