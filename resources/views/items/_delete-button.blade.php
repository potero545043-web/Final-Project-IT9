@props([
    'item',
    'triggerClass' => 'danger-button',
    'triggerText' => 'Delete',
])

<button
    type="button"
    class="{{ $triggerClass }}"
    data-delete-trigger
    data-delete-action="{{ route('items.destroy', $item) }}"
    data-delete-id="{{ $item->id }}"
    data-delete-title="{{ $item->title }}"
    data-delete-owner="{{ $item->user->name ?? 'Unknown user' }}"
    data-delete-status="{{ $item->status_label }}"
>
    {{ $triggerText }}
</button>
