<?php

namespace App\Http\Controllers;

use App\Services\Auth\AuthServiceInterface;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function verify($id, $hash)
    {
        $check = Auth::check();
        if (!$check) {
            Auth::logout();
            session()->flush();
        }
        $result = $this->authService->verifyEmailUser($id, $hash);
        if ($result) {
            Notification::make()
                ->title('Account Verification Successful')
                ->body("Please log in with the account you have authenticated")
                ->success()
                ->send();
            // Redirect to login page
        } else {
            Notification::make()
                ->title('Account Verification Failed')
                ->body("Please contact the administrator to resolve the issue")
                ->success()
                ->send();
        }
        return redirect()->intended(Filament::getUrl());
    }
}
