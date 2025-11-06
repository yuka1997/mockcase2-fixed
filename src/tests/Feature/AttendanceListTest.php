<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('role', User::ROLE_USER)->first();
    }

    public function test_user_can_see_all_their_attendance_records()
    {
        $response = $this->actingAs($this->user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $this->user->attendances()
            ->whereMonth('work_date', now()->month)
            ->get()
            ->each(function($attendance) use ($response) {
                $formattedDate = \Carbon\Carbon::parse($attendance->work_date)->format('m/d');
                $response->assertSee($formattedDate);

                $attendance->breaks->each(function($break) use ($response) {
                    $response->assertSee(substr($break->break_start,0,5));
                    $response->assertSee(substr($break->break_end,0,5));
                });
            });

    }

    public function test_current_month_is_displayed()
    {
        $response = $this->actingAs($this->user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $currentMonth = now()->format('Y/m');
        $response->assertSee($currentMonth);
    }

    public function test_previous_month_is_displayed_when_click_prev()
    {
        $prevMonth = now()->copy()->subMonth();
        $response = $this->actingAs($this->user)
            ->get('/attendance/list?month=' . $prevMonth->format('Y-m'));

        $response->assertStatus(200);

        $this->user->attendances()
            ->whereMonth('work_date', $prevMonth->month)
            ->get()
            ->each(function ($attendance) use ($response) {
                $formattedDate = Carbon::parse($attendance->work_date)->format('m/d');
                $response->assertSee($formattedDate);
            });
    }

    public function test_next_month_is_displayed_when_click_next()
    {
        $nextMonth = now()->copy()->addMonth();
        $response = $this->actingAs($this->user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);

        $this->user->attendances()
            ->whereMonth('work_date', $nextMonth->month)
            ->get()
            ->each(function ($attendance) use ($response) {
                $formattedDate = Carbon::parse($attendance->work_date)->format('m/d');
                $response->assertSee($formattedDate);
            });
    }

    public function test_detail_link_redirects_to_attendance_detail()
    {
        $attendance = $this->user->attendances()->first();

        $response = $this->actingAs($this->user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }
}