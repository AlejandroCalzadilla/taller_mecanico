<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    vehiculos: Object,
    clientes: Array,
    filters: Object,
    estados: Object,
});

const handleImageError = (vehiculo) => {
    vehiculo.foto_url = 'https://us.123rf.com/450wm/martialred/martialred1507/martialred150700661/42613290-landscape-photo-image-flat-icon-for-apps-and-websites.jpg';
};

const search = ref(props.filters.search || '');
const estado = ref(props.filters.estado || '');
const cliente_id = ref(props.filters.cliente_id || '');

// Búsqueda con debounce
watch([search, estado, cliente_id], debounce(([newSearch, newEstado, newClienteId]) => {
    router.get(route('admin.vehiculos.index'), {
        search: newSearch,
        estado: newEstado,
        cliente_id: newClienteId,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const resetFilters = () => {
    search.value = '';
    estado.value = '';
    cliente_id.value = '';
};

const cambiarEstado = (vehiculoId, nuevoEstado) => {
    if (confirm(`¿Estás seguro de cambiar el estado a ${nuevoEstado === 'activo' ? 'Activo' : 'Inactivo'}?`)) {
        router.patch(route('admin.vehiculos.update-status', vehiculoId), {
            estado: nuevoEstado
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['vehiculos'] });
            }
        });
    }
};

const eliminarVehiculo = (vehiculo) => {
    if (confirm(`¿Estás seguro de eliminar el vehículo ${vehiculo.marca} ${vehiculo.modelo} (${vehiculo.placa})?`)) {
        router.delete(route('admin.vehiculos.destroy', vehiculo.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['vehiculos'] });
            },
            onError: (errors) => {
                if (errors.error) {
                    alert(errors.error);
                }
            }
        });
    }
};
</script>

<template>

    <Head title="Gestión de Vehículos" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gestión de Vehículos</h1>
                    <p class="text-sm text-gray-600 mt-1">Administra todos los vehículos registrados en el sistema</p>
                </div>
                <Link :href="route('admin.vehiculos.create')"
                    class="ml-4 bg-taller-blue-dark hover:bg-taller-blue-light text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Estadísticas Rápidas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-4a1 1 0 00-.293-.707l-4-4A1 1 0 0015 4H3z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Total Vehículos</p>
                                <p class="text-xl font-semibold text-gray-900">{{ vehiculos.total }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Activos</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{vehiculos.data.filter(v => v.estado === 'activo').length}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Clientes Activos</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{new Set(vehiculos.data.map(v => v.cliente_id)).size}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Con Foto</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{vehiculos.data.filter(v => v.foto).length}}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros Mejorados -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Búsqueda -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Buscar
                            </label>
                            <input type="text" v-model="search" placeholder="Placa, marca, modelo, color..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark" />
                        </div>

                        <!-- Filtro por estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select v-model="estado"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark">
                                <option value="">Todos los estados</option>
                                <option v-for="(label, value) in estados" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Filtro por cliente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                            <select v-model="cliente_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark">
                                <option value="">Todos los clientes</option>
                                <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                    {{ cliente.nombre }}
                                </option>
                            </select>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex items-end space-x-2">
                            <button @click="resetFilters"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid de Vehículos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div v-if="vehiculos.data.length > 0"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
                        <div v-for="vehiculo in vehiculos.data" :key="vehiculo.id"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition duration-200">

                            <!-- Imagen del Vehículo -->
                            <div class="relative h-48 bg-gray-200">
    <img
        :src="vehiculo.foto_url"
        :alt="`${vehiculo.marca} ${vehiculo.modelo}`"
        class="w-full h-full object-cover transition duration-300 hover:scale-105"
        @error="handleImageError(vehiculo)"
    />
    <div class="absolute top-2 right-2 flex space-x-1">
        <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="{
                'bg-green-100 text-green-800': vehiculo.estado === 'activo',
                'bg-red-100 text-red-800': vehiculo.estado === 'inactivo'
            }">
            {{ vehiculo.estado === 'activo' ? 'Activo' : 'Inactivo' }}
        </span>
    </div>
</div>

                            <!-- Información del Vehículo -->
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ vehiculo.marca }} {{ vehiculo.modelo }}
                                    </h3>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                                        {{ vehiculo.anio }}
                                    </span>
                                </div>

                                <div class="space-y-2 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span class="font-medium">Placa:</span>
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">
                                            {{ vehiculo.placa }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">Color:</span>
                                        <span>{{ vehiculo.color }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">Kilometraje:</span>
                                        <span>{{ vehiculo.kilometraje?.toLocaleString() || '0' }} km</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">Cliente:</span>
                                        <span class="text-right">{{ vehiculo.cliente.nombre }}</span>
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="mt-4 flex justify-between items-center pt-4 border-t border-gray-200">
                                    <div class="flex space-x-2">
                                        <Link :href="route('admin.vehiculos.show', vehiculo.id)"
                                            class="text-blue-600 hover:text-blue-900 transition duration-200"
                                            title="Ver detalles">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        </Link>
                                        <Link :href="route('admin.vehiculos.edit', vehiculo.id)"
                                            class="text-green-600 hover:text-green-900 transition duration-200"
                                            title="Editar vehículo">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        </Link>
                                    </div>

                                    <div class="flex space-x-2">
                                        <button
                                            @click="cambiarEstado(vehiculo.id, vehiculo.estado === 'activo' ? 'inactivo' : 'activo')"
                                            class="text-gray-400 hover:text-gray-600 transition duration-200"
                                            :title="vehiculo.estado === 'activo' ? 'Desactivar vehículo' : 'Activar vehículo'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path v-if="vehiculo.estado === 'activo'" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                <path v-else stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </button>
                                        <button @click="eliminarVehiculo(vehiculo)"
                                            class="text-red-600 hover:text-red-900 transition duration-200"
                                            title="Eliminar vehículo">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay resultados -->
                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-4a1 1 0 00-.293-.707l-4-4A1 1 0 0015 4H3z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron vehículos</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Intenta ajustar los filtros de búsqueda o crear un nuevo vehículo.
                        </p>
                    </div>

                    <!-- Paginación -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                            <div class="text-sm text-gray-700">
                                Mostrando <span class="font-medium">{{ vehiculos.from }}</span> a
                                <span class="font-medium">{{ vehiculos.to }}</span> de
                                <span class="font-medium">{{ vehiculos.total }}</span> resultados
                            </div>
                            <div class="flex space-x-1">
                                <Link v-for="link in vehiculos.links" :key="link.label" :href="link.url || '#'" :class="{
                                    'bg-taller-blue-dark text-white': link.active,
                                    'text-gray-500 hover:bg-gray-50 hover:text-gray-700': !link.active && link.url,
                                    'text-gray-300 cursor-not-allowed': !link.url
                                }" class="px-3 py-2 rounded-md text-sm font-medium transition duration-200 border"
                                    v-html="link.label" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
