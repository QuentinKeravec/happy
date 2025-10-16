<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">{{ __('messages.habits') }}</h1>

    <div class="flex gap-2">
      <input type="text" class="input input-bordered input-sm w-64"
             placeholder="{{ __('messages.search') }}"
             wire:model.live.debounce.300ms="q" />
      <button class="btn btn-primary btn-sm" wire:click="openCreate">+ {{ __('messages.add') }}</button>
    </div>
  </div>

  @if (session('ok'))
    <div class="alert alert-success mb-4">
      {{ session('ok') }}
    </div>
  @endif

  <div class="overflow-x-auto">
    <table class="table table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>{{ __('messages.name') }}</th>
          <th>{{ __('messages.habit_label') }}</th>
          <th>{{ __('messages.type_label') }}</th>
          <th>{{ __('messages.cost_label') }}</th>
          <th>{{ __('messages.active') }}</th>
          <th>{{ __('messages.created_at') }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @forelse ($habits as $h)
        <tr>
          <td>{{ $h->id }}</td>
          <td>{{ $h->user->name ?? '—' }}</td>
          <td>{{ $h->name }}</td>
          <td><span class="badge {{ $h->type==='good_habit'?'badge-success':'badge-error' }}">{{ __("messages.{$h->type}") }}</span></td>
          <td>{{ $h->amount_per_day ?? '—' }}</td>
          <td>
            @if($h->is_active) <span class="badge badge-success">{{ __('messages.yes') }}</span>
            @else <span class="badge">{{ __('messages.no') }}</span> @endif
          </td>
          <td>{{ $h->created_at?->format('Y-m-d') }}</td>
          <td class="text-right">
            <button class="btn btn-xs" wire:click="openEdit({{ $h->id }})">{{ __('messages.edit') }}</button>
            <button class="btn btn-xs btn-ghost" wire:click="delete({{ $h->id }})">{{ __('messages.delete') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center opacity-60 py-8">{{ __('messages.no_result') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $habits->links() }}</div>

  {{-- Modal DaisyUI --}}
  <dialog class="modal" @if($modalOpen) open @endif>
    <div class="modal-box">
      <h3 class="font-bold text-lg">{{ __('messages.habit_action') }} {{ $editId ? __('messages.edit') : __('messages.create') }}</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div>
          <label class="label"><span class="label-text">{{ __('messages.users') }}</span></label>
          <select class="select select-bordered w-full" wire:model="user_id">
            <option value="">—</option>
            @foreach($users as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
          @error('user_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="label"><span class="label-text">{{ __('messages.habit_label') }}</span></label>
          <input type="text" class="input input-bordered w-full" wire:model="name">
          @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="label"><span class="label-text">Type</span></label>
          <select class="select select-bordered w-full" wire:model="type">
            <option value="good_habit">{{ __('messages.good_habit') }}</option>
            <option value="bad_habit">{{ __('messages.bad_habit') }}</option>
          </select>
          @error('type')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="label"><span class="label-text">{{ __('messages.cost_label') }}</span></label>
          <input type="number" step="0.01" class="input input-bordered w-full" wire:model="amount_per_day">
          @error('amount_per_day')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
          <label class="label cursor-pointer justify-start gap-3">
            <input type="checkbox" class="checkbox" wire:model="is_active">
            <span class="label-text">{{ __('messages.active') }}</span>
          </label>
        </div>
      </div>

      <div class="modal-action">
        <button class="btn" wire:click="$set('modalOpen', false)">{{ __('messages.cancel') }}</button>
        <button class="btn btn-primary" wire:click="save">{{ __('messages.add') }}</button>
      </div>
    </div>
  </dialog>
</div>
