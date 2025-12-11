<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    clientes: Array,
    estados: Object,
    horarios_disponibles: Array,
});

// Formulario
const form = useForm({
    cliente_id: '',
    vehiculo_id: '',
    fecha: '',
    hora: '',
    motivo: '',
    observaciones: '',
    estado: 'pendiente',
});

// Estados reactivos
const vehiculosCliente = ref([]);
const horariosOcupados = ref([]);
const mostrarCalendario = ref(false);

// Estados filtrados para creación (solo pendiente y confirmada)
const estadosFiltrados = computed(() => {
    const estadosPermitidos = ['pendiente', 'confirmada'];
    return Object.fromEntries(
        Object.entries(props.estados).filter(([key]) =>
            estadosPermitidos.includes(key)
        )
    );
});

// Cargar vehículos cuando se selecciona un cliente
watch(() => form.cliente_id, async (nuevoClienteId) => {
    if (nuevoClienteId) {
        // Buscar el cliente seleccionado
        const clienteSeleccionado = props.clientes.find(c => c.id == nuevoClienteId);
        if (clienteSeleccionado && clienteSeleccionado.vehiculos) {
            vehiculosCliente.value = clienteSeleccionado.vehiculos;
        } else {
            vehiculosCliente.value = [];
        }
        form.vehiculo_id = '';
    } else {
        vehiculosCliente.value = [];
        form.vehiculo_id = '';
    }
});

// Cargar horarios ocupados cuando cambia la fecha
watch(() => form.fecha, async (nuevaFecha) => {
    if (nuevaFecha) {
        try {
            const response = await fetch(route('admin.citas.por-fecha', { fecha: nuevaFecha }));
            const citas = await response.json();
            horariosOcupados.value = citas.map(cita => cita.hora);
            mostrarCalendario.value = true;
        } catch (error) {
            console.error('Error al cargar horarios:', error);
            horariosOcupados.value = [];
        }
    } else {
        horariosOcupados.value = [];
        mostrarCalendario.value = false;
    }
});

// Horarios disponibles filtrados
const horariosDisponibles = computed(() => {
    return props.horarios_disponibles.filter(horario =>
        !horariosOcupados.value.includes(horario)
    );
});

// Verificar si un horario está disponible
const estaDisponible = (horario) => {
    return !horariosOcupados.value.includes(horario);
};

// Obtener la fecha mínima (hoy)
const fechaMinima = computed(() => {
    const hoy = new Date();
    return hoy.toISOString().split('T')[0];
});

// Enviar formulario
const submit = () => {
    form.post(route('admin.citas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

// Función para obtener el nombre del día
const getNombreDia = (fecha) => {
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const fechaObj = new Date(fecha);
    return dias[fechaObj.getDay()];
};
</script>

<template>
    <Head title="Crear Nueva Cita" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Programar Nueva Cita</h1>
                    <p class="text-sm text-gray-600 mt-1">Agenda una nueva cita para un cliente y vehículo</p>
                </div>
                <Link :href="route('admin.citas.index')"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver a Citas
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Formulario -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Información de la Cita</h2>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-8">
                        <!-- Sección: Cliente y Vehículo -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Cliente -->
                            <div>
                                <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span> Cliente
                                </label>
                                <select
                                    id="cliente_id"
                                    v-model="form.cliente_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.cliente_id }"
                                    required
                                >
                                    <option value="">Seleccione un cliente</option>
                                    <option
                                        v-for="cliente in clientes"
                                        :key="cliente.id"
                                        :value="cliente.id"
                                    >
                                        {{ cliente.nombre }} - {{ cliente.email }}
                                    </option>
                                </select>
                                <p v-if="form.errors.cliente_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.cliente_id }}
                                </p>
                                <p v-if="form.cliente_id && vehiculosCliente.length === 0" class="mt-1 text-sm text-yellow-600">
                                    Este cliente no tiene vehículos registrados.
                                </p>
                            </div>

                            <!-- Vehículo -->
                            <div>
                                <label for="vehiculo_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span> Vehículo
                                </label>
                                <select
                                    id="vehiculo_id"
                                    v-model="form.vehiculo_id"
                                    :disabled="!form.cliente_id || vehiculosCliente.length === 0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    :class="{
                                        'border-red-300': form.errors.vehiculo_id,
                                        'bg-gray-100': !form.cliente_id || vehiculosCliente.length === 0
                                    }"
                                    required
                                >
                                    <option value="">Seleccione un vehículo</option>
                                    <option
                                        v-for="vehiculo in vehiculosCliente"
                                        :key="vehiculo.id"
                                        :value="vehiculo.id"
                                    >
                                        {{ vehiculo.marca }} {{ vehiculo.modelo }} - {{ vehiculo.placa }}
                                    </option>
                                </select>
                                <p v-if="form.errors.vehiculo_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.vehiculo_id }}
                                </p>
                                <p v-if="form.cliente_id && vehiculosCliente.length === 0" class="mt-1 text-sm text-yellow-600">
                                    Primero registre un vehículo para este cliente.
                                </p>
                            </div>
                        </div>

                        <!-- Sección: Fecha y Hora -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fecha -->
                            <div>
                                <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span> Fecha de la Cita
                                </label>
                                <input
                                    type="date"
                                    id="fecha"
                                    v-model="form.fecha"
                                    :min="fechaMinima"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.fecha }"
                                    required
                                />
                                <p v-if="form.errors.fecha" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.fecha }}
                                </p>
                                <p v-if="form.fecha" class="mt-1 text-sm text-blue-600 font-medium">
                                    {{ getNombreDia(form.fecha) }}
                                </p>
                            </div>

                            <!-- Hora -->
                            <div>
                                <label for="hora" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span> Hora de la Cita
                                </label>
                                <select
                                    id="hora"
                                    v-model="form.hora"
                                    :disabled="!form.fecha"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark disabled:bg-gray-100 disabled:cursor-not-allowed"
                                    :class="{
                                        'border-red-300': form.errors.hora,
                                        'bg-gray-100': !form.fecha
                                    }"
                                    required
                                >
                                    <option value="">Seleccione una hora</option>
                                    <option
                                        v-for="horario in horariosDisponibles"
                                        :key="horario"
                                        :value="horario"
                                        :class="{ 'text-green-600': estaDisponible(horario) }"
                                    >
                                        {{ horario }} {{ estaDisponible(horario) ? '✓' : '✗' }}
                                    </option>
                                </select>
                                <p v-if="form.errors.hora" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.hora }}
                                </p>
                                <p v-if="form.fecha && horariosDisponibles.length === 0" class="mt-1 text-sm text-red-600">
                                    No hay horarios disponibles para esta fecha.
                                </p>
                            </div>
                        </div>

                        <!-- Visualización de horarios disponibles -->
                        <div v-if="mostrarCalendario && form.fecha" class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">
                                Horarios Disponibles para el {{ getNombreDia(form.fecha) }} ({{ form.fecha }})
                            </h3>
                            <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                                <div
                                    v-for="horario in props.horarios_disponibles"
                                    :key="horario"
                                    @click="form.hora = horario"
                                    class="p-2 text-center text-sm rounded border cursor-pointer transition duration-200"
                                    :class="{
                                        'bg-green-100 border-green-300 text-green-800 hover:bg-green-200': estaDisponible(horario),
                                        'bg-red-100 border-red-300 text-red-800 cursor-not-allowed': !estaDisponible(horario),
                                        'bg-taller-blue-dark border-taller-blue-dark text-white': form.hora === horario && estaDisponible(horario)
                                    }"
                                    :title="estaDisponible(horario) ? 'Disponible' : 'Ocupado'"
                                >
                                    {{ horario }}
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Detalles de la Cita -->
                        <div class="space-y-6">
                            <!-- Motivo -->
                            <div>
                                <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span> Motivo de la Cita
                                </label>
                                <textarea
                                    id="motivo"
                                    v-model="form.motivo"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.motivo }"
                                    placeholder="Describa el motivo de la cita (ej: Cambio de aceite, Revisión de frenos, etc.)"
                                    required
                                />
                                <p v-if="form.errors.motivo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.motivo }}
                                </p>
                            </div>

                            <!-- Observaciones -->
                            <div>
                                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observaciones Adicionales
                                </label>
                                <textarea
                                    id="observaciones"
                                    v-model="form.observaciones"
                                    rows="2"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.observaciones }"
                                    placeholder="Observaciones adicionales o información relevante..."
                                />
                                <p v-if="form.errors.observaciones" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.observaciones }}
                                </p>
                            </div>

                            <!-- Estado -->
                            <!-- Sección: Estado -->
<div class="max-w-xs">
    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
        <span class="text-red-500">*</span> Estado Inicial
    </label>
    <select
        id="estado"
        v-model="form.estado"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
        :class="{ 'border-red-300': form.errors.estado }"
        required
    >
        <option value="">Seleccione un estado</option>
        <!-- Usar computed property o filtrar antes del v-for -->
        <option
            v-for="(label, value) in estadosFiltrados"
            :key="value"
            :value="value"
        >
            {{ label }}
        </option>
    </select>
    <p v-if="form.errors.estado" class="mt-1 text-sm text-red-600">
        {{ form.errors.estado }}
    </p>
    <p class="mt-1 text-xs text-gray-500">
        Las citas nuevas generalmente inician como "Pendiente"
    </p>
</div>
                        </div>

                        <!-- Resumen de la Cita -->
                        <div v-if="form.cliente_id && form.vehiculo_id && form.fecha && form.hora"
                             class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-blue-900 mb-2">Resumen de la Cita</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="font-medium text-blue-800">Cliente:</span>
                                    {{ clientes.find(c => c.id == form.cliente_id)?.nombre }}
                                </div>
                                <div>
                                    <span class="font-medium text-blue-800">Vehículo:</span>
                                    {{ vehiculosCliente.find(v => v.id == form.vehiculo_id)?.marca }}
                                    {{ vehiculosCliente.find(v => v.id == form.vehiculo_id)?.modelo }}
                                </div>
                                <div>
                                    <span class="font-medium text-blue-800">Fecha:</span>
                                    {{ getNombreDia(form.fecha) }}, {{ form.fecha }}
                                </div>
                                <div>
                                    <span class="font-medium text-blue-800">Hora:</span> {{ form.hora }}
                                </div>
                                <div class="md:col-span-2">
                                    <span class="font-medium text-blue-800">Motivo:</span> {{ form.motivo }}
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('admin.citas.index')"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-medium transition duration-200"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-taller-blue-dark hover:bg-taller-blue-light text-white px-6 py-2 rounded-md font-medium transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Programando...' : 'Programar Cita' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Información de ayuda -->
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Información importante</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Las citas solo pueden programarse para fechas futuras</li>
                                    <li>El horario de atención es de 8:00 AM a 5:00 PM</li>
                                    <li>Las citas tienen una duración estimada de 30 minutos</li>
                                    <li>Verifique la disponibilidad antes de confirmar</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
