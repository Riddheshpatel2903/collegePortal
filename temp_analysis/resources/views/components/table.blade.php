@props(['headers' => []])

<div class="glass-card overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar">
        <table {{ $attributes->merge(['class' => 'table-premium']) }}>
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
