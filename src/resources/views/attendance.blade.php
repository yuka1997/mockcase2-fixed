@extends('app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('header-extra')
<nav class="nav-menu">
    <a href="/attendance" class="nav-menu__link">勤怠</a>
    <a href="/attendance/list" class="nav-menu__link">勤怠一覧</a>
    <a href="/stamp_correction_request/list" class="nav-menu__link">申請</a>
    <form method="POST" action="/logout" class="nav-menu__form">
        @csrf
        <button type="submit" class="nav-menu__link">ログアウト</button>
    </form>
</nav>
@endsection

@section('content')
<div class="attendance__content">
    <div class="attendance__status">
        @if ($status === \App\Models\Attendance::STATUS_OFF)
            <span class="status__label status__label--off">勤務外</span>
        @elseif ($status === \App\Models\Attendance::STATUS_WORKING)
            <span class="status__label status__label--working">出勤中</span>
        @elseif ($status === \App\Models\Attendance::STATUS_BREAK)
            <span class="status__label status__label--break">休憩中</span>
        @elseif ($status === \App\Models\Attendance::STATUS_DONE)
            <span class="status__label status__label--done">退勤済</span>
        @endif
    </div>

    @php
        $today = now();
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $weekdays[$today->dayOfWeek];
    @endphp

    <div class="attendance__date">
        {{ $today->format('Y年n月j日') }}({{ $dayOfWeek }})
    </div>


    <div class="attendance__time">
        {{ now()->format('H:i') }}
    </div>

    <form action="/attendance" method="POST" class="attendance__form">
        @csrf
        @if ($status === \App\Models\Attendance::STATUS_OFF)
            <button type="submit" name="action" value="clock_in" class="attendance__button attendance__button--clock-in">
                出勤
            </button>
        @elseif ($status === \App\Models\Attendance::STATUS_WORKING)
            <div class="attendance__button-group">
                <button type="submit" name="action" value="clock_out" class="attendance__button attendance__button--clock-out">
                    退勤
                </button>
                <button type="submit" name="action" value="break_in" class="attendance__button attendance__button--break-in">
                    休憩入
                </button>
            </div>
        @elseif ($status === \App\Models\Attendance::STATUS_BREAK)
            <button type="submit" name="action" value="break_out" class="attendance__button attendance__button--break-out">
                休憩戻
            </button>
        @elseif ($status === \App\Models\Attendance::STATUS_DONE)
            <p class="attendance__message">お疲れ様でした。</p>
        @endif
    </form>

</div>
@endsection
