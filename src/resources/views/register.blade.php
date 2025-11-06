@extends('app')

@section('content')
<div class="register__content">
    <div class="register__heading">
        <h2 class="register__heading heading">会員登録</h2>
    </div>
    <form method="POST" action="/register" class="form">
        @csrf
        <div class="form__group">
            <span class="form__label">名前</span>
            <div class="form__input">
                <input type="text" name="name" value="{{ old('name') }}" />
            </div>
            <div class="form__error">
                @error('name')
                {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form__group">
            <span class="form__label">メールアドレス</span>
            <div class="form__input">
                <input type="email" name="email" value="{{ old('email') }}" />
            </div>
            <div class="form__error">
                @error('email')
                {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form__group">
            <span class="form__label">パスワード</span>
            <div class="form__input">
                <input type="password" name="password" value="{{ old('password') }}" />
            </div>
            <div class="form__error">
                @error('password')
                    @if ($message !== 'パスワードと一致しません')
                        {{ $message }}
                    @endif
                @enderror
            </div>
        </div>

        <div class="form__group">
            <span class="form__label">確認用パスワード</span>
            <div class="form__input">
                <input type="password" name="password_confirmation" />
            </div>
            <div class="form__error">
                @error('password_confirmation')
                    {{ $message }}
                @enderror

                @if ($errors->has('password') && $errors->first('password') === 'パスワードと一致しません')
                    {{ $errors->first('password') }}
                @endif
            </div>
        </div>

        <div class="form__actions">
            <button type="submit" class="form__button">登録する</button>
        </div>

        <div class="form__link">
            <a href="/login">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection