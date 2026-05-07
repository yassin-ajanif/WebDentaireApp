<div class="pl-0 pr-3 sm:pr-4">
    <div class="mb-6">
        <h1 class="app-title text-2xl font-semibold">{{ __('Treatment lines') }}</h1>
        @if($patientModel)
            <p class="app-subtitle mt-1 text-sm">{{ $patientModel->first_name }} {{ $patientModel->last_name }} — {{ $patientModel->telephone }}</p>
        @endif
        <a href="{{ route('patients.index') }}" class="app-title mt-2 inline-block text-sm hover:underline">{{ __('Back to patients') }}</a>
    </div>

    <div class="app-card mb-8 p-6 shadow-sm">
        <h2 class="app-title mb-4 text-lg font-medium">{{ $editingId ? __('Edit line') : __('Add line') }}</h2>
        <form wire:submit="saveLine" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="app-text-gray block text-sm font-medium">{{ __('Description') }}</label>
                <input type="text" wire:model="description" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="app-text-gray block text-sm font-medium">{{ __('Quantity') }}</label>
                <input type="number" min="1" wire:model="quantity" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                @error('quantity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="app-text-gray block text-sm font-medium">{{ __('Unit price') }}</label>
                <input type="text" wire:model="unit_price" class="app-input mt-1 block w-full px-3 py-2 text-sm" />
                @error('unit_price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                <button type="submit" class="app-btn-primary px-4 py-2 text-sm font-medium">{{ __('Save line') }}</button>
                @if($editingId)
                    <button type="button" wire:click="cancelEdit" class="app-btn-secondary px-4 py-2 text-sm">{{ __('Cancel edit') }}</button>
                @endif
            </div>
        </form>
    </div>

    <div class="app-card overflow-x-auto shadow-sm">
        <table class="app-divider min-w-full divide-y text-left text-sm">
            <thead class="app-text-gray text-xs font-semibold uppercase">
                <tr>
                    <th class="px-4 py-3">{{ __('Description') }}</th>
                    <th class="px-4 py-3">{{ __('Qty') }}</th>
                    <th class="px-4 py-3">{{ __('Unit') }}</th>
                    <th class="px-4 py-3">{{ __('Line total') }}</th>
                    <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="app-divider divide-y">
                @forelse ($lines as $line)
                    <tr wire:key="ti-{{ $line->id }}">
                        <td class="px-4 py-3">{{ $line->description }}</td>
                        <td class="px-4 py-3">{{ $line->quantity }}</td>
                        <td class="px-4 py-3">{{ $line->unit_price }}</td>
                        <td class="px-4 py-3 font-medium">{{ $line->line_total }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="startEdit({{ $line->id }})" class="app-title hover:underline">{{ __('Edit') }}</button>
                            <span class="app-text-muted">|</span>
                            <button type="button" wire:click="deleteLine({{ $line->id }})" wire:confirm="{{ __('Delete this line?') }}" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="app-text-muted px-4 py-6 text-center">{{ __('No treatment lines yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
