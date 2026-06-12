<table {{ $attributes->merge(['class' => 'mk-table']) }}>
    <thead>
        {{ $header }}
    </thead>
    <tbody>
        {{ $slot }}
    </tbody>
</table>
