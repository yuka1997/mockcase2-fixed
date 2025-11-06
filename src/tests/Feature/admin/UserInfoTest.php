<?php

namespace Tests\Feature\admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UserInfoTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('role', User::ROLE_ADMIN)->first();
        $this->user = User::where('role', User::ROLE_USER)->first();
    }

    public function test_admin_can_see_all_users_name_and_email()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);

        $users = User::where('role', User::ROLE_USER)->get();

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_admin_can_see_selected_user_attendances()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/users/{$this->user->id}/attendances");

        $response->assertStatus(200);

        $this->user->attendances()
            ->whereMonth('work_date', now()->month)
            ->get()
            ->each(function ($attendance) use ($response) {
                $formattedDate = Carbon::parse($attendance->work_date)->format('m/d');
                $response->assertSee($formattedDate);
            });
    }

    public function test_previous_month_attendances_are_displayed()
    {
        $prevMonth = now()->copy()->subMonth();

        $response = $this->actingAs($this->admin)
            ->get("/admin/users/{$this->user->id}/attendances?month=" . $prevMonth->format('Y-m'));

        $response->assertStatus(200);

        $this->user->attendances()
            ->whereMonth('work_date', $prevMonth->month)
            ->get()
            ->each(function ($attendance) use ($response) {
                $formattedDate = Carbon::parse($attendance->work_date)->format('m/d');
                $response->assertSee($formattedDate);
            });
    }

    public function test_next_month_attendances_are_displayed()
    {
        $nextMonth = now()->copy()->addMonth();

        $response = $this->actingAs($this->admin)
            ->get("/admin/users/{$this->user->id}/attendances?month=" . $nextMonth->format('Y-m'));

        $response->assertStatus(200);

        $this->user->attendances()
            ->whereMonth('work_date', $nextMonth->month)
            ->get()
            ->each(function ($attendance) use ($response) {
                $formattedDate = Carbon::parse($attendance->work_date)->format('m/d');
                $response->assertSee($formattedDate);
            });
    }

    public function test_detail_link_redirects_to_attendance_detail_page()
    {
        $attendance = $this->user->attendances()->first();

        $response = $this->actingAs($this->admin)
            ->get("/admin/attendances/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }
}