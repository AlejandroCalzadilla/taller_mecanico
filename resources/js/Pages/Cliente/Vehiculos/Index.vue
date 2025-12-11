<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    PlusIcon,
    TruckIcon,
    WrenchScrewdriverIcon,
    CalendarDaysIcon,
    MapPinIcon,
    PencilSquareIcon,
    TrashIcon,
    EyeIcon,
    CheckBadgeIcon,
    NoSymbolIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    vehiculos: Array,
    estadisticas: Object
});

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-BO').format(number);
};

const getEstadoColor = (estado) => {
    return estado === 'activo'
        ? 'bg-green-100 text-green-700 ring-green-600/20'
        : 'bg-red-100 text-red-700 ring-red-600/20';
};

const deleteVehiculo = (id) => {
    if (confirm('¿Estás seguro de eliminar este vehículo? Esta acción no se puede deshacer.')) {
        router.delete(route('cliente.vehiculos.destroy', id));
    }
};
</script>

<template>
    <Head title="Mis Vehículos" />

    <AuthenticatedLayout>
        <div class="py-8 bg-taller-cream min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-10 animate-fade-in-down">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-taller-black flex items-center gap-3">
                                <div class="p-2.5 bg-white rounded-xl shadow-sm border border-taller-blue-light/50">
                                    <TruckIcon class="h-8 w-8 text-taller-blue-dark" />
                                </div>
                                <span class="tracking-tight">Mis Vehículos</span>
                            </h1>
                            <p class="mt-2 text-gray-600 ml-1">
                                Administra tu flota y consulta el historial de cada unidad.
                            </p>
                        </div>
                        <Link
                            :href="route('cliente.vehiculos.create')"
                            class="group inline-flex items-center gap-2 px-5 py-2.5 bg-taller-blue-dark text-white rounded-xl hover:bg-taller-blue-light transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                        >
                            <PlusIcon class="h-5 w-5 group-hover:rotate-90 transition-transform duration-300" />
                            <span class="font-semibold">Registrar Vehículo</span>
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div class="stats-card group animate-fade-in-up delay-100">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <TruckIcon class="h-16 w-16 text-blue-600" />
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Flota</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-taller-black">{{ formatNumber(estadisticas.total) }}</span>
                                <span class="text-xs text-gray-400">unidades</span>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card group animate-fade-in-up delay-200 border-l-4 border-l-green-500">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <CheckBadgeIcon class="h-16 w-16 text-green-600" />
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Activos</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-green-600">{{ formatNumber(estadisticas.activos) }}</span>
                                <span class="text-xs text-green-600/70">en servicio</span>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card group animate-fade-in-up delay-300 border-l-4 border-l-purple-500">
                        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <WrenchScrewdriverIcon class="h-16 w-16 text-purple-600" />
                        </div>
                        <div class="relative z-10">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">En Mantenimiento</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-purple-600">{{ formatNumber(estadisticas.con_citas) }}</span>
                                <span class="text-xs text-purple-600/70">con cita activa</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="vehiculos.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center animate-fade-in-up">
                    <div class="mx-auto h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 animate-bounce-slow">
                        <TruckIcon class="h-12 w-12 text-gray-300" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Aún no tienes vehículos</h3>
                    <p class="text-gray-500 mb-8 max-w-sm mx-auto">
                        Registra tu primer vehículo para comenzar a gestionar sus mantenimientos y reparaciones.
                    </p>
                    <Link
                        :href="route('cliente.vehiculos.create')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-taller-blue-dark text-white font-semibold rounded-xl hover:bg-taller-blue-light transition-all shadow-md hover:shadow-lg"
                    >
                        <PlusIcon class="h-5 w-5" />
                        Registrar Ahora
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-fade-in-up delay-300">
                    <div
                        v-for="(vehiculo, index) in vehiculos"
                        :key="vehiculo.id"
                        class="group bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl hover:border-taller-blue-light/30 transition-all duration-300 flex flex-col"
                        :style="{ animationDelay: `${index * 100}ms` }"
                    >
                        <div class="relative h-48 bg-gray-100 overflow-hidden">
                            <img
                                v-if="vehiculo.foto"
                                :src="`/storage/${vehiculo.foto}`"
                                :alt="`${vehiculo.marca} ${vehiculo.modelo}`"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center bg-gray-50 group-hover:bg-gray-100 transition-colors">
                                <TruckIcon class="h-20 w-20 text-gray-300 group-hover:text-taller-blue-light/50 transition-colors" />
                            </div>

                            <div class="absolute top-4 right-4">
                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset backdrop-blur-sm shadow-sm capitalize', getEstadoColor(vehiculo.estado)]">
                                    {{ vehiculo.estado }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 group-hover:text-taller-blue-dark transition-colors line-clamp-1">
                                        {{ vehiculo.marca }} {{ vehiculo.modelo }}
                                    </h3>
                                    <p class="text-sm font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded w-fit mt-1 border border-gray-200">
                                        {{ vehiculo.placa }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <CalendarDaysIcon class="h-4 w-4 text-gray-400" />
                                    <span>{{ vehiculo.anio }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-600">
                                    <MapPinIcon class="h-4 w-4 text-gray-400" />
                                    <span>{{ formatNumber(vehiculo.kilometraje) }} km</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-600 col-span-2">
                                    <div class="h-3 w-3 rounded-full border border-gray-300 shadow-sm" :style="{ backgroundColor: vehiculo.color || '#fff' }"></div>
                                    <span class="capitalize">{{ vehiculo.color || 'No especificado' }}</span>
                                </div>
                            </div>

                            <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between">
                                <Link
                                    :href="route('cliente.vehiculos.show', vehiculo.id)"
                                    class="text-sm font-semibold text-taller-blue-dark hover:text-blue-700 flex items-center gap-1 transition-colors"
                                >
                                    <EyeIcon class="h-4 w-4" />
                                    Detalles
                                </Link>

                                <div class="flex items-center gap-1">
                                    <Link
                                        :href="route('cliente.vehiculos.edit', vehiculo.id)"
                                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                        title="Editar"
                                    >
                                        <PencilSquareIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        @click="deleteVehiculo(vehiculo.id)"
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Eliminar"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.stats-card {
    @apply bg-white rounded-2xl p-6 shadow-sm border border-gray-100 relative overflow-hidden transition-all duration-300 hover:shadow-md hover:-translate-y-1;
}

/* Animaciones */
@keyframes fade-in-down {
    0% { opacity: 0; transform: translateY(-20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes fade-in-up {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
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
    opacity: 0;
    animation-fill-mode: forwards;
}

.animate-bounce-slow {
    animation: bounce-slow 3s infinite ease-in-out;
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
</style>
