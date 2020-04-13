<div class="cover-page-wrap animated fade-in">
    <div class="cover-page-container">
        <div class="logos">
            <div class="container">
                <div class="row">
                    <div class="col-6 align-middle">
                        <img class="logo_game" src="{{ asset('img/logo_fifa20.png') }}" alt="">
                    </div>
                    <div class="col-6 align-middle">
                        <img class="logo_platform" src="{{ asset('img/logo_xboxone.png') }}" alt="">
                    </div>
                </div>

{{--                 <div id="carouselExampleIndicators" class="carousel slide carousel-fade" data-ride="carousel" style="margin-top: 150px">
                    <ol class="carousel-indicators">
                        @foreach ($results as $post)
                            <li data-target="#carouselExampleIndicators" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : ''}}"></li>
                        @endforeach
                    </ol>
                    <div class="carousel-inner">
                        @foreach ($results as $post)
                            <div class="carousel-item {{ $loop->first ? 'active' : ''}}" style="min-height: 150px;">
                                <div class="carousel-caption align-top text-left">
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
                                            <img src="{{ asset($post->img) }}" style="width: 100%; height: auto; background: #c3cfea; border: 1px solid #940a53" class="rounded-circle">
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
                                                <span style="display: block; margin-bottom: 6px; font-size: 11px; color: #00d4e4">
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
                            </div>
                        @endforeach
                    </div>
                    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div> --}}


            </div>
        </div>
    </div>
</div>
