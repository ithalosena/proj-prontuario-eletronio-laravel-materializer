{{-- Offcanvas lateral de notificações (v0.8.5-ux)
     Aberto pelo botão "Ver todas" no dropdown do sino da navbar.
     Recebe $notificacoesCount, $notificacoesLista via NavbarComposer. --}}
<div class="offcanvas offcanvas-end"
     tabindex="-1"
     id="offcanvas-notificacoes"
     aria-labelledby="offcanvas-notificacoes-titulo"
     style="width:420px">

    {{-- Cabeçalho --}}
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title fw-semibold mb-0" id="offcanvas-notificacoes-titulo">Notificações</h6>
        <div class="d-flex align-items-center gap-2 ms-auto">
            @if($notificacoesCount > 0)
                <form method="POST" action="/notificacoes/ler-todas" class="d-inline">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-text-secondary p-0 small border-0">
                        Marcar todas como lidas
                    </button>
                </form>
            @endif
            <button type="button" class="btn-close ms-1" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
    </div>

    {{-- Corpo scrollável --}}
    <div class="offcanvas-body p-0">
        @if($notificacoesLista->isEmpty())
            {{-- Estado vazio --}}
            <div class="text-center text-muted py-5">
                <i class="mdi mdi-bell-off-outline mdi-48px d-block mb-2"></i>
                <span class="small">Sem notificações</span>
            </div>
        @else
            <ul class="list-group list-group-flush">
                @foreach($notificacoesLista as $n)
                    {{-- Não lidas ficam com fundo destacado --}}
                    <li class="list-group-item list-group-item-action px-3 py-2 {{ is_null($n->read_at) ? 'bg-light' : '' }}">
                        <div class="d-flex align-items-start gap-2">
                            <a href="{{ $n->data['url'] ?? '#' }}"
                               class="d-flex align-items-start gap-2 flex-grow-1 text-decoration-none text-body">
                                <i class="mdi {{ $n->data['icone'] }} text-{{ $n->data['cor'] }} mt-1"></i>
                                <div>
                                    <div class="small {{ is_null($n->read_at) ? 'fw-semibold' : '' }}">{{ $n->data['titulo'] }}</div>
                                    <div class="text-muted small">{{ $n->data['mensagem'] }}</div>
                                    <div class="text-muted" style="font-size:0.7rem">{{ $n->created_at->diffForHumans() }}</div>
                                </div>
                            </a>
                            {{-- Botão "marcar como lida" apenas para não lidas --}}
                            @if(is_null($n->read_at))
                                <form method="POST" action="/notificacoes/{{ $n->id }}/ler" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-icon btn-text-secondary p-0" title="Marcar como lida">
                                        <i class="mdi mdi-check-circle-outline mdi-18px"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>
