<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\User;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Define las acciones disponibles en la cabecera de la vista de usuario
     *
     * @return array Acciones configuradas para la cabecera
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('impersonate')
                ->label('Iniciar sesión como este usuario')
                ->icon('heroicon-o-identification')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Impersonar usuario')
                ->modalDescription('¿Estás seguro que deseas iniciar sesión como este usuario? Podrás volver a tu cuenta desde el panel de control.')
                ->modalSubmitActionLabel('Sí, iniciar sesión')
                // Verificamos si el usuario tiene permiso para impersonar
                ->visible(fn () =>
                    Auth::user()->can('impersonate_user') &&
                    Auth::user()->id() !== $this->record->id && // No permitir impersonarse a sí mismo
                    !session()->has('impersonated_by') // No permitir impersonación anidada
                )
                ->action(function () {
                    try {
                        // Guardamos el ID del usuario original para poder volver
                        session()->put('impersonated_by', Auth::id());

                        // Registramos la acción en logs
                        \Illuminate\Support\Facades\Log::info('Usuario ID:' . Auth::id() . ' está impersonando al usuario ID:' . $this->record->id);

                        // Iniciamos sesión como el usuario seleccionado
                        Auth::login($this->record);

                        // Notificación de éxito
                        $this->notify('success', 'Ahora estás viendo el sistema como ' . $this->record->name);

                        return redirect()->route('dashboard');
                    } catch (\Exception $e) {
                        // Manejo de errores
                        $this->notify('danger', 'Error al impersonar: ' . $e->getMessage());
                        return null;
                    }
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
