<div class="announcement">
    <a href="https://t.me/joinchat/AAAAAFht_FwOl_5gqoF1Hg" target="_blank">
        <i class="fab fa-telegram-plane"></i>Únete a nuestro canal de Telegram
    </a>
</div>

<div class="corporate">
    <div class="container">
        <div class="logo text-center">
                <i class="icon-logo"></i>
                <span>
                    LigasFifaXbox
                </span>
                <div class="py-2">
                    <small class="{{ online_registered_users() > 0 ? 'text-success' : '' }}">Usuarios registrados en línea: {{ online_registered_users() }}</small>
                </div>
            </div>
        </div>

        <div class="legal pt-2 pb-3">
            <span>
                Sitios recomendados
            </span>
            <a href="https://lpx.es/" target="_blank" class="d-block pt-2">
                <img src="{{ asset('img/lpx_banner.png') }}" style="border-radius: 4px" width="180">
            </a>
        </div>

        <div class="legal">
            {{-- <div class="container"> --}}
                <ul class="menu">
                    <li>
                        <a href="{{ route('contact') }}">Contacto</a>
                    </li>
                    <li>
                        <a href="{{ route('privacity') }}">Política de privacidad</a>
                    </li>
                </ul>
            {{-- </div> --}}
        </div>
    </div>
</div>

<div class="container py-2">
    <div class="text-center">
        <p class="m-0 py-1">© 2020 Derechos Reservados - LigasFifaXbox</p>
{{--         <div class="social mt-2">
            <ul>
                <li>
                    <a href="">
                        <i class="fab fa-twitter pr-2"></i>Seguir
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fab fa-facebook-f pr-2"></i>Me gusta
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fab fa-instagram p-2"></i>Seguir
                    </a>
                </li>
            </ul>
        </div> --}}
    </div>
</div>
