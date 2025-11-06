@extends('app')

@section('content')
<div class="admin-login__content">
    <div class="admin-login__heading">
        <h2 class="admin-login__heading heading">管理者ログイン</h2>
    </div>
    <form method="POST" action="/admin/login" class="form">
        @csrf
        <div class="form__group">
            <span class="form__label">メールアドレス</span>
            <div class="form__input">
                <input type="text" name="email" value="{{ old('email') }}" />
            </div>
            <div class="form__error">
                @error('email')
                {{ $message }}
                @enderror

                @if ($errors->has('login'))
                    {{ $errors->first('login') }}
                @endif
            </div>
        </div>

        <div class="form__group">
            <span class="form__label">パスワード</span>
            <div class="form__input">
                <input type="password" name="password" />
            </div>
            <div class="form__error">
                @error('password')
                {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form__actions">
            <button type="submit" class="form__button">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection