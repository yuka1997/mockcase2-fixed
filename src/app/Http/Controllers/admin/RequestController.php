<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Models\RequestBreak;
use App\Models\BreakModel;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = StampCorrectionRequest::with(['attendance', 'user']);

        if ($status === 'pending') {
            $query->where('status', StampCorrectionRequest::STATUS_PENDING);
        } elseif ($status === 'approved') {
            $query->where('status', StampCorrectionRequest::STATUS_APPROVED);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('/admin/requests', compact('requests', 'status'));
    }

    public function show($id)
    {
        $requestData = StampCorrectionRequest::with(['attendance', 'user', 'requestBreaks'])
            ->findOrFail($id);

        return view('/admin/approval', compact('requestData'));
    }

    public function approve($id)
    {
        $requestData = StampCorrectionRequest::with(['attendance', 'requestBreaks'])->findOrFail($id);

        DB::transaction(function () use ($requestData) {

            $attendance = $requestData->attendance;
            $attendance->update([
                'clock_in'  => $requestData->requested_clock_in,
                'clock_out' => $requestData->requested_clock_out,
                'note'      => $requestData->requested_note,
            ]);

            $attendance->breaks()->delete();

            foreach ($requestData->requestBreaks as $reqBreak) {
                $attendance->breaks()->create([
                    'break_start' => $reqBreak->requested_break_start,
                    'break_end'   => $reqBreak->requested_break_end,
                ]);
            }

            $requestData->update([
                'status' => StampCorrectionRequest::STATUS_APPROVED,
            ]);
        });

        return redirect('/admin/requests/' . $id)->with('success', '申請を承認しました。');
    }
}