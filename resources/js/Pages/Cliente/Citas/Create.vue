<!-- resources/js/Pages/Cliente/Citas/Create.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    CalendarDaysIcon,
    TruckIcon,
    ClockIcon
} from '@heroicons/vue/24/outline';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    vehiculos: Array,
    vehiculoSeleccionado: Object,
    fechasDisponibles: Array,
    horariosDisponibles: Array
});

const form = useForm({
    vehiculo_id: props.vehiculoSeleccionado?.id || '',
    fecha: '',
    hora: '',
    motivo: '',
    observaciones: ''
});

// Estado reactivo para horarios disponibles filtrados
const horariosDisponiblesFiltrados = ref([]);

// Filtrar horarios disponibles cuando cambia la fecha
watch(() => form.fecha, (nuevaFecha) => {
    if (nuevaFecha) {
        // En una implementación real, aquí harías una llamada API
        // para verificar disponibilidad específica de esa fecha
        horariosDisponiblesFiltrados.value = props.horariosDisponibles;
    } else {
        horariosDisponiblesFiltrados.value = [];
    }
});

const submit = () => {
    form.post(route('cliente.citas.store'));
};

// Computed para mostrar información del vehículo seleccionado
const vehiculoSeleccionadoInfo = computed(() => {
    return props.vehiculos.find(v => v.id == form.vehiculo_id);
});

// Computed para validar si el formulario está listo
const formularioValido = computed(() => {
    return form.vehiculo_id && form.fecha && form.hora && form.motivo;
});
</script>

<template>
    <Head title="Agendar Cita" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-4 mb-4">
                        <Link
                            :href="route('cliente.citas.index')"
                            class="inline-flex items-center gap-2 text-gray-600 hover:text-taller-black transition-colors"
                        >
                            <ArrowLeftIcon class="h-5 w-5" />
                            Volver a Mis Citas
                        </Link>
                    </div>

                    <h1 class="text-3xl font-bold text-taller-black flex items-center gap-3">
                        <div class="p-2 bg-taller-blue-light rounded-lg border border-taller-blue-dark">
                            <CalendarDaysIcon class="h-6 w-6 text-taller-blue-dark" />
                        </div>
                        Agendar Nueva Cita
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Selecciona tu vehículo y el horario que prefieras para tu cita
                    </p>
                </div>

                <!-- Formulario -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">

                        <!-- Selección de Vehículo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Vehículo *
                            </label>
                            <select
                                v-model="form.vehiculo_id"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                required
                            >
                                <option value="">Seleccionar vehículo</option>
                                <option
                                    v-for="vehiculo in vehiculos"
                                    :key="vehiculo.id"
                                    :value="vehiculo.id"
                                >
                                    {{ vehiculo.marca }} {{ vehiculo.modelo }} - {{ vehiculo.placa }}
                                </option>
                            </select>
                            <p v-if="form.errors.vehiculo_id" class="text-red-600 text-sm mt-1">
                                {{ form.errors.vehiculo_id }}
                            </p>

                            <!-- Información del vehículo seleccionado -->
                            <div v-if="vehiculoSeleccionadoInfo" class="mt-3 p-3 bg-gray-50 rounded-lg border">
                                <div class="flex items-center gap-3">
                                    <TruckIcon class="h-8 w-8 text-taller-blue-dark" />
                                    <div>
                                        <p class="font-semibold">
                                            {{ vehiculoSeleccionadoInfo.marca }} {{ vehiculoSeleccionadoInfo.modelo }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Placa: {{ vehiculoSeleccionadoInfo.placa }} |
                                            Año: {{ vehiculoSeleccionadoInfo.anio }} |
                                            Color: {{ vehiculoSeleccionadoInfo.color }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Kilometraje: {{ vehiculoSeleccionadoInfo.kilometraje?.toLocaleString() }} km
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selección de Fecha y Hora -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fecha -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha *
                                </label>
                                <select
                                    v-model="form.fecha"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                    required
                                >
                                    <option value="">Seleccionar fecha</option>
                                    <option
                                        v-for="fecha in fechasDisponibles"
                                        :key="fecha"
                                        :value="fecha"
                                    >
                                        {{ new Date(fecha).toLocaleDateString('es-ES', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        }) }}
                                    </option>
                                </select>
                                <p v-if="form.errors.fecha" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.fecha }}
                                </p>
                            </div>

                            <!-- Hora -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora *
                                </label>
                                <select
                                    v-model="form.hora"
                                    :disabled="!form.fecha"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark disabled:bg-gray-100"
                                    required
                                >
                                    <option value="">Seleccionar hora</option>
                                    <option
                                        v-for="hora in horariosDisponiblesFiltrados"
                                        :key="hora"
                                        :value="hora"
                                    >
                                        {{ hora }}
                                    </option>
                                </select>
                                <p v-if="form.errors.hora" class="text-red-600 text-sm mt-1">
                                    {{ form.errors.hora }}
                                </p>
                                <p v-if="!form.fecha" class="text-xs text-gray-500 mt-1">
                                    Primero selecciona una fecha
                                </p>
                            </div>
                        </div>

                        <!-- Motivo de la Cita -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo de la Cita *
                            </label>
                            <textarea
                                v-model="form.motivo"
                                rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                placeholder="Describe el problema o servicio que necesitas..."
                                required
                            ></textarea>
                            <p v-if="form.errors.motivo" class="text-red-600 text-sm mt-1">
                                {{ form.errors.motivo }}
                            </p>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones Adicionales
                            </label>
                            <textarea
                                v-model="form.observaciones"
                                rows="2"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                placeholder="Síntomas específicos, ruidos, comportamientos extraños..."
                            ></textarea>
                            <p v-if="form.errors.observaciones" class="text-red-600 text-sm mt-1">
                                {{ form.errors.observaciones }}
                            </p>
                        </div>

                        <!-- Resumen de la Cita -->
                        <div v-if="formularioValido" class="p-4 bg-taller-cream rounded-lg border border-taller-blue-light">
                            <h3 class="text-lg font-semibold text-taller-black mb-3 flex items-center gap-2">
                                <ClockIcon class="h-5 w-5" />
                                Resumen de tu Cita
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600">Vehículo:</p>
                                    <p class="font-semibold">
                                        {{ vehiculoSeleccionadoInfo.marca }} {{ vehiculoSeleccionadoInfo.modelo }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Fecha y Hora:</p>
                                    <p class="font-semibold">
                                        {{ new Date(form.fecha).toLocaleDateString('es-ES') }} a las {{ form.hora }}
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-gray-600">Motivo:</p>
                                    <p class="font-semibold">{{ form.motivo }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('cliente.citas.index')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing || !formularioValido"
                                class="px-4 py-2 text-sm font-medium text-white bg-taller-blue-dark rounded-lg hover:bg-taller-blue-light disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <span v-if="form.processing">Agendando...</span>
                                <span v-else>Confirmar Cita</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
