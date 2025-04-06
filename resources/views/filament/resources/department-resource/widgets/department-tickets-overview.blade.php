<x-filament::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ isset($this->departmentId) ? 'Resumen de Tickets del Departamento' : 'Resumen de Tickets por Departamento' }}
        </x-slot>

        @if(isset($this->ticketsData['error']))
            <div class="p-4 text-sm text-red-600 bg-red-100 rounded-lg">
                {{ $this->ticketsData['error'] }}
            </div>
        @elseif(isset($this->departmentId) && isset($this->ticketsData['total']))
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="p-4 bg-white rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900">Total de Tickets</h3>
                    <p class="mt-2 text-3xl font-bold text-primary-600">{{ $this->ticketsData['total'] }}</p>
                </div>

                @if(count($this->ticketsData['byStatus']) > 0)
                    <div class="p-4 bg-white rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900">Por Estado</h3>
                        <div class="mt-2 space-y-2">
                            @foreach($this->ticketsData['byStatus'] as $status => $count)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">{{ $status }}</span>
                                    <span class="text-sm font-medium">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($this->ticketsData['byPriority']) > 0)
                    <div class="p-4 bg-white rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900">Por Prioridad</h3>
                        <div class="mt-2 space-y-2">
                            @foreach($this->ticketsData['byPriority'] as $priority => $count)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">{{ $priority }}</span>
                                    <span class="text-sm font-medium">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @elseif(isset($this->ticketsData['topDepartments']))
            <div class="p-4 bg-white rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900">Departamentos con más Tickets</h3>
                <div class="mt-4 space-y-3">
                    @foreach($this->ticketsData['topDepartments'] as $dept)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ $dept['name'] }}</span>
                            <span class="text-sm font-medium">{{ $dept['count'] }} tickets</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="p-4 text-sm text-gray-600 bg-gray-100 rounded-lg">
                No hay datos disponibles para mostrar.
            </div>
        @endif

        @if(isset($this->departmentId))
            <div class="mt-4">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.tickets.index', ['tableFilters[department_id][value]' => $this->departmentId]) }}"
                    color="primary"
                    size="sm"
                >
                    Ver todos los tickets de este departamento
                </x-filament::button>
            </div>
        @endif
    </x-filament::section>
</x-filament::widget>
