<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    PlusIcon,
    EyeIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    WrenchScrewdriverIcon,
    TruckIcon,
    UserIcon,
    CurrencyDollarIcon,
    DocumentChartBarIcon,
    CalendarIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    ordenes: Object,
    filters: Object,
    estados: Object,
    estadisticas: Object
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Función helper para formatear moneda
const formatCurrency = (value) => {
    return Number(value || 0).toFixed(2);
};

const getEstadoColor = (estado) => {
    const colores = {
        presupuestada: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
        aprobada: 'bg-blue-50 text-blue-700 ring-blue-600/20',
        en_proceso: 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        completada: 'bg-green-50 text-green-700 ring-green-600/20',
        entregada: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        cancelada: 'bg-red-50 text-red-700 ring-red-600/20'
    };
    return colores[estado] || 'bg-gray-50 text-gray-700 ring-gray-600/20';
};

const getEstadoIcon = (estado) => {
    const iconos = {
        presupuestada: ExclamationTriangleIcon,
        aprobada: CheckCircleIcon,
        en_proceso: WrenchScrewdriverIcon,
        completada: CheckCircleIcon,
        entregada: CheckCircleIcon,
        cancelada: XCircleIcon
    };
    return iconos[estado] || ClipboardDocumentListIcon;
};

const getProgresoColor = (progreso) => {
    if (progreso >= 90) return 'bg-green-500';
    if (progreso >= 75) return 'bg-blue-500';
    if (progreso >= 50) return 'bg-indigo-500';
    if (progreso >= 25) return 'bg-yellow-500';
    return 'bg-gray-300';
};
</script>

<template>
    <Head title="Mis Órdenes de Trabajo" />

    <AuthenticatedLayout>
        <div class="py-8 bg-taller-cream min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-10 animate-fade-in-down">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-taller-black flex items-center gap-3">
                                <div class="p-2.5 bg-white rounded-xl shadow-sm border border-taller-blue-light/50">
                                    <ClipboardDocumentListIcon class="h-8 w-8 text-taller-blue-dark" />
                                </div>
                                <span class="tracking-tight">Mis Órdenes de Trabajo</span>
                            </h1>
                            <p class="mt-2 text-gray-600 ml-1">
                                Historial y seguimiento de servicios mecánicos realizados.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="stats-card animate-fade-in-up delay-100 group">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <ClipboardDocumentListIcon class="h-16 w-16 text-taller-blue-dark" />
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Histórico</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-taller-black">{{ estadisticas.total }}</span>
                                <span class="text-xs text-gray-400">órdenes</span>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card animate-fade-in-up delay-200 group border-l-4 border-l-yellow-400">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <ExclamationTriangleIcon class="h-16 w-16 text-yellow-600" />
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Presupuestadas</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-yellow-600">{{ estadisticas.presupuestadas }}</span>
                                <span class="text-xs text-yellow-600/70">por aprobar</span>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card animate-fade-in-up delay-300 group border-l-4 border-l-taller-blue-light">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <WrenchScrewdriverIcon class="h-16 w-16 text-taller-blue-dark" />
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">En Proceso</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-taller-blue-dark">{{ estadisticas.en_proceso }}</span>
                                <span class="text-xs text-taller-blue-dark/70">en taller</span>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card animate-fade-in-up delay-400 group border-l-4 border-l-green-500">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <CheckCircleIcon class="h-16 w-16 text-green-600" />
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Completadas</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-green-600">{{ estadisticas.completadas }}</span>
                                <span class="text-xs text-green-600/70">servicios</span>
                            </div>
                        </div>
                    </div>
                </div>

                <transition enter-active-class="transition ease-out duration-500" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                    <div v-if="estadisticas.orden_activa" class="mb-10 bg-gradient-to-r from-taller-blue-dark to-blue-900 rounded-2xl shadow-xl overflow-hidden text-white relative">
                        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>

                        <div class="p-6 md:p-8 relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-white/10 rounded-lg backdrop-blur-sm animate-pulse-slow">
                                    <WrenchScrewdriverIcon class="h-8 w-8 text-white" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white/90 uppercase tracking-wider text-xs mb-1">Órden Activa</h3>
                                    <p class="text-2xl font-bold text-white mb-1">
                                        {{ estadisticas.orden_activa.codigo }}
                                    </p>
                                    <div class="flex items-center gap-2 text-blue-200 text-sm">
                                        <TruckIcon class="h-4 w-4" />
                                        {{ estadisticas.orden_activa.vehiculo.marca }} {{ estadisticas.orden_activa.vehiculo.modelo }}
                                        <span class="bg-white/20 px-1.5 rounded text-white text-xs">{{ estadisticas.orden_activa.vehiculo.placa }}</span>
                                        <span class="text-white/70">•</span>
                                        <UserIcon class="h-4 w-4" />
                                        {{ estadisticas.orden_activa.mecanico.nombre }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-sm text-blue-200">Progreso</p>
                                    <p class="text-2xl font-bold">{{ estadisticas.orden_activa.progreso }}%</p>
                                </div>
                                <Link
                                    :href="route('cliente.ordenes.show', estadisticas.orden_activa.id)"
                                    class="px-6 py-3 bg-white text-taller-blue-dark font-bold rounded-lg hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center gap-2"
                                >
                                    Ver Detalles
                                    <ArrowRightIcon class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </transition>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in-up delay-500">
                    <div class="p-6 md:p-8">

                        <div v-if="ordenes.data.length === 0" class="text-center py-16">
                            <div class="mx-auto h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 animate-bounce-slow">
                                <ClipboardDocumentListIcon class="h-12 w-12 text-gray-300" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">No tienes órdenes de trabajo</h3>
                            <p class="text-gray-500 mb-8 max-w-sm mx-auto">
                                Tus órdenes de trabajo aparecerán aquí después de que se realice el diagnóstico de tu vehículo.
                            </p>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-6">
                            <div
                                v-for="(orden, index) in ordenes.data"
                                :key="orden.id"
                                class="group border border-gray-100 rounded-xl p-5 hover:border-taller-blue-light/50 hover:shadow-md transition-all duration-300 bg-white relative overflow-hidden"
                                :style="{ animationDelay: `${index * 100}ms` }"
                            >
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-taller-blue-dark transform scale-y-0 group-hover:scale-y-100 transition-transform duration-300 origin-bottom"></div>

                                <div class="flex flex-col md:flex-row justify-between gap-4">

                                    <div class="flex-1">
                                        <div class="flex items-center justify-between md:justify-start gap-4 mb-3">
                                            <span class="font-mono text-xs font-bold text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                                {{ orden.codigo }}
                                            </span>
                                            <div class="flex items-center gap-1.5">
                                                <component :is="getEstadoIcon(orden.estado)" class="h-4 w-4" :class="orden.estado === 'completada' ? 'text-green-600' : 'text-gray-500'" />
                                                <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset capitalize', getEstadoColor(orden.estado)]">
                                                    {{ orden.estado.replace('_', ' ') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4">
                                            <div class="hidden sm:flex h-12 w-12 rounded-full bg-taller-blue-light/10 items-center justify-center shrink-0">
                                                <TruckIcon class="h-6 w-6 text-taller-blue-dark" />
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-taller-blue-dark transition-colors">
                                                    {{ orden.diagnostico?.cita?.vehiculo?.marca }} {{ orden.diagnostico?.cita?.vehiculo?.modelo }}
                                                </h3>
                                                <p class="text-sm text-gray-500 mb-2 font-mono">
                                                    {{ orden.diagnostico?.cita?.vehiculo?.placa }}
                                                </p>

                                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-700">
                                                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg">
                                                        <UserIcon class="h-4 w-4 text-gray-400" />
                                                        <span>{{ orden.mecanico?.nombre }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg">
                                                        <CalendarIcon class="h-4 w-4 text-gray-400" />
                                                        <span>{{ formatDate(orden.fecha_creacion) }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded-lg">
                                                        <CurrencyDollarIcon class="h-4 w-4 text-gray-400" />
                                                        <span class="font-semibold">${{ formatCurrency(orden.total) }}</span>
                                                    </div>
                                                </div>

                                                <!-- Barra de Progreso -->
                                                <div class="mt-4">
                                                    <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                                                        <span>Progreso del trabajo</span>
                                                        <span class="font-semibold">{{ orden.progreso }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div
                                                            class="h-2 rounded-full transition-all duration-500"
                                                            :class="getProgresoColor(orden.progreso)"
                                                            :style="{ width: `${orden.progreso}%` }"
                                                        ></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col justify-between md:items-end gap-4 mt-4 md:mt-0 md:pl-6 md:border-l border-gray-100 md:w-1/3">
                                        <div class="w-full">
                                            <p class="text-xs text-gray-400 uppercase font-bold mb-2">Resumen de Costos</p>
                                            <div class="space-y-1 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500">Servicios:</span>
                                                    <span class="font-medium">${{ formatCurrency(orden.costo_repuestos) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="text-gray-500">Mano de obra:</span>
                                                    <span class="font-medium">${{ formatCurrency(orden.costo_mano_obra) }}</span>
                                                </div>
                                                <div class="flex justify-between border-t border-gray-200 pt-1">
                                                    <span class="text-gray-700 font-semibold">Total:</span>
                                                    <span class="text-taller-blue-dark font-bold">${{ formatCurrency(orden.total) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 w-full justify-end">
                                            <Link
                                                :href="route('cliente.ordenes.show', orden.id)"
                                                class="p-2 text-gray-400 hover:text-taller-blue-dark hover:bg-blue-50 rounded-lg transition-colors"
                                                title="Ver Detalles"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>

                                            <template v-if="orden.esta_presupuestada">
                                                <Link
                                                    :href="route('cliente.ordenes.show', orden.id)"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-taller-blue-dark text-white text-xs font-semibold rounded-lg hover:bg-taller-blue-light transition-colors"
                                                >
                                                    <CheckCircleIcon class="h-3 w-3" />
                                                    Revisar
                                                </Link>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paginación -->
                        <div v-if="ordenes.data.length > 0" class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
                            <div class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">{{ ordenes.from }}</span>
                                a
                                <span class="font-medium">{{ ordenes.to }}</span>
                                de
                                <span class="font-medium">{{ ordenes.total }}</span>
                                resultados
                            </div>
                            <div class="flex gap-1">
                                <Link
                                    v-for="(link, key) in ordenes.links"
                                    :key="key"
                                    :href="link.url || '#'"
                                    class="px-3 py-1 text-sm rounded-lg border transition-colors"
                                    :class="{
                                        'bg-taller-blue-dark text-white border-taller-blue-dark': link.active,
                                        'text-gray-500 border-gray-300 hover:bg-gray-50': !link.active && link.url,
                                        'text-gray-300 border-gray-200 cursor-not-allowed': !link.url
                                    }"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animaciones Personalizadas */
.stats-card {
    @apply bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-1;
}

@keyframes fade-in-down {
    0% { opacity: 0; transform: translateY(-20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes fade-in-up {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes pulse-slow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(-5%); }
    50% { transform: translateY(0); }
}

.animate-fade-in-down {
    animation: fade-in-down 0.6s ease-out forwards;
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0; /* Inicialmente oculto para el delay */
    animation-fill-mode: forwards;
}

.animate-pulse-slow {
    animation: pulse-slow 3s infinite;
}

.animate-bounce-slow {
    animation: bounce-slow 2s infinite ease-in-out;
}

/* Delays para efecto cascada */
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-400 { animation-delay: 0.4s; }
.delay-500 { animation-delay: 0.5s; }
</style>
