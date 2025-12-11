<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    cita: Object,
    clientes: Array,
    estados: Object,
    horarios_disponibles: Array,
});

// Formatear fecha correctamente
const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Formatear hora correctamente (quitar segundos si existen)
const formatearHora = (hora) => {
    if (!hora) return '';
    // Convertir a string y tomar solo HH:MM
    return String(hora).substring(0, 5);
};

const form = useForm({
    cliente_id: props.cita.cliente_id,
    vehiculo_id: props.cita.vehiculo_id,
    fecha: formatearFecha(props.cita.fecha),
    hora: formatearHora(props.cita.hora),
    motivo: props.cita.motivo,
    observaciones: props.cita.observaciones || '',
    estado: props.cita.estado,
});

// Cliente seleccionado
const clienteSeleccionado = computed(() => {
    return props.clientes.find(c => c.id === form.cliente_id);
});

// Vehículos del cliente seleccionado
const vehiculosDisponibles = computed(() => {
    return clienteSeleccionado.value?.vehiculos || [];
});

// Watch para limpiar vehículo seleccionado cuando cambia el cliente
watch(() => form.cliente_id, (newClienteId) => {
    const vehiculosCliente = props.clientes.find(c => c.id === newClienteId)?.vehiculos || [];

    // Si el vehículo actual no pertenece al nuevo cliente, limpiar selección
    if (!vehiculosCliente.find(v => v.id === form.vehiculo_id)) {
        form.vehiculo_id = '';
    }
});

const submit = () => {
    form.put(route('admin.citas.update', props.cita.id), {
        preserveScroll: true,
        onSuccess: () => {
            // La redirección se maneja en el controlador
        },
    });
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

const estadosDisponibles = computed(() => {
    // No permitir cambiar a completada o cancelada desde el formulario de edición
    const estadosEditables = { ...props.estados };
    if (props.cita.estado !== 'completada') {
        // Se puede cambiar a completada solo si ya está completada
        delete estadosEditables.completada;
    }
    return estadosEditables;
});

// Fecha mínima (hoy)
const fechaMinima = computed(() => {
    const hoy = new Date();
    return hoy.toISOString().split('T')[0];
});
</script>

<template>
    <Head :title="`Editar Cita ${cita.codigo}`" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        Editar Cita: {{ cita.codigo }}
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Modifica los datos de la cita programada
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link :href="route('admin.citas.show', cita.id)"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Cancelar
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <!-- Header del formulario -->
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">Información de la Cita</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border-2"
                            :class="getEstadoBadgeClass(cita.estado)">
                            {{ estados[cita.estado] }}
                        </span>
                    </div>

                    <!-- Formulario -->
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Código de Cita (solo lectura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Código de Cita
                            </label>
                            <input type="text" :value="cita.codigo" disabled
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 font-mono cursor-not-allowed" />
                            <p class="mt-1 text-xs text-gray-500">
                                El código de cita no se puede modificar
                            </p>
                        </div>

                        <!-- Selección de Cliente -->
                        <div>
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <select id="cliente_id" v-model="form.cliente_id" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                :class="{ 'border-red-500': form.errors.cliente_id }">
                                <option value="">Seleccione un cliente</option>
                                <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                    {{ cliente.nombre }} ({{ cliente.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.cliente_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.cliente_id }}
                            </p>
                        </div>

                        <!-- Selección de Vehículo -->
                        <div>
                            <label for="vehiculo_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Vehículo <span class="text-red-500">*</span>
                            </label>
                            <select id="vehiculo_id" v-model="form.vehiculo_id" required
                                :disabled="!form.cliente_id || vehiculosDisponibles.length === 0"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark disabled:bg-gray-100 disabled:cursor-not-allowed"
                                :class="{ 'border-red-500': form.errors.vehiculo_id }">
                                <option value="">
                                    {{ !form.cliente_id ? 'Primero seleccione un cliente' :
                                        vehiculosDisponibles.length === 0 ? 'El cliente no tiene vehículos' :
                                            'Seleccione un vehículo' }}
                                </option>
                                <option v-for="vehiculo in vehiculosDisponibles" :key="vehiculo.id" :value="vehiculo.id">
                                    {{ vehiculo.placa }} - {{ vehiculo.marca }} {{ vehiculo.modelo }}
                                </option>
                            </select>
                            <p v-if="form.errors.vehiculo_id" class="mt-1 text-sm text-red-600">
                                {{ form.errors.vehiculo_id }}
                            </p>
                            <p v-else-if="!form.cliente_id" class="mt-1 text-xs text-gray-500">
                                Seleccione primero un cliente para ver sus vehículos
                            </p>
                            <p v-else-if="vehiculosDisponibles.length === 0" class="mt-1 text-xs text-orange-600">
                                El cliente seleccionado no tiene vehículos registrados
                            </p>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fecha -->
                            <div>
                                <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="fecha" v-model="form.fecha" :min="fechaMinima" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                    :class="{ 'border-red-500': form.errors.fecha }" />
                                <p v-if="form.errors.fecha" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.fecha }}
                                </p>
                            </div>

                            <!-- Hora -->
                            <div>
                                <label for="hora" class="block text-sm font-medium text-gray-700 mb-2">
                                    Hora <span class="text-red-500">*</span>
                                </label>
                                <select id="hora" v-model="form.hora" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                    :class="{ 'border-red-500': form.errors.hora }">
                                    <option value="">Seleccione una hora</option>
                                    <option v-for="horario in horarios_disponibles" :key="horario" :value="horario">
                                        {{ horario }}
                                    </option>
                                </select>
                                <p v-if="form.errors.hora" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.hora }}
                                </p>
                                <p v-else class="mt-1 text-xs text-gray-500">
                                    Horario disponible: 08:00 - 17:00
                                </p>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select id="estado" v-model="form.estado" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                :class="{ 'border-red-500': form.errors.estado }">
                                <option v-for="(label, value) in estadosDisponibles" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                            <p v-if="form.errors.estado" class="mt-1 text-sm text-red-600">
                                {{ form.errors.estado }}
                            </p>
                            <p v-else class="mt-1 text-xs text-gray-500">
                                Para cambiar a Completada o Cancelada, use los botones de acción en la vista de detalles
                            </p>
                        </div>

                        <!-- Motivo -->
                        <div>
                            <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo de la Cita <span class="text-red-500">*</span>
                            </label>
                            <textarea id="motivo" v-model="form.motivo" rows="3" required maxlength="500"
                                placeholder="Ej: Mantenimiento preventivo, revisión de frenos, cambio de aceite..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark resize-none"
                                :class="{ 'border-red-500': form.errors.motivo }"></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <p v-if="form.errors.motivo" class="text-sm text-red-600">
                                    {{ form.errors.motivo }}
                                </p>
                                <p class="text-xs text-gray-500 ml-auto">
                                    {{ form.motivo.length }}/500 caracteres
                                </p>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones Adicionales <span class="text-gray-400">(Opcional)</span>
                            </label>
                            <textarea id="observaciones" v-model="form.observaciones" rows="4" maxlength="1000"
                                placeholder="Información adicional sobre el problema, síntomas, o requerimientos especiales..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark resize-none"
                                :class="{ 'border-red-500': form.errors.observaciones }"></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <p v-if="form.errors.observaciones" class="text-sm text-red-600">
                                    {{ form.errors.observaciones }}
                                </p>
                                <p class="text-xs text-gray-500 ml-auto">
                                    {{ form.observaciones.length }}/1000 caracteres
                                </p>
                            </div>
                        </div>

                        <!-- Advertencia si la cita está en proceso -->
                        <div v-if="cita.estado === 'en_proceso'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-800">Cita en Proceso</h4>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Esta cita está actualmente en proceso. Algunos cambios pueden afectar el diagnóstico o la
                                        orden de trabajo asociados.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Información del diagnóstico si existe -->
                        <div v-if="cita.diagnostico" class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-green-800">Diagnóstico Asociado</h4>
                                    <p class="text-sm text-green-700 mt-1">
                                        Esta cita tiene un diagnóstico asociado ({{ cita.diagnostico.codigo }}). Tenga precaución
                                        al modificar los datos.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <Link :href="route('admin.citas.show', cita.id)"
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition duration-200">
                                Cancelar
                            </Link>
                            <button type="submit" :disabled="form.processing"
                                class="px-6 py-2 bg-taller-blue-dark text-white rounded-lg hover:bg-taller-blue-light font-semibold transition duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Información adicional -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800">Información Importante</h4>
                            <ul class="mt-2 text-sm text-blue-700 space-y-1 list-disc list-inside">
                                <li>Asegúrese de que la fecha y hora no estén ya ocupadas por otra cita</li>
                                <li>El cambio de cliente o vehículo puede afectar el historial de la cita</li>
                                <li>Si la cita tiene un diagnóstico asociado, algunos cambios pueden requerir actualizar el
                                    diagnóstico</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
