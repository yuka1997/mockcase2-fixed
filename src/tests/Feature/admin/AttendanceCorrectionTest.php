<?php

namespace Tests\Feature\admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('role', User::ROLE_ADMIN)->first();
        $this->user = User::where('role', User::ROLE_USER)->first();

        $this->attendance = Attendance::where('user_id', $this->user->id)->first();
    }

    public function test_attendance_detail_displays_correct_data()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/attendances/' . $this->attendance->id);

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee(Carbon::parse($this->attendance->work_date)->format('n月j日'));
    }

    public function test_clock_in_after_clock_out_shows_error()
    {
        $response = $this->actingAs($this->admin)
            ->put('/admin/attendances/' . $this->attendance->id, [
                'attendance_id' => $this->attendance->id,
                'requested_clock_in' => '18:00',
                'requested_clock_out' => '09:00',
                'requested_note' => '備考テスト',
            ]);

        $response->assertSessionHasErrors(['requested_clock_in']);
    }

    public function test_break_start_after_clock_out_shows_error()
    {
        $response = $this->actingAs($this->admin)
            ->put('/admin/attendances/' . $this->attendance->id, [
                'attendance_id' => $this->attendance->id,
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_breaks' => [
                    ['start' => '19:00', 'end' => '19:30']
                ],
                'requested_note' => '備考テスト',
            ]);

        $response->assertSessionHasErrors(['requested_breaks.0.start']);
    }

    public function test_break_end_after_clock_out_shows_error()
    {
        $response = $this->actingAs($this->admin)
            ->put('/admin/attendances/' . $this->attendance->id, [
                'attendance_id' => $this->attendance->id,
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_breaks' => [
                    ['start' => '12:00', 'end' => '19:00']
                ],
                'requested_note' => '備考テスト',
            ]);

        $response->assertSessionHasErrors(['requested_breaks.0.end']);
    }

    public function test_empty_note_shows_error()
    {
        $response = $this->actingAs($this->admin)
            ->put('/admin/attendances/' . $this->attendance->id, [
                'attendance_id' => $this->attendance->id,
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_note' => '',
            ]);

        $response->assertSessionHasErrors(['requested_note']);
    }
}