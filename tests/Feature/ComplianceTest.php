<?php

namespace Tests\Feature;

use App\Mail\AdminVerificationCodeMail;
use App\Models\AcademicRecord;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_the_login_form_on_the_homepage(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('--surface-raised')
            ->assertDontSee('Reservación de equipos');
    }

    public function test_login_does_not_redirect_a_player_to_a_requested_unauthorized_module(): void
    {
        $player = User::factory()->create([
            'role' => 'jugador',
            'password' => Hash::make('password'),
        ]);

        $this->withSession([
            'module_intended_url' => route('products.index'),
            'url.intended' => route('products.index'),
        ])
            ->post(route('login.attempt'), [
                'email' => $player->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($player);
    }

    public function test_login_route_does_not_log_out_an_authenticated_employee(): void
    {
        $employee = User::factory()->create(['role' => 'empleado']);

        $this->actingAs($employee)
            ->get(route('login'))
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($employee);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sistema de usuarios')
            ->assertSee('Sistema de gestión de productos')
            ->assertDontSee('Panel de empleado')
            ->assertDontSee('Sistema académico')
            ->assertDontSee('Reservación de equipos')
            ->assertDontSee('Juego multijugador');

        $this->get('/empleado/dashboard')->assertNotFound();
        $this->get(route('reservations.index'))->assertForbidden();
        $this->get(route('modules.academic'))->assertForbidden();
        $this->get(route('modules.admin'))->assertForbidden();
        $this->get(route('multiplayer.index'))->assertForbidden();
    }

    public function test_student_homepage_only_shows_academic_and_reservation_modules(): void
    {
        $student = User::factory()->create(['role' => 'estudiante']);

        $this->actingAs($student)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Sistema académico')
            ->assertSee('Reservación de equipos')
            ->assertDontSee('Sistema de usuarios')
            ->assertDontSee('Sistema de gestión de productos')
            ->assertDontSee('Juego multijugador');

        $this->get(route('modules.academic'))
            ->assertOk()
            ->assertSee('Soy Estudiante')
            ->assertDontSee('Soy Profesor');
    }

    public function test_admin_requires_two_factor_verification(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.2fa.challenge'));
        $this->assertGuest();
        Mail::assertSent(AdminVerificationCodeMail::class);

        $code = session('admin_2fa_code');
        $this->post('/verificacion-admin', ['code' => $code])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_student_login_defaults_to_homepage(): void
    {
        $student = User::factory()->create([
            'role' => 'estudiante',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login.attempt'), [
            'email' => $student->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($student);
    }

    public function test_player_login_defaults_to_homepage(): void
    {
        $player = User::factory()->create([
            'role' => 'jugador',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login.attempt'), [
            'email' => $player->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($player);
    }

    public function test_student_can_view_and_reserve_an_available_computer(): void
    {
        $student = User::factory()->create(['role' => 'estudiante']);
        $computer = Computer::create([
            'name' => 'Equipo de prueba',
            'location' => 'Laboratorio',
            'status' => 'available',
        ]);

        $this->actingAs($student)
            ->get(route('reservations.index'))
            ->assertOk()
            ->assertSee('Equipo de prueba')
            ->assertSee('Reservar');

        $this->actingAs($student)
            ->from(route('reservations.index'))
            ->post(route('reservations.reserve', $computer))
            ->assertRedirect(route('reservations.index'))
            ->assertSessionHas('success', 'Equipo reservado correctamente.');

        $this->assertDatabaseHas('computer_reservations', [
            'computer_id' => $computer->id,
            'user_id' => $student->id,
            'cancelled_at' => null,
        ]);
        $this->assertDatabaseHas('computers', [
            'id' => $computer->id,
            'status' => 'reserved',
        ]);
    }

    public function test_employee_cannot_assign_the_removed_alumno_role(): void
    {
        $employee = User::factory()->create(['role' => 'empleado']);

        $this->actingAs($employee)->post(route('users.store'), [
            'name' => 'Usuario con rol anterior',
            'email' => 'alumno-anterior@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'alumno',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'alumno-anterior@example.com']);
    }

    public function test_employee_can_create_and_update_but_cannot_delete_users(): void
    {
        $employee = User::factory()->create(['role' => 'empleado']);
        $target = User::factory()->create(['role' => 'estudiante']);

        $this->actingAs($employee)->post('/users', [
            'name' => 'Usuario nuevo',
            'email' => 'nuevo@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'role' => 'estudiante',
        ])->assertRedirect(route('users.index'));

        $this->actingAs($employee)
            ->delete("/users/{$target->id}")
            ->assertForbidden();
    }

    public function test_employee_cannot_create_an_admin_account(): void
    {
        $employee = User::factory()->create(['role' => 'empleado']);

        $this->actingAs($employee)->get(route('users.create'))
            ->assertOk()
            ->assertDontSee('<option value="admin"', false);

        $this->actingAs($employee)->post(route('users.store'), [
            'name' => 'Administrador no autorizado',
            'email' => 'nuevo-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'nuevo-admin@example.com']);
    }

    public function test_employee_cannot_edit_an_admin_or_change_their_own_role(): void
    {
        $employee = User::factory()->create(['role' => 'empleado']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($employee)->get(route('users.edit', $admin))
            ->assertForbidden();

        $this->actingAs($employee)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'empleado',
        ])->assertForbidden();

        $this->actingAs($employee)->put(route('users.update', $employee), [
            'name' => $employee->name,
            'email' => $employee->email,
            'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['id' => $employee->id, 'role' => 'empleado']);
    }

    public function test_professor_can_open_edit_and_update_academic_record(): void
    {
        $professor = User::factory()->create(['role' => 'profesor']);
        $record = AcademicRecord::create([
            'student_name' => 'Ana López',
            'student_email' => 'ana@example.com',
            'absences' => 2,
            'grade' => 6.5,
        ]);

        $this->actingAs($professor)
            ->withSession([
                'academic_access_role' => 'profesor',
                'academic_2fa_verified' => true,
            ])
            ->get(route('academic.edit', $record))
            ->assertOk()
            ->assertSee('Editar alumno');

        $this->actingAs($professor)
            ->withSession([
                'academic_access_role' => 'profesor',
                'academic_2fa_verified' => true,
            ])
            ->put(route('academic.update', $record), [
                'student_name' => 'Ana López',
                'student_email' => 'ana@example.com',
                'absences' => 1,
                'grade' => 9.5,
            ])
            ->assertRedirect(route('academic.index'))
            ->assertSessionHas('success');

        $record->refresh();
        $this->assertSame(1, $record->absences);
        $this->assertSame(9.5, (float) $record->grade);
        $this->assertSame('AU', $record->equivalence);
    }

    public function test_student_cannot_cancel_another_users_reservation(): void
    {
        $owner = User::factory()->create(['role' => 'estudiante']);
        $other = User::factory()->create(['role' => 'estudiante']);

        $computer = Computer::create([
            'name' => 'Equipo 1',
            'location' => 'Laboratorio',
            'status' => 'reserved',
        ]);

        $reservation = $computer->reservations()->create([
            'user_id' => $owner->id,
            'reserved_at' => now(),
        ]);

        $this->actingAs($other)
            ->post("/reservaciones/{$reservation->id}/cancelar")
            ->assertForbidden();
    }
}
