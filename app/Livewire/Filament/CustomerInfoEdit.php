<?php

namespace App\Livewire\Filament;

use App\Enums\ImageStoragePath;
use App\Services\Auth\AuthServiceInterface;
use App\Utils\HelperFunc;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Filament\Forms;

class CustomerInfoEdit extends Component implements HasForms
{
    use InteractsWithForms;

    public ?Authenticatable $auth;

    public ?array $data = [];

    public function boot(AuthServiceInterface $service)
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $data = $this->service->getInfoAuth();
        $this->form->fill([
            'profile_photo_url' => $data->profile_photo_url,
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'address' => $data->address,
            'introduce' => $data->introduce,
            'new_password' => '',
            'new_password_confirmation' => '',
            'bin_bank' => $data->creditCards?->first()?->bin_bank ?? null,
            'card_number' => $data->creditCards?->first()?->card_number ?? null,
            'card_holder_name' => $data->creditCards?->first()?->name ?? null,
            'contact_info' => $data->contact_info
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->fill()
            ->schema([


                Forms\Components\Fieldset::make('identification')
                    ->label("Profile information")
                    ->schema([
                        Forms\Components\FileUpload::make('profile_photo_url')
                            ->label('Profile')
                            ->avatar()
                            ->imageEditor()
                            ->storeFiles(false)
                            ->preserveFilenames()
                            ->reorderable()
                            ->columnSpanFull()
                            ->alignCenter()
                            ->helperText("Profile will be displayed on your profile. Please select the optimal size for best display.")
                            ->maxSize(5120) // 5MB = 5 * 1024 KB
                        ,
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->readonly()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone number')
                            ->placeholder("Example: 0987654321")
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_info.link_shopee')
                            ->label('Shoppe booth')
                            ->placeholder("Example: shoppe.vn/shop_name"),
                        Forms\Components\TextInput::make('contact_info.link_zalo')
                            ->label('Zalo phone number')
                            ->tel()
                            ->placeholder("Example: 0987654321"),
                        Forms\Components\TextInput::make('contact_info.link_facebook')
                            ->label('Facebook page')
                            ->placeholder("Example: facebook.com/id_facebook"),
                        Forms\Components\TextInput::make('contact_info.link_tiktok')
                            ->label('Gian hang tiktok')
                            ->placeholder("Example: tiktok.com/@id_tiktok"),
                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->placeholder("Example: 123 ABC Street, Ward 1, Hanoi City")
                            ->helperText("Your address will also be used for delivery or product pickup. Please provide the correct address.")
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('introduce')
                            ->label('Introduce myself')
                            ->placeholder("Example: I am a technology enthusiast, love to explore new things in life live.")
                            ->columnSpanFull()
                            ->maxLength(255),
                    ]),
                Forms\Components\Fieldset::make('Password')
                    ->label("Change Password")
                    ->schema([
                        Forms\Components\TextInput::make('new_password')
                            ->label('New Password')
                            ->required(fn($record) => !empty($record))
                            ->password()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->minLength(8)
                            ->validationMessages([
                                'minLength' => 'New password must be at least 8 characters long.',
                            ]),

                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Confirm new password')
                            ->password()
                            ->same('new_password')
                            ->validationMessages([
                                'same' => 'The confirmation password does not match the password new.',
                            ]),
                    ]),

                Forms\Components\Fieldset::make('payment')
                    ->label("Bank account information")
                    ->schema([
                        Forms\Components\Select::make('bin_bank')
                            ->label('Bank')
                            ->searchable()
                            ->required(fn(callable $get) => $get('card_number') || $get('card_holder_name'))
                            ->options(HelperFunc::getListBankOptions()),
                        Forms\Components\TextInput::make('card_number')
                            ->label('Bank account number')
                            ->numeric()
                            ->required(fn(callable $get) => $get('bin_bank') || $get('card_holder_name')),
                        Forms\Components\TextInput::make('card_holder_name')
                            ->label('Account owner')
                            ->required(fn(callable $get) => $get('bin_bank') || $get('card_number'))
                            ->reactive()
                            ->debounce(1000)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // 1. Remove accents 
                                    $state = HelperFunc::removeVietnameseTones($state);
                                    $newState = strtoupper($state);
                                    // Update the value on FE immediately 
                                    $set('card_holder_name', $newState);
                                }
                            })
                            ->dehydrateStateUsing(function (?string $state) {
                                if (!$state) return null;
                                $state = HelperFunc::removeVietnameseTones($state);
                                return strtoupper($state);
                            }),
                    ]),

            ])->statePath('data');
    }

    public function create(): void
    {
        $form = $this->form->getState();
        $result = $this->service->updateAuthUser($form);
        if ($result) {
            Notification::make()
                ->title('Success')
                ->body('Update information successfully!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failure')
                ->body('Update information failed!')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.filament.customer-info-edit');
    }
}
