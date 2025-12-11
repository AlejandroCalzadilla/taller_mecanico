<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    clientes: Object,
    filters: Object,
    estados: Object,
    tipos: Object, // Agregar esta línea
});

const search = ref(props.filters.search || '');
const estado = ref(props.filters.estado || '');
const tipo = ref(props.filters.tipo || '');

// Definir tipos de usuario
const tiposUsuario = {
    '': 'Todos los tipos',
    cliente: 'Cliente',
    mecanico: 'Mecánico',
    secretaria: 'Secretaria',
    propietario: 'Propietario',
    ...props.tipos
};

// Búsqueda con debounce
watch([search, estado, tipo], debounce(([newSearch, newEstado, newTipo]) => {
    router.get(route('admin.clientes.index'), {
        search: newSearch,
        estado: newEstado,
        tipo: newTipo,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const resetFilters = () => {
    search.value = '';
    estado.value = '';
    tipo.value = '';
};

// Función para cambiar estado rápidamente
const cambiarEstado = (clienteId, nuevoEstado) => {
    if (confirm(`¿Estás seguro de cambiar el estado a ${nuevoEstado === 'activo' ? 'Activo' : 'Inactivo'}?`)) {
        router.patch(route('admin.clientes.update-status', clienteId), {
            estado: nuevoEstado
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Recargar la página para reflejar los cambios
                router.reload({ only: ['clientes'] });
            }
        });
    }
};

// Función para eliminar cliente
const eliminarCliente = (cliente) => {
    if (confirm(`¿Estás seguro de eliminar al usuario ${cliente.nombre}? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.clientes.destroy', cliente.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['clientes'] });
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
    <Head title="Gestión de Usuarios" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gestión de Usuarios</h1>
                    <p class="text-sm text-gray-600 mt-1">Administra todos los usuarios del sistema</p>
                </div>
                <Link
                    :href="route('admin.clientes.create')"
                    class="ml-4 bg-taller-blue-dark hover:bg-taller-blue-light text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                                <p class="text-xl font-semibold text-gray-900">{{ clientes.total }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Activos</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ clientes.data.filter(c => c.estado === 'activo').length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Clientes</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ clientes.data.filter(c => c.tipo === 'cliente').length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600">Mecánicos</p>
                                <p class="text-xl font-semibold text-gray-900">
                                    {{ clientes.data.filter(c => c.tipo === 'mecanico').length }}
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Buscar
                            </label>
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Nombre, email o teléfono..."
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                            />
                        </div>

                        <!-- Filtro por estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select
                                v-model="estado"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                            >
                                <option value="">Todos los estados</option>
                                <option v-for="(label, value) in estados" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Filtro por tipo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Usuario</label>
                            <select
                                v-model="tipo"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                            >
                                <option v-for="(label, value) in tiposUsuario" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex items-end space-x-2">
                            <button
                                @click="resetFilters"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 flex items-center"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Usuarios Mejorada -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contacto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Vehículos
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Registro
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="cliente in clientes.data" :key="cliente.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold text-sm"
                                                    :class="{
                                                        'bg-blue-500': cliente.tipo === 'cliente',
                                                        'bg-green-500': cliente.tipo === 'mecanico',
                                                        'bg-purple-500': cliente.tipo === 'secretaria',
                                                        'bg-orange-500': cliente.tipo === 'propietario'
                                                    }">
                                                    {{ cliente.nombre.charAt(0).toUpperCase() }}
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ cliente.nombre }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    ID: {{ cliente.id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ cliente.email }}</div>
                                        <div class="text-sm text-gray-500" v-if="cliente.telefono">
                                            📞 {{ cliente.telefono }}
                                        </div>
                                        <div class="text-xs text-gray-400" v-else>
                                            Sin teléfono
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-blue-100 text-blue-800': cliente.tipo === 'cliente',
                                                'bg-green-100 text-green-800': cliente.tipo === 'mecanico',
                                                'bg-purple-100 text-purple-800': cliente.tipo === 'secretaria',
                                                'bg-orange-100 text-orange-800': cliente.tipo === 'propietario'
                                            }">
                                            {{ tiposUsuario[cliente.tipo] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-gray-100 text-gray-800': cliente.vehiculos_count === 0,
                                                'bg-blue-100 text-blue-800': cliente.vehiculos_count > 0
                                            }">
                                            {{ cliente.vehiculos_count }} vehículos
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': cliente.estado === 'activo',
                                                    'bg-red-100 text-red-800': cliente.estado === 'inactivo'
                                                }">
                                                {{ cliente.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                            </span>
                                            <button
                                                @click="cambiarEstado(cliente.id, cliente.estado === 'activo' ? 'inactivo' : 'activo')"
                                                class="text-gray-400 hover:text-gray-600 transition duration-200"
                                                :title="cliente.estado === 'activo' ? 'Desactivar usuario' : 'Activar usuario'"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path v-if="cliente.estado === 'activo'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ new Date(cliente.created_at).toLocaleDateString('es-ES') }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ new Date(cliente.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <Link
                                                :href="route('admin.clientes.show', cliente.id)"
                                                class="text-blue-600 hover:text-blue-900 transition duration-200 flex items-center"
                                                title="Ver detalles"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </Link>
                                            <Link
                                                :href="route('admin.clientes.edit', cliente.id)"
                                                class="text-green-600 hover:text-green-900 transition duration-200 flex items-center"
                                                title="Editar usuario"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </Link>
                                            <button
                                                @click="eliminarCliente(cliente)"
                                                class="text-red-600 hover:text-red-900 transition duration-200 flex items-center"
                                                :disabled="cliente.vehiculos_count > 0"
                                                :title="cliente.vehiculos_count > 0 ? 'No se puede eliminar: tiene vehículos registrados' : 'Eliminar usuario'"
                                                :class="{ 'opacity-50 cursor-not-allowed': cliente.vehiculos_count > 0 }"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Mensaje cuando no hay resultados -->
                        <div v-if="clientes.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron usuarios</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Intenta ajustar los filtros de búsqueda o crear un nuevo usuario.
                            </p>
                        </div>
                    </div>

                    <!-- Paginación Mejorada -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                            <div class="text-sm text-gray-700">
                                Mostrando <span class="font-medium">{{ clientes.from }}</span> a
                                <span class="font-medium">{{ clientes.to }}</span> de
                                <span class="font-medium">{{ clientes.total }}</span> resultados
                            </div>
                            <div class="flex space-x-1">
                                <Link
                                    v-for="link in clientes.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="{
                                        'bg-taller-blue-dark text-white': link.active,
                                        'text-gray-500 hover:bg-gray-50 hover:text-gray-700': !link.active && link.url,
                                        'text-gray-300 cursor-not-allowed': !link.url
                                    }"
                                    class="px-3 py-2 rounded-md text-sm font-medium transition duration-200 border"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
