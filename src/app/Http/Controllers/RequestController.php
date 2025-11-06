<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CorrectionRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use App\Models\RequestBreak;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = StampCorrectionRequest::with(['attendance', 'user'])
            ->where('user_id', Auth::id());

        if ($status === 'pending') {
            $query->where('status', StampCorrectionRequest::STATUS_PENDING);
        } elseif ($status === 'approved') {
            $query->where('status', StampCorrectionRequest::STATUS_APPROVED);
        } else {
            $query->where('status', StampCorrectionRequest::STATUS_REJECTED);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('requests', compact('requests'));
    }


    public function store(CorrectionRequest $request)
    {
        $newRequest = StampCorrectionRequest::create([
            'user_id'             => Auth::id(),
            'attendance_id'       => $request->attendance_id,
            'requested_clock_in'  => $request->requested_clock_in,
            'requested_clock_out' => $request->requested_clock_out,
            'status'              => StampCorrectionRequest::STATUS_PENDING,
            'requested_note'      => $request->requested_note,
        ]);

        $requestedBreaks = $request->input('requested_breaks', []);

        foreach ($requestedBreaks as $break) {
            if (!empty($break['start']) || !empty($break['end'])) {
                RequestBreak::create([
                    'request_id'            => $newRequest->id,
                    'requested_break_start' => $break['start'] ?? null,
                    'requested_break_end'   => $break['end'] ?? null,
                ]);
            }
        }

        return redirect()->to("/attendance/detail/{$request->attendance_id}")
            ->with('success', '打刻修正申請を送信しました。');
    }
}
