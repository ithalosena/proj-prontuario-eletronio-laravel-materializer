{{-- _breadcrumb.blade.php
     Partial reutilizável de breadcrumb para todas as páginas do sistema.
     Uso: @push('breadcrumbs') @include('content.pages.partials._breadcrumb', ['breadcrumbs' => [...]]) @endpush
     Parâmetro $breadcrumbs: array de ['label' => string, 'url' => string|null]
     O último item (sem 'url') é tratado como item ativo.
--}}
<div class="container-xxl px-4 pt-3 pb-0">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb breadcrumb-style1 mb-0">
      @foreach($breadcrumbs as $crumb)
        @if(!empty($crumb['url']))
          <li class="breadcrumb-item">
            <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
          </li>
        @else
          <li class="breadcrumb-item active" aria-current="page">
            {{ $crumb['label'] }}
          </li>
        @endif
      @endforeach
    </ol>
  </nav>
</div>