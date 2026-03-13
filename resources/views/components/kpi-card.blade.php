<div {{ $attributes->merge(['class' => 'kpi-card']) }}>
    <div class="flex justify-between items-center mb-2">
        <div class="flex items-center gap-2">
            @isset($icon)
                <i class="{{ $icon }} kpi-icon"></i>
            @endisset
            <span class="kpi-title">{{ $title }}</span>
        </div>
        @isset($trend)
            <span class="kpi-trend kpi-trend-{{ $trend['type'] ?? 'neutral' }}">{{ $trend['value'] }}</span>
        @endisset
    </div>
    <div class="kpi-value">{!! $value !!}</div>
    {{ $slot }}
</div>