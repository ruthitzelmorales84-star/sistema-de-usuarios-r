<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\ComputerReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ComputerReservationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_student_can_reserve_equipment_and_see_the_reservation_date_and_time(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 22)->setTime(12, 34, 56));
        $student = User::factory()->create(['role' => 'estudiante']);
        $computer = Computer::query()->create(['name' => 'Equipo de prueba']);

        $this->actingAs($student)
            ->get(route('reservations.index', ['section' => 'reserve']))
            ->assertOk()
            ->assertSee('Ver reservaciones')
            ->assertSee('action="'.route('reservations.reserve', $computer).'"', false)
            ->assertSee('method="POST"', false)
            ->assertDontSee('Ubicación');

        $this->post(route('reservations.reserve', $computer))
            ->assertRedirect(route('reservations.index', ['section' => 'list']));

        $this->assertDatabaseHas('computer_reservations', [
            'computer_id' => $computer->id,
            'user_id' => $student->id,
            'reserved_at' => '2026-11-22 12:34:56',
            'cancelled_at' => null,
        ]);
        $this->assertDatabaseHas('computers', [
            'id' => $computer->id,
            'status' => 'reserved',
        ]);
        $this->assertSame(
            '2026-11-22 12:34:56',
            ComputerReservation::query()->sole()->reserved_at->format('Y-m-d H:i:s')
        );

        $this->get(route('reservations.index', ['section' => 'list']))
            ->assertOk()
            ->assertSee('Fecha y hora de reservación')
            ->assertSee($student->name)
            ->assertSee('22/11/2026 12:34');
    }
}
