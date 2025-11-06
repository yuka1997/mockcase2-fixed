<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('role', User::ROLE_ADMIN)->first();
    }

    public function test_can_see_all_users_attendance_for_today_admin()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/attendances');

        $response->assertStatus(200);

        $attendances = Attendance::whereDate('work_date', Carbon::today())->get();

        $attendances->each(function ($attendance) use ($response) {
            $response->assertSee($attendance->user->name);
            $response->assertSee(Carbon::parse($attendance->work_date)->format('m/d'));

            if ($attendance->clock_in) {
                $response->assertSee(substr($attendance->clock_in, 0, 5));
            }
            if ($attendance->clock_out) {
                $response->assertSee(substr($attendance->clock_out, 0, 5));
            }
        });
    }

    public function test_current_date_is_displayed()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/attendances');

        $response->assertStatus(200);

        $currentDate = now()->format('Y/m/d');
        $response->assertSee($currentDate);
    }

    public function test_previous_day_attendance_is_displayed_when_click_prev()
    {
        $previousDay = now()->copy()->subDay();

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendances?date=' . $previousDay->format('Y-m-d'));

        $response->assertStatus(200);

        $attendances = Attendance::whereDate('work_date', $previousDay)->get();
        $attendances->each(function ($attendance) use ($response) {
            $response->assertSee(Carbon::parse($attendance->work_date)->format('m/d'));
        });
    }

    public function test_next_day_attendance_is_displayed_when_click_next()
    {
        $nextDay = now()->copy()->addDay();

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendances?date=' . $nextDay->format('Y-m-d'));

        $response->assertStatus(200);

        $attendances = Attendance::whereDate('work_date', $nextDay)->get();
        $attendances->each(function ($attendance) use ($response) {
            $response->assertSee(Carbon::parse($attendance->work_date)->format('m/d'));
        });
    }
}