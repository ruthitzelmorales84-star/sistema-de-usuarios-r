<?php

namespace App\Http\Controllers;

use App\Models\AcademicRecord;
use App\Models\User;
use Illuminate\Http\Request;

class AcademicRecordController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activeRole = $this->activeAcademicRole($request);

        if ($activeRole === null) {
            return redirect()->route('modules.academic');
        }

        $sort = $request->input('sort', 'student_name');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSorts = [
            'student_name' => 'student_name',
            'grade' => 'grade',
            'absences' => 'absences',
        ];

        $orderColumn = $allowedSorts[$sort] ?? 'student_name';

        $query = AcademicRecord::query();

        if ($activeRole === 'estudiante') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user?->id)
                    ->orWhere('student_email', $user?->email)
                    ->orWhere('student_name', $user?->name);
            });
        } elseif ($activeRole !== 'profesor') {
            abort(403, 'No tienes permisos para acceder al sistema académico.');
        }

        $records = $query->orderBy($orderColumn, $direction)->get();

        return view('academic.index', compact('records', 'sort', 'direction', 'activeRole'));
    }

    public function create(Request $request)
    {
        $this->ensureProfessor($request);

        return view('academic.create');
    }

    public function store(Request $request)
    {
        $this->ensureProfessor($request);

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'student_email' => ['nullable', 'email', 'max:255'],
            'absences' => ['required', 'integer', 'min:0', 'max:100'],
            'grade' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);

        $linkedUser = null;
        if (! empty($validated['student_email'])) {
            $linkedUser = User::where('email', $validated['student_email'])->first();
        }

        AcademicRecord::create([
            'user_id' => $linkedUser?->id,
            'student_name' => $validated['student_name'],
            'student_email' => $validated['student_email'] ?? null,
            'absences' => $validated['absences'],
            'grade' => $validated['grade'],
        ]);

        return redirect()->route('academic.index')->with('success', 'Registro académico guardado correctamente.');
    }

    public function edit(Request $request, AcademicRecord $academicRecord)
    {
        $this->ensureProfessor($request);

        return view('academic.edit', compact('academicRecord'));
    }

    public function update(Request $request, AcademicRecord $academicRecord)
    {
        $this->ensureProfessor($request);

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'student_email' => ['nullable', 'email', 'max:255'],
            'absences' => ['required', 'integer', 'min:0', 'max:100'],
            'grade' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);

        $linkedUser = null;
        if (! empty($validated['student_email'])) {
            $linkedUser = User::where('email', $validated['student_email'])->first();
        }

        $academicRecord->update([
            'user_id' => $linkedUser?->id,
            'student_name' => $validated['student_name'],
            'student_email' => $validated['student_email'] ?? null,
            'absences' => $validated['absences'],
            'grade' => $validated['grade'],
        ]);

        return redirect()->route('academic.index')->with('success', 'Registro académico actualizado correctamente.');
    }

    public function destroy(Request $request, AcademicRecord $academicRecord)
    {
        $this->ensureProfessor($request);

        $academicRecord->delete();

        return redirect()->route('academic.index')->with('success', 'Registro académico eliminado.');
    }

    private function activeAcademicRole(Request $request): ?string
    {
        $user = $request->user();
        $userRole = $user?->role;
        $sessionRole = $request->session()->get('academic_access_role');
        $profVerified = (bool) $request->session()->get('academic_2fa_verified', false);

        if ($userRole === 'admin') {
            if (in_array($sessionRole, ['profesor', 'estudiante'], true)) {
                return $sessionRole;
            }

            return 'profesor';
        }

        if ($sessionRole === 'profesor') {
            if (! $profVerified) {
                return null;
            }

            if ($userRole === 'estudiante') {
                return null;
            }

            return 'profesor';
        }

        if ($sessionRole === 'estudiante') {
            if ($userRole === 'estudiante' || $userRole === null) {
                return $sessionRole;
            }
        }

        if ($userRole === 'estudiante') {
            return $userRole;
        }

        return null;
    }

    private function ensureProfessor(Request $request): void
    {
        if ($this->activeAcademicRole($request) !== 'profesor') {
            abort(403, 'Solo el rol profesor puede realizar esta acción.');
        }
    }
}
