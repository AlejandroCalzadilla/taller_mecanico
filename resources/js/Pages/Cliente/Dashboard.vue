<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    TruckIcon,
    CalendarDaysIcon,
    ClockIcon,
    WrenchScrewdriverIcon,
    ClipboardDocumentCheckIcon,
    ChevronRightIcon,
    ArrowTrendingUpIcon,
    CheckCircleIcon,
    BanknotesIcon,
    UserCircleIcon,
    ClipboardDocumentListIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    userType: String,
    stats: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('es-ES', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const getStatusConfig = (status) => {
    const configs = {
        pendiente: { class: 'bg-yellow-50 text-yellow-700 ring-yellow-600/20', label: 'Pendiente' },
        confirmada: { class: 'bg-blue-50 text-blue-700 ring-blue-600/20', label: 'Confirmada' },
        en_proceso: { class: 'bg-purple-50 text-purple-700 ring-purple-600/20', label: 'En Taller' },
        completada: { class: 'bg-green-50 text-green-700 ring-green-600/20', label: 'Listo' },
        cancelada: { class: 'bg-red-50 text-red-700 ring-red-600/20', label: 'Cancelada' },
    };
    return configs[status] || { class: 'bg-gray-50 text-gray-600', label: status };
};
</script>

<template>
    <Head title="Panel Principal" />

    <AuthenticatedLayout>
        <div class="py-8 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-8 animate-fade-in-down">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Hola, {{ $page.props.auth.user.nombre }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Bienvenido a tu panel de control personal.
                    </p>
                </div>

                <div v-if="userType === 'cliente'" class="space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-up delay-100">

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
                            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <TruckIcon class="h-24 w-24 text-taller-blue-dark" />
                            </div>
                            <div class="relative z-10 flex items-center gap-4">
                                <div class="p-3 bg-blue-50 rounded-xl text-taller-blue-dark">
                                    <TruckIcon class="h-8 w-8" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Mis Vehículos</p>
                                    <div class="flex items-baseline gap-2">
                                        <h3 class="text-3xl font-bold text-gray-900">{{ stats.vehiculos }}</h3>
                                        <span class="text-xs text-green-600 font-medium bg-green-50 px-2 py-0.5 rounded-full">Activos</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-50">
                                <Link :href="route('cliente.vehiculos.index')" class="text-sm text-taller-blue-dark font-medium hover:underline flex items-center gap-1">
                                    Ver mi flota <ChevronRightIcon class="h-3 w-3" />
                                </Link>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
                            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <ClockIcon class="h-24 w-24 text-yellow-600" />
                            </div>
                            <div class="relative z-10 flex items-center gap-4">
                                <div class="p-3 bg-yellow-50 rounded-xl text-yellow-600">
                                    <CalendarDaysIcon class="h-8 w-8" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Citas Pendientes</p>
                                    <div class="flex items-baseline gap-2">
                                        <h3 class="text-3xl font-bold text-gray-900">{{ stats.citas_pendientes }}</h3>
                                        <span class="text-xs text-yellow-700 font-medium bg-yellow-50 px-2 py-0.5 rounded-full">Próximas</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-50">
                                <Link :href="route('cliente.citas.index')" class="text-sm text-taller-blue-dark font-medium hover:underline flex items-center gap-1">
                                    Gestionar agenda <ChevronRightIcon class="h-3 w-3" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="animate-fade-in-up delay-200">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <PlusIcon class="h-5 w-5 text-taller-blue-dark" />
                            Accesos Directos
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                            <Link :href="route('cliente.vehiculos.index')"
                                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-taller-blue-light hover:shadow-md transition-all duration-200 flex flex-col items-center text-center group">
                                <div class="w-12 h-12 bg-blue-50 text-taller-blue-dark rounded-full flex items-center justify-center mb-3 group-hover:bg-taller-blue-dark group-hover:text-white transition-colors">
                                    <TruckIcon class="w-6 h-6" />
                                </div>
                                <span class="font-semibold text-gray-900 text-sm">Mis Vehículos</span>
                                <span class="text-xs text-gray-500 mt-1">Administrar flota</span>
                            </Link>

                            <Link :href="route('cliente.citas.index')"
                                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-taller-blue-light hover:shadow-md transition-all duration-200 flex flex-col items-center text-center group">
                                <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mb-3 group-hover:bg-yellow-500 group-hover:text-white transition-colors">
                                    <CalendarDaysIcon class="w-6 h-6" />
                                </div>
                                <span class="font-semibold text-gray-900 text-sm">Agendar Cita</span>
                                <span class="text-xs text-gray-500 mt-1">Reservar servicio</span>
                            </Link>

                            <Link :href="route('cliente.ordenes.index')"
                                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-taller-blue-light hover:shadow-md transition-all duration-200 flex flex-col items-center text-center group">
                                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-3 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                                    <ClipboardDocumentListIcon class="w-6 h-6" />
                                </div>
                                <span class="font-semibold text-gray-900 text-sm">Mis Órdenes</span>
                                <span class="text-xs text-gray-500 mt-1">Ver trabajos</span>
                            </Link>

                            <Link :href="route('cliente.pagos.index')"
                                class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-taller-blue-light hover:shadow-md transition-all duration-200 flex flex-col items-center text-center group">
                                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-3 group-hover:bg-green-600 group-hover:text-white transition-colors">
                                    <BanknotesIcon class="w-6 h-6" />
                                </div>
                                <span class="font-semibold text-gray-900 text-sm">Mis Pagos</span>
                                <span class="text-xs text-gray-500 mt-1">Historial financiero</span>
                            </Link>

                             </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden animate-fade-in-up delay-300">
                        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <ArrowTrendingUpIcon class="h-5 w-5 text-gray-400" />
                                Actividad Reciente
                            </h3>
                            <Link :href="route('cliente.citas.index')" class="text-xs font-medium text-gray-500 hover:text-taller-blue-dark uppercase tracking-wide">
                                Ver historial completo
                            </Link>
                        </div>

                        <div v-if="stats.ultimas_citas && stats.ultimas_citas.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehículo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-for="cita in stats.ultimas_citas" :key="cita.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ cita.codigo }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center text-taller-blue-dark shrink-0">
                                                    <TruckIcon class="h-4 w-4" />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ cita.vehiculo?.marca }} {{ cita.vehiculo?.modelo }}</p>
                                                    <p class="text-xs text-gray-500">{{ cita.vehiculo?.placa }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ formatDate(cita.fecha) }}
                                            <span class="text-xs text-gray-400 block">{{ cita.hora }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span :class="['inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset', getStatusConfig(cita.estado).class]">
                                                {{ getStatusConfig(cita.estado).label }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="p-8 text-center text-gray-500 flex flex-col items-center">
                            <CalendarDaysIcon class="h-12 w-12 text-gray-300 mb-2" />
                            <p>No hay actividad reciente registrada.</p>
                        </div>
                    </div>
                </div>

                <div v-if="userType === 'mecanico'" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-up delay-100">
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 border-l-4 border-l-yellow-400 flex items-center justify-between group hover:shadow-md transition-all">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase mb-1">Diagnósticos por Revisar</p>
                                <h3 class="text-4xl font-bold text-gray-900">{{ stats.diagnosticos_pendientes }}</h3>
                                <p class="text-sm text-yellow-600 mt-2 font-medium">Requieren atención</p>
                            </div>
                            <div class="p-4 bg-yellow-50 rounded-full group-hover:scale-110 transition-transform">
                                <ClipboardDocumentCheckIcon class="h-8 w-8 text-yellow-600" />
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-200 border-l-4 border-l-taller-blue-dark flex items-center justify-between group hover:shadow-md transition-all">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase mb-1">Trabajos en Curso</p>
                                <h3 class="text-4xl font-bold text-gray-900">{{ stats.ordenes_proceso }}</h3>
                                <p class="text-sm text-taller-blue-dark mt-2 font-medium">En el taller</p>
                            </div>
                            <div class="p-4 bg-blue-50 rounded-full group-hover:scale-110 transition-transform">
                                <WrenchScrewdriverIcon class="h-8 w-8 text-taller-blue-dark" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fade-in-down {
    0% { opacity: 0; transform: translateY(-20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes fade-in-up {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-down {
    animation: fade-in-down 0.6s ease-out forwards;
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
    animation-fill-mode: forwards;
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
</style>
