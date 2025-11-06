@extends('app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/view.css') }}">
@endsection

@section('header-extra')
<nav class="nav-menu">
    <a href="/admin/attendances" class="nav-menu__link">勤怠一覧</a>
    <a href="/admin/users" class="nav-menu__link">スタッフ一覧</a>
    <a href="/admin/requests" class="nav-menu__link">申請一覧</a>
    <form method="POST" action="/logout" class="nav-menu__form">
        @csrf
        <button type="submit" class="nav-menu__link">ログアウト</button>
    </form>
</nav>
@endsection

@section('content')
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <span class="heading-bar"></span>
        スタッフ一覧
    </div>

    <div class="attendance-list__table-wrapper">
        <table class="attendance-list__table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ url('/admin/users/' . $user->id . '/attendances') }}" class="attendance-list__detail-link">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection