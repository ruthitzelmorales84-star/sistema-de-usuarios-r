<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AcademicAccessController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === 'admin';
    }

    private function isProfessorLocked(Request $request): bool
    {
        if ($this->isAdmin($request)) {
            return false;
        }

        $userRole = $request->user()?->role;
        $sessionRole = $request->session()->get('academic_access_role');

        return $userRole === 'profesor' || $sessionRole === 'profesor';
    }

    private function isStudentLocked(Request $request): bool
    {
        if ($this->isAdmin($request)) {
            return false;
        }

        $userRole = $request->user()?->role;

        return in_array($userRole, ['alumno', 'estudiante'], true);
    }

    public function chooseRole(Request $request)
    {
        return view('academic.role-select', [
            'isAdmin' => $this->isAdmin($request),
            'isProfessorLocked' => $this->isProfessorLocked($request),
            'isStudentLocked' => $this->isStudentLocked($request),
        ]);
    }

    public function enterStudent(Request $request)
    {
        if ($this->isProfessorLocked($request)) {
            abort(403, 'Como profesor no puedes cambiar al rol de alumno.');
        }

        $request->session()->put('academic_access_role', 'alumno');
        $request->session()->forget(['academic_2fa_code', 'academic_2fa_email', 'academic_2fa_verified']);

        return redirect()->route('academic.index');
    }

    public function showProfessorForm(Request $request)
    {
        if ($this->isStudentLocked($request)) {
            abort(403, 'Como alumno no puedes acceder al rol de profesor.');
        }

        return view('academic.professor-verify', [
            'email' => $request->session()->get('academic_2fa_email', auth()->user()->email ?? ''),
            'codeSent' => $request->session()->has('academic_2fa_code'),
        ]);
    }

    public function sendProfessorCode(Request $request)
    {
        if ($this->isStudentLocked($request)) {
            abort(403, 'Como alumno no puedes acceder al rol de profesor.');
        }
        if (! $this->isAdmin($request) && $request->user()?->role !== 'profesor') {
            abort(403, 'Solo profesores y administradores pueden verificar este rol.');
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $code = (string) random_int(100000, 999999);

        $request->session()->put('academic_2fa_email', $data['email']);
        $request->session()->put('academic_2fa_code', $code);
        $request->session()->put('academic_2fa_expires_at', now()->addMinutes(5)->timestamp);
        $request->session()->forget('academic_2fa_verified');

        Mail::raw(
            "Tu código de verificación para ingresar al Sistema Académico como Profesor es: {$code}",
            function ($message) use ($data) {
                $message->to($data['email'])
                    ->subject('Código de verificación - Sistema Académico');
            }
        );

        return redirect()
            ->route('academic.professor.form')
            ->with('status', 'Se envió un código de verificación a '.$data['email'].'.');
    }

    public function verifyProfessorCode(Request $request)
    {
        if ($this->isStudentLocked($request)) {
            abort(403, 'Como alumno no puedes acceder al rol de profesor.');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $expectedCode = $request->session()->get('academic_2fa_code');
        $expiresAt = (int) $request->session()->get('academic_2fa_expires_at', 0);

        if (! $expectedCode || $expiresAt < now()->timestamp || trim($data['code']) !== $expectedCode) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'El código ingresado no es válido o ya expiró.']);
        }

        $request->session()->put('academic_access_role', 'profesor');
        $request->session()->put('academic_2fa_verified', true);
        $request->session()->forget('academic_2fa_code');

        return redirect()->route('academic.index');
    }
}
