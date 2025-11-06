<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_current_datetime_is_displayed_correctly()
    {
        $now = Carbon::now();

        $weekMap = ['日', '月', '火', '水', '木', '金', '土'];
        $dayOfWeek = $weekMap[$now->dayOfWeek];
        $expectedDate = $now->format("Y年m月d日({$dayOfWeek})");
        $expectedTime = $now->format('H:i');

        $user = User::first();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee($expectedDate = $now->isoFormat('Y年M月D日(ddd)'));
        $response->assertSee($expectedTime);
    }

    public function test_status_is_displayed_as_off()
    {
        $user = User::first();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => Attendance::STATUS_OFF,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('勤務外');
    }

    public function test_status_is_displayed_as_working()
    {
        $user = User::first();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_status_is_displayed_as_break()
    {
        $user = User::first();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => Attendance::STATUS_BREAK,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_status_is_displayed_as_done()
    {
        $user = User::first();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => Attendance::STATUS_DONE,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }
}