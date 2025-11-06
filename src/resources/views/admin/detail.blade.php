@extends('app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
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
<div class="attendance-detail__content">
    <h2 class="attendance-detail__heading">
        <span class="heading-bar"></span>勤怠詳細
    </h2>

    @if (session('success'))
        <p class="attendance-detail__message">{{ session('success') }}</p>
    @endif

    <form action="/admin/attendances/{{ $attendance->id }}" method="POST">
        @csrf
        @method('PUT')

        <div class="attendance-detail__table-wrapper">
            <table class="attendance-detail__table">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name ?? '' }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="attendance-detail__date-cell">
                                @if($attendance->work_date)
                                    <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                                    <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                                @else
                                    <span>--</span>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="attendance-detail__value time-range">
                                <input type="time" name="requested_clock_in" value="{{ old('requested_clock_in', $attendance->clock_in ? substr($attendance->clock_in, 0, 5) : '') }}">
                                <span class="separator">〜</span>
                                <input type="time" name="requested_clock_out" value="{{ old('requested_clock_out', $attendance->clock_out ? substr($attendance->clock_out, 0, 5) : '') }}">
                            </div>
                            <div class="form__error">
                                @error('requested_clock_in')
                                {{ $message }}
                                @enderror
                            </div>
                            <div class="form__error">
                                @error('requested_clock_out')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>

                    @foreach($attendance->breaks as $index => $break)
                    <tr>
                        <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                        <td>
                            <div class="attendance-detail__value time-range">
                                <input type="time" name="requested_breaks[{{ $index }}][start]" value="{{ old("requested_breaks.$index.start", $break->break_start ? substr($break->break_start, 0, 5) : '') }}">
                                <span class="separator">〜</span>
                                <input type="time" name="requested_breaks[{{ $index }}][end]" value="{{ old("requested_breaks.$index.end", $break->break_end ? substr($break->break_end, 0, 5) : '') }}">
                            </div>
                            @php
                                $breakError = $errors->first("requested_breaks.$index.start") ?: $errors->first("requested_breaks.$index.end");
                            @endphp
                            @if ($breakError)
                                <div class="form__error">{{ $breakError }}</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="requested_note">{{ old('requested_note', $attendance->note) }}</textarea>
                            <div class="form__error">
                                @error('requested_note')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="attendance-detail__actions">
            <button type="submit" class="attendance-detail__button">修正</button>
        </div>
    </form>
</div>
@endsection
