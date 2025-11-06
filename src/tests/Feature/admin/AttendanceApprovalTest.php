<?php

namespace Tests\Feature\admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\RequestBreak;
use Carbon\Carbon;

class AttendanceApprovalTest extends TestCase
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
        $this->attendance = $this->user->attendances()->first();
    }

    public function test_pending_requests_are_displayed_for_admin()
    {
        $pendingRequest = StampCorrectionRequest::create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'requested_note' => '承認待ちテスト',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/requests?status=pending');

        $response->assertStatus(200);
        $response->assertViewHas('requests', function ($requests) use ($pendingRequest) {
            return $requests->contains('id', $pendingRequest->id);
        });
    }

    public function test_approved_requests_are_displayed_for_admin()
    {
        $approvedRequest = StampCorrectionRequest::create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'requested_clock_in' => '08:30',
            'requested_clock_out' => '17:00',
            'requested_note' => '承認済みテスト',
            'status' => StampCorrectionRequest::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/requests?status=done');

        $response->assertStatus(200);
        $response->assertSee((string)$approvedRequest->id);
    }

    public function test_request_detail_is_displayed_correctly()
    {
        $request = StampCorrectionRequest::create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'requested_clock_in' => '10:00',
            'requested_clock_out' => '19:00',
            'requested_note' => '詳細テスト',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/requests/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('詳細テスト');
    }

    public function test_admin_can_approve_request_and_update_attendance()
    {
        $request = StampCorrectionRequest::create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'requested_clock_in' => '09:30',
            'requested_clock_out' => '18:30',
            'requested_note' => '承認処理テスト',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/requests/{$request->id}/approve");

        $response->assertRedirect();

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => StampCorrectionRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $request->attendance_id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
        ]);
    }
}