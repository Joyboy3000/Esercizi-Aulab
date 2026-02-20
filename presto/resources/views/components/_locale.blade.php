@props(['lang', 'country'])

<a href="{{ route('setLocale', ['lang' => $lang]) }}" class="text-decoration-none" title="{{ strtoupper($lang) }}">
    <x-dynamic-component :component="'flag-country-'.$country" style="width: 24px; height: 24px;" />
</a>
