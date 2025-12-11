<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    cita: Object,
    estados: Object,
});

// Obtener flash messages de forma segura
const page = usePage();
const flash = computed(() => page.props.flash || {});

const eliminarCita = () => {
    if (confirm(`¿Estás seguro de eliminar la cita ${props.cita.codigo}?`)) {
        router.delete(route('admin.citas.destroy', props.cita.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('admin.citas.index'));
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
    if (confirm(`¿Estás seguro de cambiar el estado a ${props.estados[nuevoEstado]}?`)) {
        router.patch(route('admin.citas.update-status', props.cita.id), {
            estado: nuevoEstado
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            }
        });
    }
};

const getEstadoBadgeClass = (estado) => {
    const classes = {
        pendiente: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        confirmada: 'bg-blue-100 text-blue-800 border-blue-200',
        en_proceso: 'bg-purple-100 text-purple-800 border-purple-200',
        completada: 'bg-green-100 text-green-800 border-green-200',
        cancelada: 'bg-red-100 text-red-800 border-red-200',
    };
    return classes[estado] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getNombreDia = (fecha) => {
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Víernes', 'Sábado'];
    const fechaObj = new Date(fecha);
    return dias[fechaObj.getDay()];
};

const puedeCambiarEstado = computed(() => {
    const estadoActual = props.cita.estado;
    return !['completada', 'cancelada'].includes(estadoActual);
});

const getProximoEstado = computed(() => {
    const estados = {
        pendiente: 'confirmada',
        confirmada: 'en_proceso',
        en_proceso: 'completada',
        completada: null,
        cancelada: null
    };
    return estados[props.cita.estado];
});

const getLabelProximoEstado = computed(() => {
    const proximo = getProximoEstado.value;
    return proximo ? props.estados[proximo] : null;
});

const puedeEliminar = computed(() => {
    return !props.cita.diagnostico && props.cita.estado !== 'en_proceso';
});
</script>

<template>

    <Head :title="`Cita ${cita.codigo} - Detalles`" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Cita: {{ cita.codigo }}
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Detalles completos de la cita programada
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link :href="route('admin.citas.edit', cita.id)"
                        class="bg-taller-blue-dark hover:bg-taller-blue-light text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar Cita
                    </Link>
                    <Link :href="route('admin.citas.index')"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Citas
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Alertas -->
                <div v-if="flash.success"
                    class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ flash.success }}
                </div>

                <div v-if="flash.error" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ flash.error }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna izquierda - Información principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Tarjeta de información general -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <h2 class="text-lg font-semibold text-gray-900">Información General</h2>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border-2"
                                    :class="getEstadoBadgeClass(cita.estado)">
                                    {{ estados[cita.estado] }}
                                </span>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Código y Fecha -->
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Código de Cita</label>
                                        <p class="mt-1 text-lg font-mono text-gray-900">{{ cita.codigo }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Fecha y Hora</label>
                                        <p class="mt-1 text-lg text-gray-900">
                                            {{ getNombreDia(cita.fecha) }}, {{ formatFecha(cita.fecha) }}
                                        </p>
                                        <p class="text-sm text-gray-600">Hora: {{ cita.hora }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Cliente -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Información del Cliente</h2>
                            </div>
                            <div class="p-6">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ cita.cliente.nombre }}</h3>
                                        <div class="mt-2 space-y-1 text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                {{ cita.cliente.email }}
                                            </div>
                                            <div v-if="cita.cliente.telefono" class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                                {{ cita.cliente.telefono }}
                                            </div>
                                            <div v-if="cita.cliente.direccion" class="flex items-start">
                                                <svg class="w-4 h-4 mr-2 text-gray-400 mt-0.5 flex-shrink-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span class="break-words">{{ cita.cliente.direccion }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Vehículo -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Información del Vehículo</h2>
                            </div>
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row items-start space-y-4 md:space-y-0 md:space-x-6">
                                    <!-- Imagen del Vehículo -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-48 h-32 bg-gray-200 rounded-lg overflow-hidden border border-gray-300">
                                                <img v-if="cita.vehiculo.foto_url" :src="cita.vehiculo.foto_url"
                                                    :alt="`${cita.vehiculo.marca} ${cita.vehiculo.modelo}`"
                                                    class="w-full h-full object-cover" />
                                                <div v-else
                                                    class="w-full h-full flex items-center justify-center bg-gray-100">
                                                    <svg class="w-12 h-12 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-4a1 1 0 00-.293-.707l-4-4A1 1 0 0015 4H3z" />
                                                    </svg>
                                                </div>
                                        </div>
                                        <p v-if="!cita.vehiculo.foto_url"
                                            class="mt-2 text-xs text-center text-gray-500">
                                            Sin imagen del vehículo
                                        </p>
                                    </div>

                                    <!-- Información del Vehículo -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ cita.vehiculo.marca }} {{ cita.vehiculo.modelo }}
                                        </h3>
                                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                            <div class="space-y-2">
                                                <div>
                                                    <span class="font-medium text-gray-700">Placa:</span>
                                                    <span
                                                        class="ml-2 font-mono bg-gray-100 px-2 py-1 rounded text-sm">{{
                                                            cita.vehiculo.placa }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-700">Año:</span>
                                                    {{ cita.vehiculo.anio }}
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-700">Color:</span>
                                                    <span class="inline-flex items-center">
                                                        <span class="w-3 h-3 rounded-full border border-gray-300 mr-1"
                                                            :style="{ backgroundColor: cita.vehiculo.color?.toLowerCase() }"></span>
                                                        {{ cita.vehiculo.color }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <div>
                                                    <span class="font-medium text-gray-700">Kilometraje:</span>
                                                    {{ cita.vehiculo.kilometraje?.toLocaleString() || '0' }} km
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-700">Estado:</span>
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                        :class="cita.vehiculo.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                                        {{ cita.vehiculo.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Observaciones del Vehículo -->
                                        <div v-if="cita.vehiculo.observaciones" class="mt-4 p-3 bg-gray-50 rounded-lg">
                                            <h4 class="text-sm font-medium text-gray-700 mb-1">Observaciones del
                                                Vehículo</h4>
                                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{
                                                cita.vehiculo.observaciones
                                            }}</p>
                                        </div>

                                        <!-- Enlace para ver más detalles del vehículo -->
                                        <div class="mt-4">
                                            <Link :href="route('admin.vehiculos.show', cita.vehiculo.id)"
                                                class="text-taller-blue-dark hover:text-taller-blue-light text-sm font-medium transition duration-200 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Ver detalles completos del vehículo
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles de la Cita -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Detalles de la Cita</h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Motivo Principal</h4>
                                    <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ cita.motivo }}</p>
                                </div>

                                <div v-if="cita.observaciones">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Observaciones Adicionales</h4>
                                    <p class="text-gray-600 bg-blue-50 p-3 rounded-lg whitespace-pre-wrap">{{
                                        cita.observaciones
                                    }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnóstico (si existe) -->
                        <div v-if="cita.diagnostico" class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Diagnóstico Asociado</h2>
                            </div>
                            <div class="p-6">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-sm font-medium text-green-800">
                                                Diagnóstico: {{ cita.diagnostico.codigo }}
                                            </h3>
                                            <p class="text-sm text-green-700 mt-1">
                                                Realizado por: {{ cita.diagnostico.mecanico?.nombre || 'Mecániconoasignado' }}
                                            </p>
                                        </div>
                                        <Link :href="route('admin.diagnosticos.show', cita.diagnostico.id)"
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition duration-200">
                                        Ver Diagnóstico
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha - Acciones e Información -->
                    <div class="space-y-6">
                        <!-- Acciones Rápidas -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Acciones</h2>
                            </div>
                            <div class="p-6 space-y-3">
                                <!-- Cambiar Estado -->
                                <button v-if="puedeCambiarEstado && getProximoEstado"
                                    @click="cambiarEstado(getProximoEstado)"
                                    class="w-full bg-taller-blue-dark hover:bg-taller-blue-light text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Cambiar a {{ getLabelProximoEstado }}
                                </button>

                                <!-- Cancelar Cita -->
                                <button v-if="puedeCambiarEstado" @click="cambiarEstado('cancelada')"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancelar Cita
                                </button>

                                <!-- Crear Diagnóstico -->
                                <Link v-if="!cita.diagnostico && ['confirmada', 'en_proceso'].includes(cita.estado)"
                                    :href="route('admin.diagnosticos.create', { cita_id: cita.id })"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Crear Diagnóstico
                                </Link>

                                <!-- Eliminar Cita -->
                                <button v-if="puedeEliminar" @click="eliminarCita"
                                    class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar Cita
                                </button>
                            </div>
                        </div>

                        <!-- Información del Sistema -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Información del Sistema</h2>
                            </div>
                            <div class="p-6 space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Creado el:</span>
                                    <span class="text-gray-900">{{ new Date(cita.created_at).toLocaleDateString()
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Actualizado el:</span>
                                    <span class="text-gray-900">{{ new Date(cita.updated_at).toLocaleDateString()
                                    }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">ID:</span>
                                    <span class="text-gray-900 font-mono">{{ cita.id }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Flujo de Estados -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Flujo de la Cita</h2>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <div v-for="(label, value) in estados" :key="value" class="flex items-center">
                                        <div class="flex-shrink-0 w-3 h-3 rounded-full border-2" :class="{
                                            'bg-green-500 border-green-500': cita.estado === value,
                                            'border-gray-300': cita.estado !== value
                                        }"></div>
                                        <span class="ml-3 text-sm" :class="{
                                            'font-medium text-gray-900': cita.estado === value,
                                            'text-gray-500': cita.estado !== value
                                        }">
                                            {{ label }}
                                        </span>
                                        <svg v-if="cita.estado === value" class="ml-2 w-4 h-4 text-green-500"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
