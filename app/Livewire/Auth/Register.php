<?php

namespace App\Livewire\Auth;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Register extends Component
{
    public $name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public $invitation_token = '';

    public function mount()
    {
        $this->invitation_token = (string) request()->query('token', '');
        $this->email = (string) request()->query('email', '');
    }

    public function register()
    {
        // Anti-bruteforce des jetons d'invitation
        $throttleKey = 'register|'.request()->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('invitation_token', __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($throttleKey),
            ]));

            return;
        }
        RateLimiter::hit($throttleKey, 60);

        $this->email = strtolower(trim($this->email));

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'invitation_token' => ['required', 'string', 'max:64'],
        ]);

        $user = DB::transaction(function () {
            $invitation = Invitation::where('token', $this->invitation_token)
                ->whereNull('used_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->lockForUpdate()
                ->first();

            // Une invitation nominative ne peut servir qu'à l'adresse invitée
            if (! $invitation || ($invitation->email && strtolower($invitation->email) !== $this->email)) {
                return null;
            }

            // Consommation atomique : un même jeton ne peut créer qu'un compte
            $claimed = Invitation::whereKey($invitation->id)
                ->whereNull('used_at')
                ->update(['used_at' => now(), 'email' => $this->email]);

            if (! $claimed) {
                return null;
            }

            return User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
        });

        if (! $user) {
            $this->addError('invitation_token', __('app.Ce jeton d\'invitation est invalide, expiré ou a déjà été utilisé.'));

            return;
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);
        session()->regenerate();

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
