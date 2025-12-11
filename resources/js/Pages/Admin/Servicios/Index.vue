<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    servicios: Object,
    filters: Object,
    tipos: Object,
    estados: Object,
});

const search = ref(props.filters.search || '');
const tipo = ref(props.filters.tipo || '');
const estado = ref(props.filters.estado || '');

// Búsqueda con debounce
watch([search, tipo, estado], debounce(([newSearch, newTipo, newEstado]) => {
    router.get(route('admin.servicios.index'), {
        search: newSearch,
        tipo: newTipo,
        estado: newEstado,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const resetFilters = () => {
    search.value = '';
    tipo.value = '';
    estado.value = '';
};


const cambiarEstado = (servicioId, nuevoEstado) => {
    if (confirm(`¿Estás seguro de cambiar el estado a ${nuevoEstado === 'activo' ? 'Activo' : 'Inactivo'}?`)) {
        // Solución temporal: usar la ruta de update normal
        router.put(route('admin.servicios.status', servicioId), {
            estado: nuevoEstado,
            _method: 'PUT'
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['servicios'] });
            }
        });
    }
};

const eliminarServicio = (servicio) => {
    if (confirm(`¿Estás seguro de eliminar el servicio "${servicio.nombre}"?`)) {
        router.delete(route('admin.servicios.destroy', servicio.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['servicios'] });
            },
            onError: (errors) => {
                if (errors.error) {
                    alert(errors.error);
                }
            }
        });
    }
};

const getTipoBadgeClass = (tipo) => {
    const classes = {
        diagnostico: 'bg-purple-100 text-purple-800',
        mantenimiento: 'bg-blue-100 text-blue-800',
        reparacion: 'bg-orange-100 text-orange-800',
    };
    return classes[tipo] || 'bg-gray-100 text-gray-800';
};

const getEstadoBadgeClass = (estado) => {
    return estado === 'activo'
        ? 'bg-green-100 text-green-800'
        : 'bg-red-100 text-red-800';
};
</script>

<template>
    <Head title="Gestión de Servicios" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gestión de Servicios</h1>
                    <p class="text-sm text-gray-600 mt-1">Administra todos los servicios del taller</p>
                </div>
                <Link :href="route('admin.servicios.create')"
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
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Total Servicios</p>
                                <p class="text-xl font-semibold text-gray-900">{{ servicios.total }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Activos</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ servicios.data.filter(s => s.estado === 'activo').length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Diagnósticos</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ servicios.data.filter(s => s.tipo === 'diagnostico').length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Reparaciones</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ servicios.data.filter(s => s.tipo === 'reparacion').length }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Búsqueda -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Buscar
                            </label>
                            <input type="text" v-model="search" placeholder="Nombre, descripción, tipo..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark" />
                        </div>

                        <!-- Filtro por tipo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                            <select v-model="tipo"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark">
                                <option value="">Todos los tipos</option>
                                <option v-for="(label, value) in tipos" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
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

                        <!-- Botones de acción -->
                        <div class="flex items-end space-x-2">
                            <button @click="resetFilters"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid de Servicios -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div v-if="servicios.data.length > 0" class="divide-y divide-gray-200">
                        <div v-for="servicio in servicios.data" :key="servicio.id" class="p-6 hover:bg-gray-50 transition duration-200">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ servicio.nombre }}</h3>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getTipoBadgeClass(servicio.tipo)">
                                                {{ tipos[servicio.tipo] }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getEstadoBadgeClass(servicio.estado)">
                                                {{ servicio.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                    </div>

                                    <p v-if="servicio.descripcion" class="mt-2 text-sm text-gray-600">
                                        {{ servicio.descripcion }}
                                    </p>

                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                            </svg>
                                            <span class="font-semibold text-gray-900">BS/. {{ servicio.precio_base }}</span>
                                        </div>
                                        <div v-if="servicio.duracion_estimada" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ servicio.duracion_estimada }} min
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ new Date(servicio.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                </div>

                                <div class="ml-6 flex items-center space-x-2">
                                    <Link
                                        :href="route('admin.servicios.edit', servicio.id)"
                                        class="text-green-600 hover:text-green-900 transition duration-200"
                                        title="Editar servicio"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </Link>

                                    <button
                                        @click="cambiarEstado(servicio.id, servicio.estado === 'activo' ? 'inactivo' : 'activo')"
                                        class="text-gray-400 hover:text-gray-600 transition duration-200"
                                        :title="servicio.estado === 'activo' ? 'Desactivar servicio' : 'Activar servicio'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="servicio.estado === 'activo'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>

                                    <button
                                        @click="eliminarServicio(servicio)"
                                        class="text-red-600 hover:text-red-900 transition duration-200"
                                        title="Eliminar servicio"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay resultados -->
                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron servicios</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Intenta ajustar los filtros de búsqueda o crear un nuevo servicio.
                        </p>
                    </div>

                    <!-- Paginación -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                            <div class="text-sm text-gray-700">
                                Mostrando <span class="font-medium">{{ servicios.from }}</span> a
                                <span class="font-medium">{{ servicios.to }}</span> de
                                <span class="font-medium">{{ servicios.total }}</span> resultados
                            </div>
                            <div class="flex space-x-1">
                                <Link v-for="link in servicios.links" :key="link.label" :href="link.url || '#'" :class="{
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
