<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('role', User::ROLE_USER)->first();
    }

    public function test_start_work_button_works_correctly()
    {
        $this->actingAs($this->user);

        $response = $this->get('/attendance');
        $response->assertSee('勤務外');

        $this->post('/attendance', ['action' => 'clock_in']);
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_cannot_clock_in_twice_in_a_day()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'clock_in']);

        $attendance = Attendance::where('user_id', $this->user->id)
                        ->where('work_date', Carbon::today())
                        ->first();

        $this->assertNotNull($attendance->clock_in);
        $this->assertEquals(Attendance::STATUS_WORKING, $attendance->status);
    }

    public function test_clock_in_time_is_displayed_in_attendance_list()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);

        $response = $this->get('/attendance/list');
        $attendance = Attendance::where('user_id', $this->user->id)
                        ->where('work_date', Carbon::today())
                        ->first();
        $this->assertStringContainsString(
            Carbon::parse($attendance->clock_in)->format('H:i'),
            $response->getContent()
        );
    }

    public function test_start_break_button_works_correctly()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'break_in']);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date', Carbon::today())
            ->first();

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }


    public function test_can_take_multiple_breaks_in_a_day()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'break_in']);
        $this->post('/attendance', ['action' => 'break_out']);
        $this->post('/attendance', ['action' => 'break_in']);
        $this->post('/attendance', ['action' => 'break_out']);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date', Carbon::today())
            ->first();

        $this->assertTrue($attendance->breaks()->count() >= 2);

        foreach ($attendance->breaks as $break) {
            $this->assertNotNull($break->break_start);
            $this->assertNotNull($break->break_end);
        }

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_end_break_button_works_correctly()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'break_in']);
        $this->post('/attendance', ['action' => 'break_out']);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date', Carbon::today())
            ->first();

        $lastBreak = $attendance->breaks()->latest('id')->first();
        $this->assertNotNull($lastBreak->break_end);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_can_end_break_multiple_times_in_a_day()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'break_in']);
        $this->post('/attendance', ['action' => 'break_out']);
        $this->post('/attendance', ['action' => 'break_in']);
        $this->post('/attendance', ['action' => 'break_out']);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date', Carbon::today())
            ->first();

        foreach ($attendance->breaks as $break) {
            $this->assertNotNull($break->break_end);
        }

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_break_times_are_displayed_in_attendance_list()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'break_in']);
        $this->post('/attendance', ['action' => 'break_out']);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date', Carbon::today())
            ->first();

        $response = $this->get('/attendance/list');
        foreach ($attendance->breaks as $break) {
            if ($break->break_start && $break->break_end) {
                $this->assertStringContainsString(
                    Carbon::parse($break->break_start)->format('H:i'),
                    $response->getContent()
                );
            }
        }
    }

    public function test_end_work_button_works_correctly()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'clock_out']);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test_clock_out_time_is_displayed_in_attendance_list()
    {
        $this->actingAs($this->user);

        $this->post('/attendance', ['action' => 'clock_in']);
        $this->post('/attendance', ['action' => 'clock_out']);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date', Carbon::today())
            ->first();

        $response = $this->get('/attendance/list');
        $this->assertStringContainsString(
            Carbon::parse($attendance->clock_out)->format('H:i'),
            $response->getContent()
        );
    }
}