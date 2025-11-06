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
        <span class="heading-bar"></span>勤怠詳細(approval)
    </h2>

    @if (session('success'))
        <p class="attendance-detail__message">{{ session('success') }}</p>
    @endif

    <div class="attendance-detail__table-wrapper">
        <table class="attendance-detail__table">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td>{{ $requestData->user->name ?? '' }}</td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        @if($requestData->attendance && $requestData->attendance->work_date)
                            {{ \Carbon\Carbon::parse($requestData->attendance->work_date)->format('Y年n月j日') }}
                        @else
                            --
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        {{ $requestData->requested_clock_in ? substr($requestData->requested_clock_in, 0, 5) : '--' }}
                        〜
                        {{ $requestData->requested_clock_out ? substr($requestData->requested_clock_out, 0, 5) : '--' }}
                    </td>
                </tr>

                @foreach($requestData->requestBreaks as $index => $break)
                <tr>
                    <th>休憩{{ $index > 0 ? $index + 1 : '' }}</th>
                    <td>
                        {{ $break->requested_break_start ? substr($break->requested_break_start, 0, 5) : '--' }}
                        〜
                        {{ $break->requested_break_end ? substr($break->requested_break_end, 0, 5) : '--' }}
                    </td>
                </tr>
                @endforeach

                <tr>
                    <th>申請理由</th>
                    <td>{{ $requestData->requested_note ?? '（なし）' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="attendance-detail__actions">
        @if($requestData->status == \App\Models\StampCorrectionRequest::STATUS_PENDING)
            <form action="{{ url('/admin/requests/' . $requestData->id . '/approve') }}" method="POST">
                @csrf
                <button type="submit" class="attendance-detail__button">承認</button>
            </form>
        @elseif($requestData->status == \App\Models\StampCorrectionRequest::STATUS_APPROVED)
            <button class="attendance-detail__approved" disabled>承認済み</button>
        @endif
    </div>
</div>
@endsection