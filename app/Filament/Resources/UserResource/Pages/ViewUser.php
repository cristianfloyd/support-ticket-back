<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('impersonate')
                ->label('Iniciar sesión como este usuario')
                ->icon('heroicon-o-identification')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->action(function () {
                    // Implementar lógica de impersonación
                    session()->put('impersonated_by', auth()->id());
                    auth()->login($this->record);

                    return redirect()->route('dashboard');
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserResource\Widgets\UserStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            UserResource\Widgets\UserActivityWidget::class,
        ];
    }
}
