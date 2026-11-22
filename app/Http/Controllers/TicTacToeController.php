<?php

namespace App\Http\Controllers;

use App\Models\TicTacToeGame;
use App\Models\TicTacToeMove;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TicTacToeController extends Controller
{
    private function isAdmin(?User $user): bool
    {
        return $user?->role === 'admin';
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);

        $availablePlayers = User::query()
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        $availableGames = TicTacToeGame::with(['playerX', 'playerO'])
            ->where('status', 'waiting')
            ->where('player_x_id', '!=', $user->id)
            ->latest()
            ->get();

        $activeGames = TicTacToeGame::with(['playerX', 'playerO', 'currentTurnUser'])
            ->whereIn('status', ['waiting', 'playing'])
            ->where(function ($q) use ($user, $isAdmin) {
                if (! $isAdmin) {
                    $q->where('player_x_id', $user->id)
                        ->orWhere('player_o_id', $user->id);
                }
            })
            ->latest()
            ->get();

        $historyGames = TicTacToeGame::with(['playerX', 'playerO', 'winner'])
            ->whereIn('status', ['finished', 'draw', 'abandoned', 'cancelled'])
            ->where(function ($q) use ($user, $isAdmin) {
                if (! $isAdmin) {
                    $q->where('player_x_id', $user->id)
                        ->orWhere('player_o_id', $user->id);
                }
            })
            ->latest()
            ->get();

        $allUsers = $isAdmin ? User::orderBy('id', 'desc')->get() : collect();
        $allGames = $isAdmin ? TicTacToeGame::with(['playerX', 'playerO', 'winner', 'currentTurnUser'])->latest()->get() : collect();

        return view('multiplayer.index', compact(
            'user',
            'isAdmin',
            'availablePlayers',
            'availableGames',
            'activeGames',
            'historyGames',
            'allUsers',
            'allGames'
        ));
    }

    public function create(Request $request)
    {
        $game = TicTacToeGame::create([
            'player_x_id' => $request->user()->id,
            'player_o_id' => null,
            'current_turn_user_id' => $request->user()->id,
            'winner_id' => null,
            'status' => 'waiting',
            'board' => array_fill(0, 9, null),
        ]);

        return redirect()->route('multiplayer.show', $game)->with('success', 'Partida creada. Eres el jugador X.');
    }

    public function join(Request $request, TicTacToeGame $game)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($game, $userId) {
            $lockedGame = TicTacToeGame::where('id', $game->id)->lockForUpdate()->firstOrFail();

            if ($lockedGame->status !== 'waiting') {
                abort(422, 'La partida no está disponible para unirse.');
            }

            if ((int) $lockedGame->player_x_id === (int) $userId) {
                abort(422, 'No puedes unirte a tu propia partida.');
            }

            $lockedGame->update([
                'player_o_id' => $userId,
                'status' => 'playing',
                'current_turn_user_id' => $lockedGame->player_x_id,
            ]);
        });

        return redirect()->route('multiplayer.show', $game)->with('success', 'Te uniste a la partida. Eres el jugador O.');
    }

    public function show(Request $request, TicTacToeGame $game)
    {
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);
        $isParticipant = in_array($user->id, [$game->player_x_id, $game->player_o_id], true);

        if (! $isAdmin && ! $isParticipant) {
            abort(403, 'No tienes permiso para ver esta partida.');
        }

        $game->load(['playerX', 'playerO', 'currentTurnUser', 'winner', 'moves.user']);

        return view('multiplayer.show', compact('game', 'user', 'isAdmin', 'isParticipant'));
    }

    public function state(Request $request, TicTacToeGame $game)
    {
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);
        $isParticipant = in_array($user->id, [$game->player_x_id, $game->player_o_id], true);

        if (! $isAdmin && ! $isParticipant) {
            abort(403);
        }

        $game->load(['playerX', 'playerO', 'currentTurnUser', 'winner']);

        return response()->json([
            'id' => $game->id,
            'status' => $game->status,
            'board' => $game->board,
            'current_turn_user_id' => $game->current_turn_user_id,
            'current_turn_name' => $game->currentTurnUser?->name,
            'winner_id' => $game->winner_id,
            'winner_name' => $game->winner?->name,
            'player_x_name' => $game->playerX?->name,
            'player_o_name' => $game->playerO?->name,
        ]);
    }

    public function move(Request $request, TicTacToeGame $game)
    {
        $data = $request->validate([
            'position' => ['required', 'integer', 'min:0', 'max:8'],
        ]);

        $userId = $request->user()->id;
        $position = (int) $data['position'];

        $result = DB::transaction(function () use ($game, $userId, $position) {
            $lockedGame = TicTacToeGame::where('id', $game->id)->lockForUpdate()->firstOrFail();

            if ($lockedGame->status !== 'playing') {
                return ['ok' => false, 'message' => 'La partida no está activa.'];
            }

            if ((int) $lockedGame->current_turn_user_id !== (int) $userId) {
                return ['ok' => false, 'message' => 'No es tu turno.'];
            }

            $symbol = null;
            $nextTurnUserId = null;

            if ((int) $lockedGame->player_x_id === (int) $userId) {
                $symbol = 'X';
                $nextTurnUserId = $lockedGame->player_o_id;
            } elseif ((int) $lockedGame->player_o_id === (int) $userId) {
                $symbol = 'O';
                $nextTurnUserId = $lockedGame->player_x_id;
            } else {
                return ['ok' => false, 'message' => 'No participas en esta partida.'];
            }

            $board = $lockedGame->board ?? array_fill(0, 9, null);

            if (! empty($board[$position])) {
                return ['ok' => false, 'message' => 'Esa casilla ya está ocupada.'];
            }

            $board[$position] = $symbol;

            TicTacToeMove::create([
                'game_id' => $lockedGame->id,
                'user_id' => $userId,
                'symbol' => $symbol,
                'position' => $position,
            ]);

            $winnerSymbol = $this->checkWinner($board);

            if ($winnerSymbol !== null) {
                $lockedGame->update([
                    'board' => $board,
                    'status' => 'finished',
                    'winner_id' => $userId,
                    'current_turn_user_id' => null,
                ]);
            } elseif (! in_array(null, $board, true)) {
                $lockedGame->update([
                    'board' => $board,
                    'status' => 'draw',
                    'winner_id' => null,
                    'current_turn_user_id' => null,
                ]);
            } else {
                $lockedGame->update([
                    'board' => $board,
                    'current_turn_user_id' => $nextTurnUserId,
                ]);
            }

            return ['ok' => true, 'message' => 'Movimiento registrado.'];
        });

        if (! $result['ok']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']], 422);
            }

            return back()->withErrors(['move' => $result['message']]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']]);
        }

        return redirect()->route('multiplayer.show', $game);
    }

    public function abandon(Request $request, TicTacToeGame $game)
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($game, $userId) {
            $lockedGame = TicTacToeGame::where('id', $game->id)->lockForUpdate()->firstOrFail();

            if (! in_array($userId, [$lockedGame->player_x_id, $lockedGame->player_o_id], true)) {
                abort(403);
            }

            if (! in_array($lockedGame->status, ['waiting', 'playing'], true)) {
                return;
            }

            $winnerId = null;
            if ($lockedGame->status === 'playing') {
                $winnerId = (int) $lockedGame->player_x_id === (int) $userId
                    ? $lockedGame->player_o_id
                    : $lockedGame->player_x_id;
            }

            $lockedGame->update([
                'status' => 'abandoned',
                'winner_id' => $winnerId,
                'current_turn_user_id' => null,
            ]);
        });

        return redirect()->route('multiplayer.index')->with('success', 'Has abandonado la partida.');
    }

    public function cancel(Request $request, TicTacToeGame $game)
    {
        if (! $this->isAdmin($request->user())) {
            abort(403, 'Solo el administrador puede cancelar partidas.');
        }

        $game->update([
            'status' => 'cancelled',
            'current_turn_user_id' => null,
        ]);

        return back()->with('success', 'Partida cancelada por el administrador.');
    }

    public function storeUser(Request $request)
    {
        if (! $this->isAdmin($request->user())) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'jugador'])],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function updateUser(Request $request, User $user)
    {
        if (! $this->isAdmin($request->user())) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'jugador'])],
        ]);

        $user->update($data);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroyUser(Request $request, User $user)
    {
        if (! $this->isAdmin($request->user())) {
            abort(403);
        }

        if ((int) Auth::id() === (int) $user->id) {
            return back()->withErrors(['user' => 'No puedes eliminar tu propio usuario activo.']);
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    private function checkWinner(array $board): ?string
    {
        $lines = [
            [0, 1, 2],
            [3, 4, 5],
            [6, 7, 8],
            [0, 3, 6],
            [1, 4, 7],
            [2, 5, 8],
            [0, 4, 8],
            [2, 4, 6],
        ];

        foreach ($lines as [$a, $b, $c]) {
            if (! empty($board[$a]) && $board[$a] === $board[$b] && $board[$a] === $board[$c]) {
                return $board[$a];
            }
        }

        return null;
    }
}