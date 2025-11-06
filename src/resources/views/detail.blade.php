@extends('app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
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
<div class="attendance-detail__content">
    <h2 class="attendance-detail__heading">
        <span class="heading-bar"></span>勤怠詳細
    </h2>

    @php
        $isPending = isset($stampRequest) && $stampRequest->status == \App\Models\StampCorrectionRequest::STATUS_PENDING;
    @endphp

    <form action="/requests" method="POST">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

        <div class="attendance-detail__table-wrapper">
            <table class="attendance-detail__table">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>{{ Auth::user()->name }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="attendance-detail__date-cell">
                                <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                                <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            @if($isPending)
                                <div class="attendance-detail-pending">
                                    {{ substr($attendance->clock_in,0,5) }}
                                    <span class="separator">〜</span>
                                    {{ substr($attendance->clock_out,0,5) }}
                                </div>
                            @else
                                <div class="attendance-detail__value time-range">
                                    <input type="time" name="requested_clock_in" value="{{ old('requested_clock_in', $attendance->clock_in ? substr($attendance->clock_in,0,5) : '') }}">
                                    <span class="separator">〜</span>
                                    <input type="time" name="requested_clock_out" value="{{ old('requested_clock_out', $attendance->clock_out ? substr($attendance->clock_out,0,5) : '') }}">
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
                            @endif
                        </td>
                    </tr>

                    @foreach($attendance->breaks as $index => $break)
                    <tr>
                        <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                        <td>
                            @if($isPending)
                                <div class="attendance-detail-pending">
                                    {{ substr($break->break_start,0,5) }}
                                    <span class="separator">〜</span>
                                    {{ substr($break->break_end,0,5) }}
                                </div>
                            @else
                                <div class="attendance-detail__value time-range">
                                    <input type="time" name="requested_breaks[{{ $index }}][start]" value="{{ old("requested_breaks.$index.start", $break->break_start ? substr($break->break_start,0,5) : '') }}">
                                    <span class="separator">〜</span>
                                    <input type="time" name="requested_breaks[{{ $index }}][end]" value="{{ old("requested_breaks.$index.end", $break->break_end ? substr($break->break_end,0,5) : '') }}">
                                </div>
                                <div class="form__error">
                                    @error('requested_breaks.*.start')
                                    {{ $message }}
                                    @enderror
                                </div>
                                <div class="form__error">
                                    @error('requested_breaks.*.end')
                                    {{ $message }}
                                    @enderror
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    @unless($isPending)
                    <tr>
                        <th>休憩{{ $attendance->breaks->count() + 1 }}</th>
                        <td>
                            <div class="attendance-detail__value time-range">
                                <input type="time" name="requested_breaks[new][start]" value="{{ old('requested_breaks.new.start') }}">
                                <span class="separator">〜</span>
                                <input type="time" name="requested_breaks[new][end]" value="{{ old('requested_breaks.new.end') }}">
                            </div>
                            <div class="form__error">
                                @error('requested_breaks.*.start')
                                {{ $message }}
                                @enderror
                            </div>
                            <div class="form__error">
                                @error('requested_breaks.*.end')
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @endunless

                    <tr>
                        <th>備考</th>
                        <td>
                            @if($isPending)
                                <div class="attendance-detail-pending">
                                    {{ $attendance->note ?: '（なし）' }}
                                </div>
                            @else
                                <textarea name="requested_note">{{ old('requested_note', $attendance->note) }}</textarea>
                                <div class="form__error">
                                    @error('requested_note')
                                    {{ $message }}
                                    @enderror
                                </div>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($isPending)
            <p class="request-pending-message">*承認待ちのため修正はできません。</p>
        @else
            <div class="attendance-detail__actions">
                <button type="submit" class="attendance-detail__button">修正</button>
            </div>
        @endif
    </form>
</div>
@endsection
