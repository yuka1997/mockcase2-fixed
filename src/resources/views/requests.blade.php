@extends('app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/requests.css') }}">
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
<div class="request-list__content">
    <div class="request-list__heading">
        <span class="heading-bar"></span>
        申請一覧
    </div>

    <div class="request-list__tab">
        <a href="?status=pending" class="request-list__tab-link {{ request('status','pending') == 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=approved" class="request-list__tab-link {{ request('status') == 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <div class="request-list__table-wrapper">
        <table class="request-list__table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $req)
                <tr>
                    <td>
                        @if($req->status == \App\Models\StampCorrectionRequest::STATUS_PENDING)
                            承認待ち
                        @elseif($req->status == \App\Models\StampCorrectionRequest::STATUS_APPROVED)
                            承認済み
                        @else
                            拒否
                        @endif
                    </td>
                    <td>{{ $req->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($req->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $req->requested_note }}</td>
                    <td>{{ $req->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ url('/attendance/detail/'.$req->attendance_id) }}" class="request-list__detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($requests->isEmpty())
            <p class="request-list__empty">該当する申請はありません。</p>
        @endif
    </div>
</div>
@endsection