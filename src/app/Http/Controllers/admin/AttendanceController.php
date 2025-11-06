<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CorrectionRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $attendances = Attendance::whereDate('work_date', $date)
            ->with('user', 'breaks')
            ->orderBy('user_id')
            ->get();

        return view('admin.view', [
            'attendances' => $attendances,
            'currentDate' => \Carbon\Carbon::parse($date),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
        $user = $attendance->user;
        return view('admin.detail', compact('attendance'));
    }

    public function update(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            $attendance->update([
                'clock_in'  => $request->input('requested_clock_in') ?: null,
                'clock_out' => $request->input('requested_clock_out') ?: null,
                'note'      => $request->input('requested_note'),
            ]);

            foreach ($attendance->breaks as $index => $break) {
                if (isset($request->requested_breaks[$index])) {
                    $break->update([
                        'break_start' => $request->requested_breaks[$index]['start'] ?: null,
                        'break_end'   => $request->requested_breaks[$index]['end'] ?: null,
                    ]);
                }
            }
        });

        return redirect("/admin/attendances/{$attendance->id}")
            ->with('success', '勤怠情報を修正しました。');
    }

    public function userAttendances(Request $request, User $user)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
            ->orderBy('work_date', 'asc')
            ->get();

        return view('admin.user_attendances', [
            'user' => $user,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'month' => $month,
        ]);
    }

    public function exportCsv(Request $request, User $user)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->with('breaks')
            ->orderBy('work_date', 'asc')
            ->get();

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['日付', '出勤時刻', '退勤時刻', '休憩時間(合計)', '備考']);

            foreach ($attendances as $attendance) {
                $totalBreakMinutes = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $totalBreakMinutes += Carbon::parse($break->break_start)->diffInMinutes(Carbon::parse($break->break_end));
                    }
                }
                $breakTimeFormatted = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                fputcsv($handle, [
                    $attendance->work_date,
                    $attendance->clock_in,
                    $attendance->clock_out,
                    $breakTimeFormatted,
                    $attendance->note ?? '',
                ]);
            }

            fclose($handle);
        });

        $filename = "{$user->name}_{$month}_attendances.csv";

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}