<div class="section-title">
    <div class="container">
        <div class="clearfix p-0">
            <h3 class="float-left">
                Últimas noticias
            </h3>
            @if ($posts->lastPage() > 1)
                <div class="navigation-buttons float-right">
                    <a href="{{ $posts->previousPageUrl() }}" class="mr-2 {{ $posts->currentPage() == 1 ? 'disabled' : '' }}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <span>{{ $posts->currentPage() }}</span>
                    <a href="{{ $posts->nextPageUrl() }}" class="ml-2 {{ $posts->currentPage() == $posts->lastPage() ? 'disabled' : '' }}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            @endif
        </div>
        <ul class="list-inline pt-2">
           <li class="list-inline-item">
                <a class="social-icon text-xs-center {{ $type == '' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home') }}">Todas</a>
            </li>
           <li class="list-inline-item">
                <a class="social-icon text-xs-center {{ $type == 'default' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'default') }}">Informativas</a>
            </li>
           <li class="list-inline-item">
                <a class="social-icon text-xs-center {{ $type == 'result' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'result') }}">Resultados</a>
            </li>
           <li class="list-inline-item">
                <a class="social-icon text-xs-center {{ $type == 'transfer' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'transfer') }}">Mercado</a>
            </li>
           <li class="list-inline-item">
                <a class="social-icon text-xs-center {{ $type == 'press' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'press') }}">Sala de prensa</a>
            </li>
           <li class="list-inline-item">
                <a class="social-icon text-xs-center {{ $type == 'champion' ? 'text-white disabled' : 'text-muted'  }}" href="{{ route('home.posts', 'champion') }}">Campeones</a>
            </li>
        </ul>
    </div>
</div>


<div class="row">
    <div class="col-12">
        @if ($posts->count() == 0)
            <div class="px-4 py-3 text-white">
                No existen noticias
            </div>
        @else
            <ul style="list-style: none; margin:0; padding: 0; border-bottom: 1px solid #0a1217">
                @foreach ($posts as $post)
                    <li class="py-2 d-block" style="display: table; border-top: 1px solid #0a1217">
                        <div class="container">
                            @if ($post->transfer_id || $post->match_id || $post->press_id)
                                @if ($post->transfer_id)
                                    <a href="{{ route('market') }}" class="d-block">
                                @elseif ($post->match_id && $post->match_exists())
                                    @if ($post->type == "champion")
                                        <a href="{{ route('competitions.table', [$post->match->competition()->season->slug, $post->match->competition()->slug, $post->match->group()->phase_slug_if_necesary(), $post->match->group()->group_slug_if_necesary()]) }}" class="d-block">
                                    @else
                                        <a href="{{ route('competitions.calendar', [$post->match->competition()->season->slug, $post->match->competition()->slug, $post->match->group()->phase_slug_if_necesary(), $post->match->group()->group_slug_if_necesary()]) }}" class="d-block">
                                    @endif
                                @elseif ($post->press_id && $post->press->participant_exists())
                                    <a href="{{ route('club.press', [$post->press->participant->season->slug, $post->press->participant->slug()]) }}" class="d-block">
                                @endif
                            @endif
                            <figure style="width: 96px; height: 96px; display: table-cell; position: relative; line-height: 1.1em;" class="m-0 text-center align-top px-2">
                                @if ($post->type == "transfer")
                                    <img src="{{ asset($post->img) }}" style="width: 100%; height: auto; background: #ECE8CE; border: 1px solid #590096" class="rounded-circle">
                                @elseif ($post->type == "press")
                                    <img src="{{ asset($post->img) }}" style="margin: .5em; width: auto; height: 60px;" class="rounded">
                                    <small class="text-white d-inline-block text-truncate" style="max-width: 80px;">{{ $post->press->participant->user->name }}</small>
                                @elseif ($post->type == "default")
                                    <img src="{{ asset($post->img) }}" style="width: auto; height: 80px">
                                @elseif ($post->type == "result")
                                    <img src="{{ asset($post->img) }}" style="width: auto; height: 80px" class="rounded-circle">
                                    <img src="{{ asset('img/competitions/whistle.png') }}" style="position: absolute; width: 35px; left: 58px; top: 48px" class="">
                                @elseif ($post->type == "champion")
                                    <img src="{{ asset('img/winner.png') }}" style="width: auto; height: 80px">
                                    @if ($post->match->clash)
                                        <img src="{{ asset($post->match->clash->winner()->participant->logo()) }}" style="position: absolute; width: 35px; left: 58px; top: 48px" class="">
                                    @else
                                        <img src="{{ asset($post->match->day->league->table_participant_position(1)->participant->logo()) }}" style="position: absolute; width: 35px; left: 58px; top: 48px" class="">
                                    @endif
                                    <img src="{{ asset($post->match->competition()->getImgFormatted()) }}" style="position: absolute; width: 35px; left: 5px; top: 48px" class="rounded-circle">
                                @endif
                            </figure>
                            <div style="display: table-cell; padding-left: 8px;" class="align-top">
                                <ul style="list-style: none; margin:0; padding: 0">
                                    <li>
                                        <span class="text-white d-block" style="font-size: .7em">
                                            {{ $post->created_at->diffForHumans() }}
                                        </span>
                                        <span style="display: block; margin-bottom: 6px; font-size: 11px; color: #08f8bf">
                                            {{ $post->category }}
                                            @if ($post->type == "champion")
                                                <span> - {{ $post->match->competition()->season->name }}</span>
                                            @endif
                                        </span>
                                        <span style="display: block; font-size: 16px; font-weight: 500; line-height: 20px; color: #fff;">
                                            {{ $post->title }}
                                        </span>
                                        <span style="display: block; font-size: 13px;line-height: 18px; color: #A4A4A4" class="mt-1">
                                            {{ $post->description }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            @if ($post->transfer_id || $post->match_id || $post->press_id)
                                @if ($post->transfer_id)
                                    </a>
                                @elseif ($post->match_id && $post->match_exists())
                                    </a>
                                @elseif ($post->press_id && $post->press->participant_exists())
                                    </a>
                                @endif
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
