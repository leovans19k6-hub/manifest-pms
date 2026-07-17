@props([
    'method' => 'GET',
    'action' => null,
])

<x-card class="mb-6">

    <form
        method="{{ strtoupper($method) }}"
        action="{{ $action }}"
    >

        {{ $slot }}

    </form>

</x-card>