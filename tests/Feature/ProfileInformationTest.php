<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->name, $component->state['name']);
        $this->assertEquals($user->email, $component->state['email']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', ['name' => 'Test Name', 'email' => 'test@example.com'])
            ->call('updateProfileInformation');

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@example.com', $user->fresh()->email);
    }

    public function test_profile_information_can_update_the_login_username(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/user/profile-information', [
            'name' => 'Test Name',
            'username' => 'New.Username',
            'email' => $user->email,
        ])->assertSessionHasNoErrors();

        $this->assertSame('new.username', $user->fresh()->username);
    }

    public function test_profile_page_renders_role_specific_information_and_mobile_navigation(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

        $studentProfile = $this->actingAs($siswa)->get(route('siswa.profile'))
            ->assertOk()
            ->assertSee('Profil Siswa')
            ->assertSee('NIS')
            ->assertSee('Navigasi siswa')
            ->assertSee(route('siswa.profile'), false)
            ->assertSee(route('siswa.dashboard'), false)
            ->assertSee(route('siswa.dispensations.index'), false)
            ->assertSee(route('siswa.dispensations.create'), false)
            ->assertSee(route('siswa.notifications.index'), false)
            ->assertSee(route('logout'), false)
            ->assertDontSee('Buka navigasi')
            ->assertDontSee('Keamanan — Two-Factor Authentication');
        $this->assertSame(1, substr_count($studentProfile->getContent(), '<aside'));
        $this->assertStringContainsString('hidden lg:flex', $studentProfile->getContent());

        $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);

        $this->actingAs($guru)->get(route('guru.profile'))
            ->assertOk()
            ->assertSee('Profil Guru')
            ->assertSee('NIP')
            ->assertSee(route('guru.profile'), false)
            ->assertSee(route('guru.dispensations.create'), false)
            ->assertDontSee('Profil Administrator');

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('Profil Admin')
            ->assertSee('Administrator')
            ->assertSee(route('admin.profile'), false)
            ->assertDontSee('Profil Siswa')
            ->assertSee('Navigasi utama');
    }

    public function test_admin_profile_route_rejects_a_student_account(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

        $this->actingAs($student)->get(route('admin.profile'))->assertForbidden();
    }
}
