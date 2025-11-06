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
        {{ $user->name }} さんの勤怠
    </div>

    <div class="attendance-list__month-nav">
        @php
            $current = \Carbon\Carbon::parse($currentMonth);
            $prev = $current->copy()->subMonth()->format('Y-m');
            $next = $current->copy()->addMonth()->format('Y-m');
        @endphp

        <a href="{{ url('/admin/users/' . $user->id . '/attendances?month=' . $prev) }}" class="day-nav__button">
            <img src="{{ asset('img/arrow.png') }}" class="day-nav__arrow" alt="前の月">前月
        </a>
        <div class="day-nav__current">
            <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="day-nav__calendar">
            <span class="day-nav__now">{{ $current->format('Y/m') }}</span>
        </div>
        <a href="{{ url('/admin/users/' . $user->id . '/attendances?month=' . $next) }}" class="day-nav__button">
            翌月
            <img src="{{ asset('img/arrow.png') }}" class="day-nav__arrow" alt="翌月">
        </a>
    </div>

    <div class="attendance-list__table-wrapper">
        <table class="attendance-list__table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    @php
                        $breakMinutes = 0;
                        foreach ($attendance->breaks as $break) {
                            if ($break->break_start && $break->break_end) {
                                $breakMinutes += \Carbon\Carbon::parse($break->break_start)->diffInMinutes($break->break_end);
                            }
                        }

                        $totalMinutes = 0;
                        if ($attendance->clock_in && $attendance->clock_out) {
                            $totalMinutes = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $breakMinutes;
                        }

                        $weekday = ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($attendance->work_date)->dayOfWeek];
                    @endphp

                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d') }}（{{ $weekday }}）</td>
                        <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                        <td>
                            @if ($breakMinutes > 0)
                                {{ floor($breakMinutes / 60) }}:{{ sprintf('%02d', $breakMinutes % 60) }}
                            @endif
                        </td>
                        <td>
                            @if ($totalMinutes > 0)
                                {{ floor($totalMinutes / 60) }}:{{ sprintf('%02d', $totalMinutes % 60) }}
                            @endif
                        </td>
                        <td>
                            <a href="{{ url('/admin/attendances/' . $attendance->id) }}" class="attendance-list__detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="csv-export">
    <a href="/admin/users/{{ $user->id }}/attendances/export?month={{ $month }}" class="csv-button">
        CSV出力
    </a>
</div>

</div>
@endsection