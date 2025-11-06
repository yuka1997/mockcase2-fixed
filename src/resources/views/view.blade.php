@extends('app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/view.css') }}">
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
<div class="attendance-list__content">
    <div class="attendance-list__heading">
        <span class="heading-bar"></span>
        勤怠一覧
    </div>

    <div class="attendance-list__month-nav">
        @php
            $current = \Carbon\Carbon::parse($month);
            $prev = $current->copy()->subMonth()->format('Y-m');
            $next = $current->copy()->addMonth()->format('Y-m');

            $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
        @endphp

        <a href="{{ url('/attendance/list?month='.$prev) }}" class="month-nav__button">
            <img src="{{ asset('img/arrow.png') }}" class="month-nav__arrow" alt="前の月">前月
        </a>
        <div class="month-nav__current">
            <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="month-nav__calendar">
            <span class="month-nav__now">{{ $current->format('Y/m') }}</span>
        </div>
        <a href="{{ url('/attendance/list?month='.$next) }}" class="month-nav__button">
            翌月
            <img src="{{ asset('img/arrow.png') }}" class="month-nav__arrow" alt="翌月">
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
                @foreach ($dates as $date)
                    @php
                        $record = $attendances->get($date->toDateString());

                        $breakMinutes = 0;
                        if ($record && $record->breaks) {
                            foreach ($record->breaks as $break) {
                                if ($break->break_start && $break->break_end) {
                                    $breakMinutes += \Carbon\Carbon::parse($break->break_start)->diffInMinutes($break->break_end);
                                }
                            }
                        }

                        $totalMinutes = 0;
                        if ($record && $record->clock_in && $record->clock_out) {
                            $totalMinutes = \Carbon\Carbon::parse($record->clock_in)->diffInMinutes(\Carbon\Carbon::parse($record->clock_out)) - $breakMinutes;
                        }

                        $weekday = $weekDays[$date->dayOfWeek];
                    @endphp
                    <tr>
                        <td>{{ $date->format('m/d') }}({{ $weekday }})</td>
                        <td>{{ $record && $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $record && $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '' }}</td>
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
                            @if ($record)
                                <a href="{{ url('/attendance/detail/'.$record->id) }}" class="attendance-list__detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
