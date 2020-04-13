<div class="section-title">
    <div class="container">
        <h3>Recién llegados</h3>
    </div>
</div>
<div class="container text-white">
    @if ($last_users->count()>0)
        <ul class="py-2 px-0 text-white text-center">
            @foreach ($last_users as $user)
                <li class="m-1" style="list-style: none; display: inline-block;">
                    <img src="{{ asset($user->avatar()) }}" width="64" class="rounded-circle p-1" style="border: 1px solid #AB9205; background: #777168">
                    <small class="d-block text-truncate mt-1" style="max-width: 64px">{{ $user->name }}</small>
                </li>
            @endforeach
        </ul>
    @else
        <div class="px-4 py-3 text-white">
            No existen usuarios
        </div>
    @endif
</div>