<div {{ $attributes->merge(['class' => 'mk-card']) }}>
    <h4 class="mk-label">{{ $title }}</h4>
    <div class="mk-value">{{ $slot }}</div>
</div>
