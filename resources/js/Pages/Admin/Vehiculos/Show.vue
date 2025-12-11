<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    vehiculo: Object,
    estadisticas: Object,
});

// Obtener flash messages de forma segura
const page = usePage();
const flash = computed(() => page.props.flash || {});

const eliminarVehiculo = () => {
    if (confirm(`¿Estás seguro de eliminar el vehículo ${props.vehiculo.marca} ${props.vehiculo.modelo} (${props.vehiculo.placa})?`)) {
        router.delete(route('admin.vehiculos.destroy', props.vehiculo.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('admin.vehiculos.index'));
            },
            onError: (errors) => {
                if (errors.error) {
                    alert(errors.error);
                }
            }
        });
    }
};

const cambiarEstado = (nuevoEstado) => {
    if (confirm(`¿Estás seguro de cambiar el estado a ${nuevoEstado === 'activo' ? 'Activo' : 'Inactivo'}?`)) {
        router.patch(route('admin.vehiculos.update-status', props.vehiculo.id), {
            estado: nuevoEstado
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            }
        });
    }
};
</script>

<template>
    <Head :title="`${vehiculo.marca} ${vehiculo.modelo} - Detalles`" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        {{ vehiculo.marca }} {{ vehiculo.modelo }}
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Detalles completos del vehículo - {{ vehiculo.placa }}
                    </p>
                </div>
                <div class="flex ml-4 space-x-2">
                    <Link :href="route('admin.vehiculos.edit', vehiculo.id)"
                        class="bg-taller-blue-dark hover:bg-taller-blue-light text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </Link>
                    <Link :href="route('admin.vehiculos.index')"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Volver
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Alertas - Versión corregida -->
                <div v-if="flash.success" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ flash.success }}
                </div>

                <div v-if="flash.error" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ flash.error }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna izquierda - Información principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Tarjeta de información del vehículo -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Información del Vehículo</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Imagen del vehículo -->
                                    <div class="md:col-span-2">
                                        <div class="flex flex-col md:flex-row gap-6">
                                            <div class="flex-shrink-0">
                                                <div class="w-64 h-48 bg-gray-200 rounded-lg overflow-hidden">
                                                    <img
                                                        v-if="vehiculo.foto_url"
                                                        :src="vehiculo.foto_url"
                                                        :alt="`${vehiculo.marca} ${vehiculo.modelo}`"
                                                        class="w-full h-full object-cover"
                                                    />
                                                    <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-4a1 1 0 00-.293-.707l-4-4A1 1 0 0015 4H3z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Marca</label>
                                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.marca }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Modelo</label>
                                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.modelo }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Año</label>
                                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.anio }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Color</label>
                                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.color }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Placa</label>
                                                        <p class="mt-1 text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ vehiculo.placa }}</p>
                                                    </div>
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Kilometraje</label>
                                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.kilometraje?.toLocaleString() || '0' }} km</p>
                                                    </div>
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-500">Estado</label>
                                                        <div class="mt-1">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                                :class="{
                                                                    'bg-green-100 text-green-800': vehiculo.estado === 'activo',
                                                                    'bg-red-100 text-red-800': vehiculo.estado === 'inactivo'
                                                                }">
                                                                {{ vehiculo.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del cliente -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Información del Cliente</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Nombre</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.cliente.nombre }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Email</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.cliente.email }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Teléfono</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.cliente.telefono || 'No especificado' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Dirección</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ vehiculo.cliente.direccion || 'No especificada' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div v-if="vehiculo.observaciones" class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Observaciones</h2>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ vehiculo.observaciones }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha - Estadísticas y acciones -->
                    <div class="space-y-6">
                        <!-- Estadísticas -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Estadísticas</h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Total de Citas</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ estadisticas.total_citas }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Citas Pendientes</span>
                                    <span class="text-sm font-semibold text-red-600">{{ estadisticas.citas_pendientes }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">Última Cita</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ estadisticas.ultima_cita || 'Nunca' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones rápidas -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Acciones</h2>
                            </div>
                            <div class="p-6 space-y-3">
                                <button
                                    @click="cambiarEstado(vehiculo.estado === 'activo' ? 'inactivo' : 'activo')"
                                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center justify-center"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="vehiculo.estado === 'activo'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    {{ vehiculo.estado === 'activo' ? 'Desactivar' : 'Activar' }} Vehículo
                                </button>

                                <Link
                                    :href="route('admin.citas.create', { vehiculo_id: vehiculo.id })"
                                    class="w-full bg-taller-blue-dark hover:bg-taller-blue-light text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center justify-center"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Crear Cita
                                </Link>

                                <button
                                    @click="eliminarVehiculo"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center justify-center"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar Vehículo
                                </button>
                            </div>
                        </div>

                        <!-- Información del sistema -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Información del Sistema</h2>
                            </div>
                            <div class="p-6 space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Creado el:</span>
                                    <span class="text-gray-900">{{ new Date(vehiculo.created_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Actualizado el:</span>
                                    <span class="text-gray-900">{{ new Date(vehiculo.updated_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">ID:</span>
                                    <span class="text-gray-900 font-mono">{{ vehiculo.id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
