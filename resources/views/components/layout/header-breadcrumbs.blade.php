@php
    use App\Helpers\MenuHelper;

    $crumbs = MenuHelper::currentBreadcrumb();
    $currentLabel = collect($crumbs)->last()['label'] ?? '';
@endphp

<nav aria-label="Breadcrumb" {{ $attributes->class(['header-breadcrumbs']) }}>
    <span class="header-breadcrumbs__mobile md:hidden">{{ $currentLabel }}</span>

    <ol class="header-breadcrumbs__trail hidden md:flex">
        @foreach ($crumbs as $index => $crumb)
            @if ($index > 0)
                <li class="header-breadcrumbs__sep" aria-hidden="true">/</li>
            @endif
            <li class="header-breadcrumbs__item">
                @if (! empty($crumb['current']) || empty($crumb['path']))
                    <span @class(['header-breadcrumbs__current' => ! empty($crumb['current']), 'header-breadcrumbs__muted' => empty($crumb['current'])])>
                        {{ $crumb['label'] }}
                    </span>
                @else
                    <a href="{{ $crumb['path'] }}" class="header-breadcrumbs__link">
                        {{ $crumb['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
