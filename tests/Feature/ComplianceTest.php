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
            ->assertRedirect(route('products.index'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_employee_can_create_and_update_but_cannot_delete_users(): void
    {
        $employee = User::factory()->create(['role' => 'empleado']);
        $target = User::factory()->create(['role' => 'alumno']);

        $this->actingAs($employee)->post('/users', [
            'name' => 'Usuario nuevo',
            'email' => 'nuevo@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'role' => 'alumno',
        ])->assertRedirect(route('users.index'));

        $this->actingAs($employee)
            ->delete("/users/{$target->id}")
            ->assertForbidden();
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
