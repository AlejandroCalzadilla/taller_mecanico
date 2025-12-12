<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeftIcon,
    BanknotesIcon,
    CurrencyDollarIcon,
    QrCodeIcon,
    UserIcon,
    TruckIcon,
    CalendarDaysIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    BuildingLibraryIcon,
    DocumentTextIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    pago: Object,
    metodosPago: Object
});

// Estados reactivos
const metodoPagoSeleccionado = ref('qr');
const montoPago = ref(0);
const montoSugerido = ref(0);
const qrImage = ref(null);
const generandoQR = ref(false);
const errorQR = ref(null);
const nroPagoQR = ref('');
const procesandoPago = ref(false);
const mostrarConfirmacion = ref(false);

// Formularios
const formQR = ref({
    banco: '',
    referencia: '',
    numero_comprobante: '',
    observaciones: ''
});

const formEfectivo = ref({
    numero_comprobante: '',
    observaciones: ''
});

// Computed properties
const saldoPendiente = computed(() => {
    return parseFloat(props.pago.monto_pendiente) || 0;
});

const esCredito = computed(() => {
    return props.pago.tipo_pago === 'credito';
});

const proximaCuota = computed(() => {
    if (!esCredito.value) return null;
    return props.pago.cuotas_pagadas + 1;
});

const montoCuota = computed(() => {
    if (!esCredito.value) return saldoPendiente.value;
    return props.pago.monto_total / props.pago.numero_cuotas;
});

const montoMaximo = computed(() => {
    return Math.min(saldoPendiente.value, 10000); // Límite máximo por seguridad
});

// Métodos
const formatPrecio = (precio) => {
    if (precio === null || precio === undefined) return '0.00';
    const numero = typeof precio === 'string' ? parseFloat(precio) : precio;
    if (isNaN(numero)) return '0.00';
    return numero.toFixed(2);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const sugerirMonto = (tipo) => {
    switch (tipo) {
        case 'completo':
            montoSugerido.value = saldoPendiente.value;
            break;
        case 'cuota':
            montoSugerido.value = esCredito.value ? montoCuota.value : saldoPendiente.value;
            break;
        case 'mitad':
            montoSugerido.value = saldoPendiente.value / 2;
            break;
        case 'minimo':
            montoSugerido.value = Math.min(100, saldoPendiente.value);
            break;
    }
    montoPago.value = montoSugerido.value;

    // Si está en modo QR, regenerar el QR
    if (metodoPagoSeleccionado.value === 'qr' && montoPago.value > 0) {
        generarQR();
    }
};

const generarNumeroComprobante = () => {
    const prefix = metodoPagoSeleccionado.value === 'efectivo' ? 'EF' : 'QR';
    const fecha = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    return `${prefix}-${fecha}-${random}`;
};

// Función para generar QR desde el backend
const generarQR = async () => {
    if (!montoPago.value || montoPago.value <= 0) {
        errorQR.value = 'Primero establece un monto para generar el QR';
        return;
    }

    generandoQR.value = true;
    errorQR.value = null;

    try {
        const response = await axios.post(route('cliente.pagofacil.generar-qr'), {
            pago_id: props.pago.id,
            monto: montoPago.value
        });

        if (response.data.success) {
            qrImage.value = response.data.qr_image;
            nroPagoQR.value = response.data.nro_pago;

            // Auto-completar campos del formulario
            formQR.value.referencia = response.data.transaction_id;
            formQR.value.numero_comprobante = response.data.nro_pago;
            formQR.value.banco = 'PagoFácil';

            generandoQR.value = false;
            errorQR.value = null;
        } else {
            errorQR.value = response.data.message || 'Error al generar el QR';
            generandoQR.value = false;
        }
    } catch (error) {
        console.error('Error al generar QR:', error);
        errorQR.value = error.response?.data?.message || 'Error al generar el código QR. Intenta nuevamente.';
        generandoQR.value = false;
    }
};

const onMetodoPagoChange = () => {
    // Generar número de comprobante automático si está vacío
    if (!formQR.value.numero_comprobante && !formEfectivo.value.numero_comprobante) {
        const comprobante = generarNumeroComprobante();
        formQR.value.numero_comprobante = comprobante;
        formEfectivo.value.numero_comprobante = comprobante;
    }

    // Limpiar campos QR si se cambia a efectivo
    if (metodoPagoSeleccionado.value === 'efectivo') {
        formQR.value.banco = '';
        formQR.value.referencia = '';
        qrImage.value = null;
        errorQR.value = null;
    }

    // Si se cambia a QR y hay monto, generar QR automáticamente
    if (metodoPagoSeleccionado.value === 'qr' && montoPago.value && montoPago.value > 0) {
        generarQR();
    }
};

const validarFormulario = () => {
    if (!montoPago.value || montoPago.value <= 0) {
        errorQR.value = 'El monto debe ser mayor a 0';
        return false;
    }

    if (montoPago.value > saldoPendiente.value) {
        errorQR.value = `El monto no puede ser mayor al saldo pendiente (${formatPrecio(saldoPendiente.value)})`;
        return false;
    }

    if (metodoPagoSeleccionado.value === 'qr') {
        if (!formQR.value.banco) {
            errorQR.value = 'Selecciona un banco para el pago QR';
            return false;
        }
        if (!formQR.value.referencia) {
            errorQR.value = 'Ingresa la referencia del pago QR';
            return false;
        }
        if (!qrImage.value) {
            errorQR.value = 'Primero genera el código QR';
            return false;
        }
    }

    return true;
};

const procesarPagoQR = async () => {
    if (!validarFormulario()) return;

    procesandoPago.value = true;
    errorQR.value = null;

    try {
        const response = await router.post(route('cliente.pagos.procesar-qr', props.pago.id), {
            monto: montoPago.value,
            banco: formQR.value.banco,
            referencia: formQR.value.referencia,
            numero_comprobante: formQR.value.numero_comprobante,
            observaciones: formQR.value.observaciones
        });

        if (response.data.success) {
            mostrarConfirmacion.value = true;
        } else {
            errorQR.value = response.data.message || 'Error al procesar el pago';
        }
    } catch (error) {
        errorQR.value = 'Error de conexión. Intenta nuevamente.';
        console.error('Error procesando pago QR:', error);
    } finally {
        procesandoPago.value = false;
    }
};

const procesarPagoEfectivo = async () => {
    if (!validarFormulario()) return;

    procesandoPago.value = true;
    errorQR.value = null;

    try {
        const response = await router.post(route('cliente.pagos.confirmar-efectivo', props.pago.id), {
            monto: montoPago.value,
            numero_comprobante: formEfectivo.value.numero_comprobante,
            observaciones: formEfectivo.value.observaciones
        });

        if (response.data.success) {
            mostrarConfirmacion.value = true;
        } else {
            errorQR.value = response.data.message || 'Error al registrar el pago';
        }
    } catch (error) {
        errorQR.value = 'Error de conexión. Intenta nuevamente.';
        console.error('Error procesando pago efectivo:', error);
    } finally {
        procesandoPago.value = false;
    }
};

const submitPago = () => {
    if (metodoPagoSeleccionado.value === 'qr') {
        procesarPagoQR();
    } else {
        procesarPagoEfectivo();
    }
};

const irADetalles = () => {
    router.visit(route('cliente.pagos.show', props.pago.id));
};

// Inicialización
onMounted(() => {
    // Sugerir monto de cuota por defecto para créditos
    if (esCredito.value) {
        sugerirMonto('cuota');
    } else {
        sugerirMonto('completo');
    }

    // Generar número de comprobante inicial
    const comprobante = generarNumeroComprobante();
    formQR.value.numero_comprobante = comprobante;
    formEfectivo.value.numero_comprobante = comprobante;
});
</script>

<template>
    <Head :title="`Pagar - ${pago.codigo}`" />

    <AuthenticatedLayout>
        <div class="py-8 bg-taller-cream min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="mb-8 animate-fade-in-down">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('cliente.pagos.show', pago.id)"
                            class="inline-flex items-center gap-2 text-taller-blue-dark hover:text-taller-blue-light transition-colors duration-200 p-2 hover:bg-white rounded-lg"
                        >
                            <ArrowLeftIcon class="h-5 w-5" />
                            <span class="font-medium">Volver al Detalle</span>
                        </Link>
                        <div class="h-8 w-px bg-gray-300"></div>
                        <div>
                            <h1 class="text-3xl font-bold text-taller-black flex items-center gap-3">
                                <div class="p-2.5 bg-white rounded-xl shadow-sm border border-taller-blue-light/50">
                                    <BanknotesIcon class="h-8 w-8 text-taller-blue-dark" />
                                </div>
                                <span class="tracking-tight">Realizar Pago</span>
                            </h1>
                            <p class="mt-2 text-gray-600 ml-1">
                                Completa el pago para la orden {{ pago.orden_trabajo?.codigo }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Formulario de Pago -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Información del Pago -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 animate-fade-in-up delay-100">
                            <h2 class="text-xl font-bold text-taller-black mb-4 flex items-center gap-2">
                                <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                                Información del Pago
                            </h2>

                            <div class="space-y-4">
                                <!-- Monto a Pagar -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Monto a Pagar *
                                    </label>
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <CurrencyDollarIcon class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            type="number"
                                            v-model="montoPago"
                                            :max="montoMaximo"
                                            step="0.01"
                                            class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg font-semibold"
                                            placeholder="0.00"
                                        />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <span class="text-gray-500 font-medium">Bs.</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Máximo permitido: <span class="font-semibold">${{ formatPrecio(montoMaximo) }}</span>
                                    </p>

                                    <!-- Botones de montos sugeridos -->
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <button
                                            type="button"
                                            @click="sugerirMonto('completo')"
                                            class="px-3 py-1.5 text-xs font-medium bg-green-100 text-green-700 rounded-lg border border-green-200 hover:bg-green-200 transition-colors"
                                        >
                                            Pagar Total (${{ formatPrecio(saldoPendiente) }})
                                        </button>
                                        <button
                                            v-if="esCredito"
                                            type="button"
                                            @click="sugerirMonto('cuota')"
                                            class="px-3 py-1.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-lg border border-blue-200 hover:bg-blue-200 transition-colors"
                                        >
                                            Cuota {{ proximaCuota }} (${{ formatPrecio(montoCuota) }})
                                        </button>
                                        <button
                                            type="button"
                                            @click="sugerirMonto('mitad')"
                                            class="px-3 py-1.5 text-xs font-medium bg-yellow-100 text-yellow-700 rounded-lg border border-yellow-200 hover:bg-yellow-200 transition-colors"
                                        >
                                            Mitad (${{ formatPrecio(saldoPendiente / 2) }})
                                        </button>
                                        <button
                                            type="button"
                                            @click="sugerirMonto('minimo')"
                                            class="px-3 py-1.5 text-xs font-medium bg-purple-100 text-purple-700 rounded-lg border border-purple-200 hover:bg-purple-200 transition-colors"
                                        >
                                            Mínimo ($100.00)
                                        </button>
                                    </div>
                                </div>

                                <!-- Método de Pago -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Método de Pago *
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                name="metodo_pago"
                                                value="qr"
                                                v-model="metodoPagoSeleccionado"
                                                @change="onMetodoPagoChange"
                                                class="peer sr-only"
                                            />
                                            <div class="rounded-lg border-2 border-gray-200 bg-white p-4 hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all text-center">
                                                <QrCodeIcon class="h-6 w-6 mx-auto mb-2 text-gray-600 peer-checked:text-green-600" />
                                                <span class="block text-sm font-medium text-gray-900">Pago QR</span>
                                                <span class="text-xs text-gray-500">Transferencia/Pago móvil</span>
                                            </div>
                                        </label>
                                        <!-- <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                name="metodo_pago"
                                                value="efectivo"
                                                v-model="metodoPagoSeleccionado"
                                                @change="onMetodoPagoChange"
                                                class="peer sr-only"
                                            />
                                            <div class="rounded-lg border-2 border-gray-200 bg-white p-4 hover:bg-gray-50 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all text-center">
                                                <BanknotesIcon class="h-6 w-6 mx-auto mb-2 text-gray-600 peer-checked:text-green-600" />
                                                <span class="block text-sm font-medium text-gray-900">Efectivo</span>
                                                <span class="text-xs text-gray-500">En el taller</span>
                                            </div>
                                        </label> -->
                                    </div>
                                </div>

                                <!-- Campos específicos para QR -->
                                <div v-if="metodoPagoSeleccionado === 'qr'" class="space-y-4">

                                    <!-- Sección del Código QR -->
                                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200 text-center">
                                        <h4 class="text-sm font-semibold text-blue-900 flex items-center justify-center gap-2 mb-3">
                                            <QrCodeIcon class="h-4 w-4" />
                                            Código QR de Pago
                                        </h4>

                                        <div v-if="generandoQR" class="flex flex-col items-center justify-center py-8">
                                            <ArrowPathIcon class="h-8 w-8 text-blue-500 animate-spin mb-2" />
                                            <p class="text-sm text-blue-700">Generando código QR...</p>
                                        </div>

                                        <div v-else-if="qrImage" class="space-y-3">
                                            <div class="flex justify-center">
                                                <img
                                                    :src="qrImage"
                                                    alt="Código QR de pago"
                                                    class="w-48 h-48 border-2 border-white rounded-lg shadow-sm"
                                                />
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-gray-600 mb-1">Escanea este código con tu app de pagos</p>
                                                <p class="text-xs font-mono text-gray-700 bg-white px-2 py-1 rounded border">
                                                    Referencia: {{ nroPagoQR }}
                                                </p>
                                                <p class="text-xs text-gray-600 mt-1">Monto: ${{ formatPrecio(montoPago) }}</p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="generarQR"
                                                class="inline-flex items-center gap-1 px-3 py-1 text-xs bg-white text-blue-600 border border-blue-300 rounded hover:bg-blue-50 transition-colors"
                                            >
                                                <ArrowPathIcon class="h-3 w-3" />
                                                Regenerar QR
                                            </button>
                                        </div>

                                        <div v-else class="py-6">
                                            <p class="text-sm text-gray-600 mb-3">
                                                Establece un monto y haz clic para generar el código QR
                                            </p>
                                            <button
                                                type="button"
                                                @click="generarQR"
                                                :disabled="!montoPago || montoPago <= 0"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                                            >
                                                <QrCodeIcon class="h-4 w-4" />
                                                Generar Código QR
                                            </button>
                                        </div>

                                        <div v-if="errorQR" class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                                            {{ errorQR }}
                                        </div>
                                    </div>

                                    <!-- Información de la transacción QR -->
                                   <!--  <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2 mb-3">
                                            <BuildingLibraryIcon class="h-4 w-4" />
                                            Información de la Transacción
                                        </h4>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                                    Banco *
                                                </label>
                                                <select
                                                    v-model="formQR.banco"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                                    required
                                                >
                                                    <option value="">Seleccionar banco</option>
                                                    <option value="Tigo Money">Tigo Money</option>
                                                    <option value="BISA">BISA</option>
                                                    <option value="BNB">BNB</option>
                                                    <option value="BCP">BCP</option>
                                                    <option value="Mercantil Santa Cruz">Mercantil Santa Cruz</option>
                                                    <option value="Económico">Económico</option>
                                                    <option value="Fassil">Fassil</option>
                                                    <option value="Ganadero">Ganadero</option>
                                                    <option value="Unión">Unión</option>
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                                    Referencia/Código *
                                                </label>
                                                <input
                                                    type="text"
                                                    v-model="formQR.referencia"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm"
                                                    placeholder="Código de transacción"
                                                    required
                                                />
                                            </div>
                                        </div>
                                    </div> -->
                                </div>

                                <!-- Información para Efectivo -->
                                <div v-if="metodoPagoSeleccionado === 'efectivo'" class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <h4 class="text-sm font-semibold text-yellow-900 flex items-center gap-2 mb-3">
                                        <BanknotesIcon class="h-4 w-4" />
                                        Información para Pago en Efectivo
                                    </h4>
                                    <p class="text-sm text-yellow-700 mb-3">
                                        Al confirmar este pago, se registrará una solicitud. Deberás acercarte al taller para completar el pago en efectivo.
                                    </p>
                                    <div class="bg-white p-3 rounded border border-yellow-100">
                                        <p class="text-xs font-medium text-gray-700">Dirección del Taller:</p>
                                        <p class="text-xs text-gray-600">Av. Principal #123, Zona Central</p>
                                        <p class="text-xs text-gray-600">Horario: Lunes a Viernes 8:00 - 18:00</p>
                                        <p class="text-xs text-gray-600">Sábados: 8:00 - 12:00</p>
                                    </div>
                                </div>

                                <!-- Número de Comprobante -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        N° Comprobante (Opcional)
                                    </label>
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <DocumentTextIcon class="h-4 w-4 text-gray-400" />
                                        </div>
                                        <input
                                            type="text"
                                            :value="metodoPagoSeleccionado === 'qr' ? formQR.numero_comprobante : formEfectivo.value.numero_comprobante"
                                            @input="metodoPagoSeleccionado === 'qr' ? formQR.numero_comprobante = $event.target.value : formEfectivo.value.numero_comprobante = $event.target.value"
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm"
                                            placeholder="Generado automáticamente"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Se genera automáticamente si se deja vacío
                                    </p>
                                </div>

                                <!-- Observaciones -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Observaciones
                                    </label>
                                    <textarea
                                        :value="metodoPagoSeleccionado === 'qr' ? formQR.observaciones : formEfectivo.value.observaciones"
                                        @input="metodoPagoSeleccionado === 'qr' ? formQR.observaciones = $event.target.value : formEfectivo.value.observaciones = $event.target.value"
                                        rows="3"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                        placeholder="Notas adicionales sobre este pago..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 animate-fade-in-up delay-200">
                            <button
                                @click="submitPago"
                                :disabled="procesandoPago || !montoPago || montoPago <= 0 || (metodoPagoSeleccionado === 'qr' && !qrImage)"
                                class="w-full py-4 px-6 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg shadow-sm transition-all duration-200 flex items-center justify-center gap-2 text-lg"
                            >
                                <BanknotesIcon class="h-6 w-6" />
                                <span v-if="procesandoPago">Procesando Pago...</span>
                                <span v-else>
                                    {{ metodoPagoSeleccionado === 'qr' ? 'Confirmar Pago QR' : 'Solicitar Pago en Efectivo' }}
                                    de ${{ formatPrecio(montoPago) }}
                                </span>
                            </button>

                            <div v-if="errorQR" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                                <ExclamationTriangleIcon class="h-4 w-4 inline mr-1" />
                                {{ errorQR }}
                            </div>
                        </div>
                    </div>

                    <!-- Panel Lateral - Información -->
                    <div class="space-y-6">
                        <!-- Resumen del Pago -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 animate-fade-in-up delay-300">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <DocumentTextIcon class="h-5 w-5 text-blue-600" />
                                Resumen del Pago
                            </h3>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Cliente:</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ pago.orden_trabajo?.diagnostico?.cita?.cliente?.nombre }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Vehículo:</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.marca }}
                                        {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.modelo }}
                                    </span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Placa:</span>
                                    <span class="text-sm font-mono font-medium text-gray-900">
                                        {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.placa }}
                                    </span>
                                </div>

                                <div class="border-t border-gray-200 pt-3">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">Total Orden:</span>
                                        <span class="text-lg font-bold text-gray-900">
                                            ${{ formatPrecio(pago.monto_total) }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-green-600">Pagado:</span>
                                        <span class="text-lg font-bold text-green-600">
                                            ${{ formatPrecio(pago.monto_pagado) }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-red-600">Pendiente:</span>
                                        <span class="text-lg font-bold text-red-600">
                                            ${{ formatPrecio(saldoPendiente) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Información de Crédito (si aplica) -->
                                <div v-if="esCredito" class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <h4 class="text-sm font-semibold text-yellow-900 mb-2">Plan de Crédito</h4>
                                    <div class="space-y-1 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-yellow-700">Total Cuotas:</span>
                                            <span class="font-medium">{{ pago.numero_cuotas }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-yellow-700">Cuotas Pagadas:</span>
                                            <span class="font-medium text-green-600">{{ pago.cuotas_pagadas }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-yellow-700">Próxima Cuota:</span>
                                            <span class="font-medium">#{{ proximaCuota }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-yellow-700">Monto por Cuota:</span>
                                            <span class="font-medium">${{ formatPrecio(montoCuota) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 animate-fade-in-up delay-400">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <UserIcon class="h-5 w-5 text-taller-blue-dark" />
                                Contacto y Soporte
                            </h3>

                            <div class="space-y-3 text-sm">
                                <p class="text-gray-600">
                                    Si tienes problemas con el pago, contáctanos:
                                </p>
                                <div class="space-y-2">
                                    <p class="font-medium text-gray-900">Taller Mecánico</p>
                                    <p class="text-gray-600">📞 Tel: +591 77777777</p>
                                    <p class="text-gray-600">📧 Email: contacto@tallermecanico.com</p>
                                    <p class="text-gray-600">🕒 Horario: Lunes a Viernes 8:00 - 18:00</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <div v-if="mostrarConfirmacion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 animate-fade-in">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md animate-scale-in text-center">
                <CheckCircleIcon class="h-16 w-16 text-green-500 mx-auto mb-4" />
                <h3 class="text-xl font-bold text-gray-900 mb-2">¡Pago Registrado Exitosamente!</h3>
                <p class="text-gray-600 mb-6">
                    {{ metodoPagoSeleccionado === 'qr'
                        ? 'Tu pago QR ha sido procesado. El monto se reflejará en tu cuenta una vez confirmado por el sistema.'
                        : 'Tu solicitud de pago en efectivo ha sido registrada. Por favor acércate al taller para completar el pago.'
                    }}
                </p>
                <div class="space-y-3">
                    <button
                        @click="irADetalles"
                        class="w-full py-3 px-4 bg-taller-blue-dark text-white font-semibold rounded-lg hover:bg-taller-blue-light transition-colors"
                    >
                        Ver Detalles del Pago
                    </button>
                    <button
                        @click="mostrarConfirmacion = false"
                        class="w-full py-3 px-4 text-gray-600 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors"
                    >
                        Cerrar
                    </button>
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

@keyframes fade-in {
    0% { opacity: 0; }
    100% { opacity: 1; }
}

@keyframes scale-in {
    0% { opacity: 0; transform: scale(0.9); }
    100% { opacity: 1; transform: scale(1); }
}

.animate-fade-in-down {
    animation: fade-in-down 0.6s ease-out forwards;
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s ease-out forwards;
    opacity: 0;
    animation-fill-mode: forwards;
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out forwards;
}

.animate-scale-in {
    animation: scale-in 0.3s ease-out forwards;
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-400 { animation-delay: 0.4s; }
</style>
