@php
$__icon = $icon ?? '📦';
$__size = $size ?? 20;
$__isImg = \Illuminate\Support\Str::startsWith($__icon, ['http://', 'https://']);
$__class = $class ?? '';
@endphp
@if($__isImg)
<img src="{{ $__icon }}" style="width:{{ $__size }}px;height:{{ $__size }}px;border-radius:{{ max(2, $__size/5) }}px;vertical-align:middle;" class="{{ $__class }}" alt="">
@else
<span style="font-size:{{ $__size }}px;line-height:1;vertical-align:middle;" class="{{ $__class }}">{{ $__icon }}</span>
@endif
