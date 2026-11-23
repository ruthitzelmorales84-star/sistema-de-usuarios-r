<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\ComputerReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComputerReservationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->role === 'admin';

        $computers = Computer::query()->with(['reservations.user'])->orderBy('name')->get();

        $reservations = $isAdmin
            ? ComputerReservation::with(['computer', 'user'])->latest('reserved_at')->get()
            : ComputerReservation::with('computer')
                ->where('user_id', $user->id)
                ->latest('reserved_at')
                ->get();

        return view('reservations.index', compact('computers', 'reservations', 'isAdmin'));
    }

    public function storeEquipment(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Computer::create([...$data, 'status' => 'available']);

        return back()->with('success', 'Equipo registrado correctamente.');
    }

    public function updateEquipment(Request $request, Computer $computer)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $computer->update($data);

        return back()->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroyEquipment(Request $request, Computer $computer)
    {
        $this->authorizeAdmin($request);
        $computer->delete();

        return back()->with('success', 'Equipo eliminado correctamente.');
    }

    public function reserve(Request $request, Computer $computer)
    {
        if ($request->user()->role !== 'estudiante') {
            abort(403, 'Solo los estudiantes pueden reservar equipos.');
        }

        DB::transaction(function () use ($computer, $request) {
            $lockedComputer = Computer::whereKey($computer->id)->lockForUpdate()->firstOrFail();

            if ($lockedComputer->status !== 'available') {
                abort(422, 'El equipo ya no está disponible.');
            }

            ComputerReservation::create([
                'computer_id' => $lockedComputer->id,
                'user_id' => $request->user()->id,
                'reserved_at' => now(),
                'cancelled_at' => null,
            ]);

            $lockedComputer->update(['status' => 'reserved']);
        });

        return back()->with('success', 'Equipo reservado correctamente.');
    }

    public function cancel(Request $request, ComputerReservation $reservation)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && (int) $reservation->user_id !== (int) $user->id) {
            abort(403, 'No puedes cancelar una reservación ajena.');
        }

        DB::transaction(function () use ($reservation) {
            $lockedReservation = ComputerReservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($lockedReservation->cancelled_at !== null) {
                return;
            }

            $lockedReservation->update(['cancelled_at' => now()]);
            Computer::whereKey($lockedReservation->computer_id)
                ->lockForUpdate()
                ->firstOrFail()
                ->update(['status' => 'available']);
        });

        return back()->with('success', 'Reservación cancelada.');
    }

    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Solo el administrador puede gestionar equipos.');
        }
    }
}
