<?php

namespace App\Livewire\Filament;

use App\Services\Config\ConfigServiceInterface;
use Filament\Notifications\Notification;
use Livewire\Component;

class ConfigForm extends Component
{
    private ConfigServiceInterface $service;

    public array $config_value = [];

    public $configList;


    public function boot(ConfigServiceInterface $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->configList = $this->service->getAllConfig();
        foreach ($this->configList as $config) {
            $this->config_value[$config->config_key] = $config->config_value;
        }
    }

    public function updateConfig()
    {
        $result = $this->service->updateConfigs($this->config_value);
        if ($result) {
            Notification::make()
                ->title('Config update successful')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Config update failed')
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.filament.config-form');
    }
}
