<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

// --- LÓGICA INTACTA ---
const props = defineProps({
    citas: Object,
    clientes: Array,
    filters: Object,
    estados: Object,
});

const search = ref(props.filters.search || '');
const estado = ref(props.filters.estado || '');
const cliente_id = ref(props.filters.cliente_id || '');
const fecha = ref(props.filters.fecha || '');

// Búsqueda con debounce
watch([search, estado, cliente_id, fecha], debounce(([newSearch, newEstado, newClienteId, newFecha]) => {
    router.get(route('admin.citas.index'), {
        search: newSearch,
        estado: newEstado,
        cliente_id: newClienteId,
        fecha: newFecha,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const resetFilters = () => {
    search.value = '';
    estado.value = '';
    cliente_id.value = '';
    fecha.value = '';
};

const cambiarEstado = (citaId, nuevoEstado) => {
    if (confirm(`¿Estás seguro de cambiar el estado a ${props.estados[nuevoEstado]}?`)) {
        router.patch(route('admin.citas.update-status', citaId), {
            estado: nuevoEstado
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['citas'] });
            }
        });
    }
};

const eliminarCita = (cita) => {
    if (confirm(`¿Estás seguro de eliminar la cita ${cita.codigo}?`)) {
        router.delete(route('admin.citas.destroy', cita.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['citas'] });
            },
            onError: (errors) => {
                if (errors.error) {
                    alert(errors.error);
                }
            }
        });
    }
};

const getEstadoBadgeClass = (estado) => {
    const classes = {
        pendiente: 'bg-yellow-50 text-yellow-700 border-yellow-200 ring-yellow-600/10',
        confirmada: 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-600/10',
        en_proceso: 'bg-purple-50 text-purple-700 border-purple-200 ring-purple-600/10',
        completada: 'bg-green-50 text-green-700 border-green-200 ring-green-600/10',
        cancelada: 'bg-red-50 text-red-700 border-red-200 ring-red-600/10',
    };
    return classes[estado] || 'bg-gray-50 text-gray-700 border-gray-200';
};

const formatFecha = (fecha) => {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
// --- FIN LÓGICA INTACTA ---
</script>

<template>
    <Head title="Gestión de Citas" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in-down">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center">
                        <svg class="w-7 h-7 mr-2 text-taller-blue-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Gestión de Citas
                    </h1>
                    <p class="text-sm text-gray-500 mt-1 ml-9">Panel de control de programación del taller.</p>
                </div>
                <Link :href="route('admin.citas.create')"
                    class="group bg-taller-blue-dark hover:bg-taller-blue-light text-white px-5 py-2.5 rounded-lg font-medium shadow-sm hover:shadow-md transition-all duration-300 ease-in-out transform hover:-translate-y-0.5 flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5 transition-transform duration-300 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Cita
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center transition-all duration-300 hover:shadow-md hover:-translate-y-1 group relative overflow-hidden">
                         <div class="absolute -right-4 -bottom-4 text-gray-50 opacity-10 group-hover:scale-110 transition-transform duration-500">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
                         </div>
                        <div class="p-3 rounded-xl bg-gray-50 text-gray-500 group-hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div class="ml-4 z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ citas.total }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center transition-all duration-300 hover:shadow-md hover:-translate-y-1 group">
                        <div class="p-3 rounded-xl bg-yellow-50 text-yellow-600 group-hover:bg-yellow-100 transition-colors">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="ml-4">
                             <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pendientes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ citas.data.filter(c => c.estado === 'pendiente').length }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center transition-all duration-300 hover:shadow-md hover:-translate-y-1 group">
                        <div class="p-3 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Confirmadas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ citas.data.filter(c => c.estado === 'confirmada').length }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center transition-all duration-300 hover:shadow-md hover:-translate-y-1 group">
                        <div class="p-3 rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-100 transition-colors">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">En Taller</p>
                            <p class="text-2xl font-bold text-gray-900">{{ citas.data.filter(c => c.estado === 'en_proceso').length }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center transition-all duration-300 hover:shadow-md hover:-translate-y-1 group">
                        <div class="p-3 rounded-xl bg-green-50 text-green-600 group-hover:bg-green-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="ml-4">
                             <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Listas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ citas.data.filter(c => c.estado === 'completada').length }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-md">

                    <div class="p-5 border-b border-gray-200 bg-gray-50/80">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 relative group">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-taller-blue-dark transition-colors duration-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text" v-model="search" placeholder="Buscar por código, placa, cliente..."
                                    class="pl-10 w-full border-gray-300 rounded-xl shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark text-sm h-11 transition-all duration-200 focus:shadow-md" />
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <select v-model="estado" class="w-full sm:w-auto border-gray-300 rounded-xl shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark text-sm h-11 transition-all duration-200 focus:shadow-md cursor-pointer">
                                    <option value="">Estado: Todos</option>
                                    <option v-for="(label, value) in estados" :key="value" :value="value">{{ label }}</option>
                                </select>

                                <select v-model="cliente_id" class="w-full sm:w-auto border-gray-300 rounded-xl shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark text-sm h-11 transition-all duration-200 focus:shadow-md cursor-pointer">
                                    <option value="">Cliente: Todos</option>
                                    <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">{{ cliente.nombre }}</option>
                                </select>

                                <input type="date" v-model="fecha" class="w-full sm:w-auto border-gray-300 rounded-xl shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark text-sm h-11 text-gray-600 transition-all duration-200 focus:shadow-md cursor-pointer" />

                                <button @click="resetFilters" title="Limpiar filtros" class="h-11 px-4 border border-gray-300 rounded-xl text-gray-500 bg-white hover:bg-gray-100 hover:text-gray-700 transition-all duration-200 hover:shadow-sm hover:border-gray-400 active:scale-95 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cita</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vehículo / Motivo</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="cita in citas.data" :key="cita.id" class="hover:bg-blue-50/30 transition-colors duration-200 group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-900 font-mono group-hover:text-taller-blue-dark transition-colors">{{ cita.codigo }}</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium w-fit mt-2 border shadow-sm" :class="getEstadoBadgeClass(cita.estado)">
                                                {{ estados[cita.estado] }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-9 w-9 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm mr-3 shadow-sm border border-gray-200 group-hover:border-blue-200 transition-colors">
                                                {{ cita.cliente.nombre.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ cita.cliente.nombre }}</div>
                                                <div class="text-xs text-gray-500 flex items-center mt-0.5">
                                                     <svg class="w-3 h-3 mr-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    {{ cita.cliente.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium flex items-center">
                                             <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            {{ cita.vehiculo.marca }} {{ cita.vehiculo.modelo }}
                                        </div>
                                        <div class="text-xs text-gray-500 mb-2 ml-5">Placa: {{ cita.vehiculo.placa }}</div>
                                        <div class="text-xs text-gray-600 bg-gray-100 border border-gray-200 px-2 py-1 rounded-md inline-block max-w-[180px] truncate group-hover:bg-white transition-colors" :title="cita.motivo">
                                            {{ cita.motivo }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900 flex items-center">
                                                 <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ formatFecha(cita.fecha) }}
                                            </div>
                                            <div class="text-xs text-gray-500 ml-5 mt-0.5 flex items-center">
                                                 <svg class="w-3 h-3 mr-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ cita.hora }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity duration-200">
                                            <Link :href="route('admin.citas.show', cita.id)" class="text-gray-400 hover:text-blue-600 p-1.5 rounded-lg hover:bg-blue-100 transition-all duration-200 hover:scale-110 hover:shadow-sm" title="Ver Detalle">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </Link>

                                            <Link :href="route('admin.citas.edit', cita.id)" class="text-gray-400 hover:text-green-600 p-1.5 rounded-lg hover:bg-green-100 transition-all duration-200 hover:scale-110 hover:shadow-sm" title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </Link>

                                            <button
                                                @click="cambiarEstado(cita.id, cita.estado === 'pendiente' ? 'confirmada' : cita.estado === 'confirmada' ? 'en_proceso' : cita.estado === 'en_proceso' ? 'completada' : 'cancelada')"
                                                class="text-gray-400 hover:text-purple-600 p-1.5 rounded-lg hover:bg-purple-100 transition-all duration-200 hover:scale-110 hover:shadow-sm"
                                                :title="`Avanzar estado`">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </button>

                                            <button @click="eliminarCita(cita)" class="text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-100 transition-all duration-200 hover:scale-110 hover:shadow-sm" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="citas.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center bg-gray-50">
                                        <svg class="mx-auto h-16 w-16 text-gray-300 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="mt-4 text-lg font-medium text-gray-900">No se encontraron citas</h3>
                                        <p class="mt-2 text-sm text-gray-500">Intenta ajustar los filtros de búsqueda o crea una nueva cita.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            Mostrando <span class="font-medium">{{ citas.from || 0 }}</span> a <span class="font-medium">{{ citas.to || 0 }}</span> de <span class="font-medium">{{ citas.total }}</span> resultados
                        </div>
                        <div class="flex gap-1.5">
                            <Link v-for="(link, i) in citas.links" :key="i"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 border',
                                    link.active
                                        ? 'bg-taller-blue-dark text-white border-taller-blue-dark shadow-md transform scale-105'
                                        : 'text-gray-600 bg-white border-gray-300 hover:bg-gray-100 hover:border-gray-400 hover:text-gray-800 hover:shadow-sm',
                                    !link.url ? 'opacity-50 cursor-not-allowed hover:bg-white hover:shadow-none hover:border-gray-300' : ''
                                ]"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
