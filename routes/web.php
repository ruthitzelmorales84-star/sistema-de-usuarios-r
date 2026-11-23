<?php

use App\Http\Controllers\AcademicAccessController;
use App\Http\Controllers\AcademicRecordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComputerReservationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TicTacToeController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/sistema-administracion', function (Request $request) {
    if (! Auth::check()) {
        return redirect()->route('login', ['intended' => $request->fullUrl()]);
    }

    return view('modules.admin');
})->middleware('role:admin')->name('modules.admin');

Route::middleware(['auth', 'role:jugador,admin'])->prefix('juego-multijugador')->name('multiplayer.')->group(function () {
    Route::get('/', [TicTacToeController::class, 'index'])->name('index');
    Route::post('/crear', [TicTacToeController::class, 'create'])->middleware('role:jugador,admin')->name('create');
    Route::post('/{game}/unirse', [TicTacToeController::class, 'join'])->middleware('role:jugador,admin')->name('join');
    Route::get('/{game}', [TicTacToeController::class, 'show'])->name('show');
    Route::get('/{game}/estado', [TicTacToeController::class, 'state'])->name('state');
    Route::post('/{game}/mover', [TicTacToeController::class, 'move'])->middleware('role:jugador,admin')->name('move');
    Route::post('/{game}/abandonar', [TicTacToeController::class, 'abandon'])->middleware('role:jugador,admin')->name('abandon');
    Route::post('/{game}/cancelar', [TicTacToeController::class, 'cancel'])->middleware('role:admin')->name('cancel');

    Route::post('/usuarios', [TicTacToeController::class, 'storeUser'])->name('users.store');
    Route::put('/usuarios/{user}', [TicTacToeController::class, 'updateUser'])->name('users.update');
    Route::delete('/usuarios/{user}', [TicTacToeController::class, 'destroyUser'])->name('users.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/sistema-academico', [AcademicAccessController::class, 'chooseRole'])
        ->middleware('role:admin,profesor,estudiante')
        ->name('modules.academic');
    Route::post('/sistema-academico/estudiante', [AcademicAccessController::class, 'enterStudent'])
        ->middleware('role:admin,estudiante')
        ->name('academic.student.enter');
    Route::get('/sistema-academico/profesor', [AcademicAccessController::class, 'showProfessorForm'])
        ->middleware('role:admin,profesor')
        ->name('academic.professor.form');
    Route::post('/sistema-academico/profesor/enviar-codigo', [AcademicAccessController::class, 'sendProfessorCode'])
        ->middleware('role:admin,profesor')
        ->name('academic.professor.send');
    Route::post('/sistema-academico/profesor/verificar', [AcademicAccessController::class, 'verifyProfessorCode'])
        ->middleware('role:admin,profesor')
        ->name('academic.professor.verify');
});

Route::middleware(['auth', 'role:empleado,admin'])->prefix('productos')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/{product}/comprar', [ProductController::class, 'buy'])->name('buy');

    Route::middleware('role:admin')->group(function () {
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/verificacion-admin', [AuthController::class, 'showTwoFactorChallenge'])->name('admin.2fa.challenge');
Route::post('/verificacion-admin', [AuthController::class, 'verifyTwoFactor'])->name('admin.2fa.verify');

Route::middleware('auth')->prefix('reservaciones')->name('reservations.')->group(function () {
    Route::get('/', [ComputerReservationController::class, 'index'])
        ->middleware('role:admin,estudiante')
        ->name('index');
    Route::middleware('role:admin')->group(function () {
        Route::post('/equipos', [ComputerReservationController::class, 'storeEquipment'])->name('equipment.store');
        Route::put('/equipos/{computer}', [ComputerReservationController::class, 'updateEquipment'])->name('equipment.update');
        Route::delete('/equipos/{computer}', [ComputerReservationController::class, 'destroyEquipment'])->name('equipment.destroy');
    });
    Route::middleware('role:estudiante')->post('/equipos/{computer}/reservar', [ComputerReservationController::class, 'reserve'])->name('reserve');
    Route::post('/{reservation}/cancelar', [ComputerReservationController::class, 'cancel'])
        ->middleware('role:admin,estudiante')
        ->name('cancel');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboards.admin');
    })->name('dashboard');
});

Route::middleware('auth')->get('/dashboard', function () {
    return 'Panel general';
})->name('dashboard');

Route::get('/sistema-usuarios', function (Request $request) {
    if (! Auth::check()) {
        return redirect()->route('login', ['intended' => $request->fullUrl()]);
    }

    return view('modules.users');
})->middleware(['auth', 'role:empleado,admin'])->name('modules.users');

Route::get('/users', function (Request $request) {
    if (! Auth::check()) {
        return redirect()->route('login', ['intended' => $request->fullUrl()]);
    }

    return app(UserController::class)->index($request);
})->middleware(['auth', 'role:empleado,admin'])->name('users.index');

Route::get('/users/create', function (Request $request) {
    if (! Auth::check()) {
        return redirect()->route('login', ['intended' => $request->fullUrl()]);
    }

    return app(UserController::class)->create($request);
})->middleware(['auth', 'role:empleado,admin'])->name('users.create');

Route::post('/users', [UserController::class, 'store'])->middleware(['auth', 'role:empleado,admin'])->name('users.store');

Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware(['auth', 'role:empleado,admin'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->middleware(['auth', 'role:empleado,admin'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware(['auth', 'role:admin'])->name('users.destroy');
Route::delete('/users/{id}/force', [UserController::class, 'forceDestroy'])->middleware(['auth', 'role:admin'])->name('users.force-destroy');
Route::post('/users/{id}/restore', [UserController::class, 'restore'])->middleware(['auth', 'role:admin'])->name('users.restore');

Route::get('/users/{user}', function (Request $request, User $user) {
    if (! Auth::check()) {
        return redirect()->route('login', ['intended' => $request->fullUrl()]);
    }

    return app(UserController::class)->show($request, $user);
})->name('users.show');

Route::middleware('auth')->get('/academic', function (Request $request) {
    $sessionRole = $request->session()->get('academic_access_role');
    $userRole = $request->user()?->role;

    if ($userRole === 'admin') {
        return app(AcademicRecordController::class)->index($request);
    }

    if ($sessionRole === 'profesor' && $request->session()->get('academic_2fa_verified')) {
        return app(AcademicRecordController::class)->index($request);
    }

    if ($sessionRole === 'estudiante' || in_array($userRole, ['profesor', 'estudiante'], true)) {
        return app(AcademicRecordController::class)->index($request);
    }

    return redirect()->route('modules.academic');
})->name('academic.index');

Route::middleware(['auth', 'role:profesor,admin'])->prefix('academic')->name('academic.')->group(function () {
    Route::get('/create', [AcademicRecordController::class, 'create'])->name('create');
    Route::post('/', [AcademicRecordController::class, 'store'])->name('store');
    Route::get('/{academicRecord}/edit', [AcademicRecordController::class, 'edit'])->name('edit');
    Route::put('/{academicRecord}', [AcademicRecordController::class, 'update'])->name('update');
    Route::delete('/{academicRecord}', [AcademicRecordController::class, 'destroy'])->name('destroy');
});
