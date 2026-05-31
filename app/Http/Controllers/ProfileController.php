<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Controller de perfil do usuário (ST-10).
 * Disponível para qualquer perfil autenticado (níveis 1–5).
 * Permite editar nome, e-mail, senha e avatar.
 */
class ProfileController extends Controller
{
    // =========================================================
    // GET /perfil — exibe formulário de edição
    // =========================================================

    public function edit(): View
    {
        return view('content.pages.perfil', [
            'user' => Auth::user(),
        ]);
    }

    // =========================================================
    // PUT /perfil — atualiza nome, e-mail e senha
    // =========================================================

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $dados = ['name' => $request->name, 'email' => $request->email];

        // Atualiza senha apenas se new_password foi enviado
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Senha atual incorreta.'])
                    ->withInput();
            }
            $dados['password'] = Hash::make($request->new_password);
        }

        $user->update($dados);

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }

    // =========================================================
    // POST /perfil/avatar — faz upload do avatar
    // =========================================================

    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Selecione uma imagem.',
            'avatar.image'    => 'O arquivo deve ser uma imagem.',
            'avatar.mimes'    => 'Formatos aceitos: JPG, PNG, WebP.',
            'avatar.max'      => 'A imagem deve ter no máximo 2 MB.',
        ]);

        $user = Auth::user();

        // Remove avatar anterior para não acumular arquivos órfãos
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Salva como avatars/{user_id}.{ext} para facilitar lookup direto
        $ext  = $request->file('avatar')->getClientOriginalExtension();
        $path = $request->file('avatar')->storeAs(
            'avatars',
            $user->id . '.' . $ext,
            'public'
        );

        $user->update(['avatar' => $path]);

        return back()->with('success', 'Avatar atualizado.');
    }

    // =========================================================
    // DELETE /perfil/avatar — remove o avatar
    // =========================================================

    public function deleteAvatar(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Avatar removido.');
    }
}
