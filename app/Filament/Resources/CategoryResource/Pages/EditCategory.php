<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Category',
            '' => 'Edit category',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Permanently delete')
                ->requiresConfirmation()
                ->modalHeading('Confirm permanent delete')
                ->modalDescription('Are you sure you want to delete this category permanently? This action cannot be undone.')
                ->action(function (Category $record) {
                    if ($record->children()->exists()) {
                        Notification::make()
                            ->title('Error')
                            ->body('Category cannot be deleted because it has children.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if ($record->products()->exists()) {
                        Notification::make()
                            ->title('Error')
                            ->body('Category cannot be deleted because it has related products.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $record->forceDelete();
                    Notification::make()
                        ->title('Success')
                        ->body('The category has been permanently deleted successfully!')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (Category::where('name', $data['name'])->where('id', '!=', $this->record->id)->exists()) {
            Notification::make()
                ->title('Error')
                ->body('The category name already exists.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
