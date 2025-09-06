<?php

namespace App\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\RegistrationResponse;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class OverrideRegistrationResponse  extends RegistrationResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        // Thêm thông báo vào session
        Notification::make()
            ->title('Account created successfully')
            ->body('Please check your email for verification. If you do not receive the email, please check your spam folder.')
            ->success()
            ->send();
        // Redirect tới trang login
        return redirect()->intended(Filament::getUrl());
    }
}
