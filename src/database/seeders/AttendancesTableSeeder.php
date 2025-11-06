<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakModel;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('role', User::ROLE_USER)->get();

        $today = Carbon::today();
        $prevMonth = $today->copy()->subMonth();
        $nextMonth = $today->copy()->addMonth();

        foreach ($users as $user) {
            for ($i = 1; $i <= 3; $i++) {
                $this->createAttendanceWithBreak($user->id, $prevMonth->copy()->day($i));
            }

            for ($i = 1; $i <= 7; $i++) {
                $this->createAttendanceWithBreak($user->id, $today->copy()->day($i));
            }

            for ($i = 1; $i <= 3; $i++) {
                $this->createAttendanceWithBreak($user->id, $nextMonth->copy()->day($i));
            }
        }
    }

    public function createAttendanceWithBreak($userId, $date)
    {
        $status = app()->environment('testing')
        ? Attendance::STATUS_OFF
        : Attendance::STATUS_DONE;

        $attendance = Attendance::create([
            'user_id' => $userId,
            'work_date' => $date->toDateString(),
            'clock_in' => $status === Attendance::STATUS_DONE ? '09:00:00' : null,
            'clock_out' => $status === Attendance::STATUS_DONE ? '18:00:00' : null,
            'status' => $status,
            'note' => 'ダミーデータ',
        ]);

        if ($status === Attendance::STATUS_DONE) {
            BreakModel::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
            ]);
        }
    }
}
