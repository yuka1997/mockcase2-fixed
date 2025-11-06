<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>COACHTECH</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="/login">
                <img src="{{ asset('img/logo.svg') }}" alt="COACHTECHロゴ">
            </a>
            <div class="header__extra">
                @yield('header-extra')
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>