@php
    use App\Entities\Appointment\Contracts\AppointmentServiceInterface;
    use App\Entities\Appointment\Enums\AppointmentStatus;

    $variant = $variant ?? 'light';
    $svc = app(AppointmentServiceInterface::class);
    $from = $appointment->status;

    $ordered = [
        ['to' => AppointmentStatus::InProgress, 'label' => __('Démarrer')],
        ['to' => AppointmentStatus::Done, 'label' => __('Terminer')],
        ['to' => AppointmentStatus::Waiting, 'label' => __('Remettre en attente')],
        ['to' => AppointmentStatus::Cancelled, 'label' => __('Annuler')],
    ];

    $visible = [];
    foreach ($ordered as $item) {
        if ($from !== $item['to'] && $svc->isTransitionAllowed($from, $item['to'])) {
            $visible[] = $item;
        }
    }
@endphp

@if (count($visible) > 0)
    <div @class([
        'app-queue-card-en-cours-actions flex flex-wrap items-center justify-end gap-x-0' => $variant === 'dark',
        'mt-3 flex flex-wrap items-center justify-end gap-x-0 text-right text-xs' => $variant === 'light',
    ])>
        @foreach ($visible as $i => $item)
            @if ($i > 0)
                <span @class([
                    'app-queue-action-sep px-1' => $variant === 'dark',
                    'app-text-muted px-1' => $variant === 'light',
                ])>|</span>
            @endif
            @php
                $isCancel = $item['to'] === AppointmentStatus::Cancelled;
            @endphp
            <button
                type="button"
                wire:click="setAppointmentStatus({{ $appointment->id }}, '{{ $item['to']->value }}')"
                @class([
                    'app-queue-action app-queue-action-cancel hover:underline' => $variant === 'dark' && $isCancel,
                    'app-queue-action hover:underline' => $variant === 'dark' && ! $isCancel,
                    'text-red-600 hover:underline' => $variant === 'light' && $isCancel,
                    'app-title hover:underline' => $variant === 'light' && ! $isCancel,
                ])
            >{{ $item['label'] }}</button>
        @endforeach
    </div>
@endif
