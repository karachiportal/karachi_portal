<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
use App\Models\PriceItem;
use App\Models\PriceUpdateLog;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ThesisSystemTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function citizen(string $n = '1'): User
    {
        return User::create([
            'name' => "Citizen $n", 'username' => "citizen$n",
            'cnic' => "42101-123456$n-1", 'mobile' => "0300123456$n",
            'email' => "c$n@example.com", 'password' => Hash::make('Secret@123'), 'role' => 'citizen',
        ]);
    }

    private function staff(string $role): User
    {
        return User::create([
            'name' => ucfirst($role), 'username' => $role . '_t',
            'cnic' => $role === 'admin' ? '42101-7777777-1' : '42101-8888888-1',
            'mobile' => $role === 'admin' ? '03007777777' : '03008888888',
            'email' => "$role@example.com", 'password' => Hash::make('Secret@123'), 'role' => $role,
        ]);
    }

    private function complaintPayload(int $pics = 2): array
    {
        $p = [
            'full_name' => 'Citizen 1', 'cnic' => '42101-1234561-1', 'mobile' => '03001234561',
            'item_name' => 'Rice', 'shop_name' => 'Test Store', 'latitude' => 24.8607,
            'longitude' => 67.0011, 'location_address' => 'Saddar, Karachi', 'details' => 'Charged above the notified price',
        ];
        for ($i = 0; $i < $pics; $i++) {
            $p['pictures'][$i] = UploadedFile::fake()->image("p$i.jpg", 400, 300);
        }
        return $p;
    }

    private function file(User $u, int $pics = 2)
    {
        Sanctum::actingAs($u);
        return $this->post('/api/complaints', $this->complaintPayload($pics), ['Accept' => 'application/json']);
    }

    // ── Authentication ──
    public function test_tc01_register_valid_citizen(): void
    {
        $r = $this->postJson('/api/auth/register', [
            'name' => 'Ali', 'cnic' => '42101-1111111-1', 'mobile' => '03111111111',
            'email' => 'ali@example.com', 'password' => 'Secret@123', 'password_confirmation' => 'Secret@123',
        ]);
        $r->assertStatus(201)->assertJsonPath('success', true)->assertJsonPath('user.role', 'citizen');
        $this->assertNotEmpty($r->json('token'));
    }

    public function test_tc02_register_rejects_bad_cnic_format(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Ali', 'cnic' => '4210111111111', 'mobile' => '03111111111',
            'email' => 'ali@example.com', 'password' => 'Secret@123', 'password_confirmation' => 'Secret@123',
        ])->assertStatus(422)->assertJsonValidationErrors('cnic');
    }

    public function test_tc03_register_rejects_duplicate_cnic(): void
    {
        $this->citizen();
        $this->postJson('/api/auth/register', [
            'name' => 'Ali', 'cnic' => '42101-1234561-1', 'mobile' => '03111111111',
            'email' => 'ali@example.com', 'password' => 'Secret@123', 'password_confirmation' => 'Secret@123',
        ])->assertStatus(422)->assertJsonValidationErrors('cnic');
    }

    public function test_tc04_login_by_username_cnic_or_mobile(): void
    {
        $this->citizen();
        foreach (['citizen1', '42101-1234561-1', '03001234561'] as $id) {
            $this->postJson('/api/auth/login', ['username' => $id, 'password' => 'Secret@123'])
                ->assertOk()->assertJsonPath('success', true);
        }
    }

    public function test_tc05_login_rejects_wrong_password(): void
    {
        $this->citizen();
        $this->postJson('/api/auth/login', ['username' => 'citizen1', 'password' => 'wrong'])
            ->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_tc06_new_login_revokes_old_token(): void
    {
        $u = $this->citizen();
        $this->postJson('/api/auth/login', ['username' => 'citizen1', 'password' => 'Secret@123']);
        $this->postJson('/api/auth/login', ['username' => 'citizen1', 'password' => 'Secret@123']);
        $this->assertSame(1, $u->tokens()->count());
    }

    public function test_tc07_protected_route_needs_token(): void
    {
        $this->getJson('/api/complaints')->assertStatus(401);
    }

    // ── Prices ──
    public function test_tc08_price_list_and_categories_are_public(): void
    {
        $this->getJson('/api/prices/categories')->assertOk()->assertJsonPath('success', true);
        $this->getJson('/api/prices')->assertOk()->assertJsonPath('success', true);
    }

    public function test_tc09_price_search_finds_item(): void
    {
        $this->getJson('/api/prices/search?q=rice')->assertOk()->assertJsonPath('success', true);
    }

    public function test_tc10_price_trend_for_7_30_90_days(): void
    {
        $id = PriceItem::first()->id;
        foreach (['7days', '30days', '3months'] as $p) {
            $this->getJson("/api/prices/$id/trend?period=$p")->assertOk()->assertJsonPath('success', true);
        }
    }

    // ── Complaints ──
    public function test_tc11_file_complaint_with_photos(): void
    {
        Storage::fake('public');
        $r = $this->file($this->citizen(), 2);
        $r->assertStatus(201)->assertJsonPath('data.status', 'pending');
        $this->assertMatchesRegularExpression('/^CMPL-\d{4}-00001$/', $r->json('data.complaint_number'));
        $this->assertCount(2, $r->json('data.pictures'));
    }

    public function test_tc12_complaint_requires_at_least_one_photo(): void
    {
        $this->file($this->citizen(), 0)->assertStatus(422);
    }

    public function test_tc13_complaint_rejects_more_than_five_photos(): void
    {
        Storage::fake('public');
        $this->file($this->citizen(), 6)->assertStatus(422);
    }

    public function test_tc14_citizen_cannot_open_another_citizens_complaint(): void
    {
        Storage::fake('public');
        $id = $this->file($this->citizen('1'))->json('data.id');
        Sanctum::actingAs($this->citizen('2'));
        $this->getJson("/api/complaints/$id")->assertStatus(404);
    }

    public function test_tc15_citizen_tracks_own_complaint_by_number(): void
    {
        Storage::fake('public');
        $no = $this->file($this->citizen())->json('data.complaint_number');
        $this->getJson("/api/complaints/track/$no")->assertOk()->assertJsonPath('data.complaint_number', $no);
        $this->getJson('/api/complaints/track/CMPL-1999-99999')->assertStatus(404);
    }

    public function test_tc16_citizen_cannot_track_another_citizens_complaint(): void
    {
        Storage::fake('public');
        $no = $this->file($this->citizen('1'))->json('data.complaint_number');
        Sanctum::actingAs($this->citizen('2'));
        $this->getJson("/api/complaints/track/$no")->assertStatus(404)->assertJsonMissing(['cnic' => '42101-1234561-1']);
    }

    public function test_tc17_tracking_requires_login(): void
    {
        $this->getJson('/api/complaints/track/CMPL-2026-00001')->assertStatus(401);
    }

    public function test_tc18_admin_status_update_logs_and_notifies(): void
    {
        Storage::fake('public');
        $citizen = $this->citizen();
        $id = $this->file($citizen)->json('data.id');
        Sanctum::actingAs($this->staff('admin'));
        $this->putJson("/api/admin/complaints/$id/status", ['status' => 'in_progress', 'admin_remarks' => 'Inspector assigned'])
            ->assertOk()->assertJsonPath('data.status', 'in_progress');
        $this->assertSame(1, ComplaintStatusLog::where('complaint_id', $id)->where('new_status', 'in_progress')->count());
        $this->assertSame(1, UserNotification::where('user_id', $citizen->id)->where('type', 'complaint_update')->count());
    }

    public function test_tc19_citizen_is_refused_admin_api(): void
    {
        Sanctum::actingAs($this->citizen());
        $this->getJson('/api/admin/complaints')->assertStatus(403);
    }

    public function test_tc20_viewer_can_read_but_not_change(): void
    {
        Storage::fake('public');
        $id = $this->file($this->citizen())->json('data.id');
        Sanctum::actingAs($this->staff('staff'));
        $this->getJson('/api/admin/complaints')->assertOk();
        $this->putJson("/api/admin/complaints/$id/status", ['status' => 'resolved'])->assertStatus(403);
        $this->assertSame('pending', Complaint::find($id)->status);
    }

    // ── Price administration ──
    public function test_tc21_single_price_update_is_logged(): void
    {
        $item = PriceItem::where('price', '>', 0)->first();
        $old = $item->price;
        Sanctum::actingAs($admin = $this->staff('admin'));
        $this->putJson("/api/admin/price-items/{$item->id}", ['price' => $old + 10])->assertOk();
        $log = PriceUpdateLog::where('price_item_id', $item->id)->latest('id')->first();
        $this->assertEquals($old, $log->old_price);
        $this->assertEquals($old + 10, $log->new_price);
        $this->assertSame('single', $log->source);
        $this->assertSame($admin->id, $log->updated_by);
    }

    public function test_tc22_bulk_price_update_is_logged(): void
    {
        $items = PriceItem::take(3)->get();
        Sanctum::actingAs($this->staff('admin'));
        $this->postJson('/api/admin/prices/bulk-update', [
            'items' => $items->map(fn($i) => ['id' => $i->id, 'price' => $i->price + 5])->all(),
        ])->assertOk();
        $this->assertSame(3, PriceUpdateLog::where('source', 'bulk')->count());
    }

    // ── Notifications ──
    public function test_tc23_broadcast_reaches_every_citizen_and_read_all_clears(): void
    {
        $c = $this->citizen();
        $citizens = User::where('role', 'citizen')->count();
        Sanctum::actingAs($this->staff('admin'));
        $this->postJson('/api/admin/notifications/broadcast', ['title' => 'Ramazan prices', 'body' => 'New list issued', 'type' => 'price_alert'])->assertOk();
        $this->assertSame($citizens, UserNotification::where('title', 'Ramazan prices')->count());
        Sanctum::actingAs($c);
        $this->getJson('/api/notifications/unread-count')->assertJsonPath('count', 1);
        $this->postJson('/api/notifications/read-all')->assertOk();
        $this->getJson('/api/notifications/unread-count')->assertJsonPath('count', 0);
    }

    // ── Web panel ──
    public function test_tc24_citizen_cannot_log_in_to_web_panel(): void
    {
        $this->citizen();
        $this->post('/admin/login', ['username' => 'citizen1', 'password' => 'Secret@123'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_tc25_admin_logs_in_to_web_panel(): void
    {
        $this->staff('admin');
        $this->post('/admin/login', ['username' => 'admin_t', 'password' => 'Secret@123'])->assertRedirect(route('admin.dashboard'));
        $this->get('/admin/dashboard')->assertOk();
    }
}
