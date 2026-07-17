<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\HrmpsbPanelMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliberationSessionAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bypass the custom Role middleware so we can isolate testing for the Session Access middleware.
        $this->withoutMiddleware([\App\Http\Middleware\RoleMiddleware::class]);
    }

    public function test_user_without_session_access_is_redirected()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        
        $applicant = Applicant::factory()->create(['position_applied' => 'Administrative Officer I']);

        $this->actingAs($user);
        
        $this->assertDatabaseMissing('hrmpsb_panel_members', [
            'user_id' => $user->id,
            'position_applied' => $applicant->position_applied,
        ]);

        $response = $this->get(route('recruitment.deliberation.show', $applicant));
        
        $response->assertRedirect(route('recruitment.deliberation.list'));
        $response->assertSessionHasErrors(['access' => 'You do not have access to the deliberation session for this position.']);
    }

    public function test_user_with_session_access_can_view_workspace()
    {
        $user = User::factory()->create();
        
        $applicant = Applicant::factory()->create(['position_applied' => 'Administrative Officer I']);

        // Grant access
        HrmpsbPanelMember::create([
            'position_applied' => $applicant->position_applied,
            'user_id' => $user->id,
            'role' => 'HRMPSB_MEMBER',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('recruitment.deliberation.show', $applicant));
        
        $response->assertStatus(200);
        $response->assertViewIs('recruitment.deliberation.show');
    }
}
