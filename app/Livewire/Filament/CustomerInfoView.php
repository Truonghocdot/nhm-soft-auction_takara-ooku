<?php

namespace App\Livewire\Filament;

use App\Services\Auth\AuthServiceInterface;
use App\Utils\HelperFunc;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class CustomerInfoView extends Component implements HasForms, HasInfolists
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    private AuthServiceInterface $service;

    public ?Authenticatable $auth;

    public function boot(AuthServiceInterface $service)
    {
        $this->service = $service;
    }
    public function mount(): void
    {
        $this->auth = $this->service->getInfoAuth();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->auth)
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('name')
                                            ->label('Username'),
                                        Components\TextEntry::make('email')
                                            ->label('Email'),
                                        Components\TextEntry::make('phone')
                                            ->label('Phone number')
                                            ->default("Not updated"),
                                        Components\TextEntry::make('created_at')
                                            ->label('Account creation date')
                                            ->dateTime("d/m/Y H:i"),
                                        Components\TextEntry::make('contact_info.link_facebook')
                                            ->label('Facebook page')
                                            ->default("Not updated"),
                                        Components\TextEntry::make('contact_info.link_tiktok')
                                            ->label('Tiktok store')
                                            ->default("Not updated"),
                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('membership')->label('Membership')
                                            ->formatStateUsing(fn($record): string => $record->activeMemberships->count() > 0 ? 'Membership' : 'Not registered')
                                            ->badge()
                                            ->color(fn($record): string => $record->activeMemberships->count() > 0 ? 'success' : 'danger'),
                                        Components\TextEntry::make('address')
                                            ->label('Address')
                                            ->default("Not updated"),
                                        Components\TextEntry::make('introduce')
                                            ->label('Introduce yourself')
                                            ->default("Not updated"),
                                        Components\TextEntry::make('contact_info.link_shopee')
                                            ->label('Shopee booth')
                                            ->default("Not updated"),
                                        Components\TextEntry::make('contact_info.link_zalo')
                                            ->label('Zalo phone number')
                                            ->default("Not updated"),
                                    ]),
                                ]),
                            Components\ImageEntry::make('profile_photo_path')
                                ->label('Image')
                                ->hiddenLabel()
                                ->getStateUsing(fn($record) => HelperFunc::generateURLFilePath($record->profile_photo_path))
                                ->grow(false),
                        ])->from('lg'),
                    ]),
            ]);
    }


    public function render()
    {
        return view('livewire.filament.customer-info-view');
    }
}
