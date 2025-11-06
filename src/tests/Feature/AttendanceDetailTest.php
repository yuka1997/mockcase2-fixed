<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('role', User::ROLE_USER)->first();
        $this->attendance = Attendance::with('breaks')
            ->where('user_id', $this->user->id)
            ->first();
    }

    public function test_attendance_detail_displays_user_name()
    {
        $response = $this->actingAs($this->user)
            ->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    public function test_attendance_detail_displays_date()
    {
        $response = $this->actingAs($this->user)
            ->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);

        $formattedDate = Carbon::parse($this->attendance->work_date)->format('n月j日');
        $response->assertSee($formattedDate);
    }

    public function test_attendance_detail_displays_clock_in_and_clock_out()
    {
        $response = $this->actingAs($this->user)
            ->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee(substr($this->attendance->clock_in, 0, 5));
        $response->assertSee(substr($this->attendance->clock_out, 0, 5));
    }

    public function test_attendance_detail_displays_breaks()
    {
        $response = $this->actingAs($this->user)
            ->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);

        foreach ($this->attendance->breaks as $break) {
            $start = substr($break->break_start, 0, 5);
            $end   = substr($break->break_end, 0, 5);

            $response->assertSee($start);
            $response->assertSee($end);
        }
    }

}