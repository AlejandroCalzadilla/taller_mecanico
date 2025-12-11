<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

// Importación de Heroicons
import {
  ArrowLeftIcon,
  PencilSquareIcon,
  BanknotesIcon,
  UserIcon,
  TruckIcon,
  WrenchScrewdriverIcon,
  ClipboardDocumentCheckIcon,
  CreditCardIcon,
  CalendarDaysIcon,
  CheckCircleIcon,
  ClockIcon,
  CurrencyDollarIcon,
  DocumentTextIcon,
  QrCodeIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  pago: Object,
  planPagos: Array,
  metodosPago: Object,
  usuarios: Array
})

const formPago = useForm({
  monto: null,
  metodo_pago: '',
  numero_comprobante: '',
  banco: '',
  referencia: '',
  recibido_por: '',
  observaciones: ''
})

// Computed properties para las propiedades que faltan
const porcentajePagado = computed(() => {
  if (!props.pago.monto_total || props.pago.monto_total === 0) return 0
  const porcentaje = ((props.pago.monto_pagado || 0) / props.pago.monto_total) * 100
  return Math.min(Math.round(porcentaje), 100)
})

const esAlCredito = computed(() => {
  return props.pago.tipo_pago === 'credito'
})

const cuotasPendientes = computed(() => {
  return props.pago.numero_cuotas - props.pago.cuotas_pagadas
})

const montoCuota = computed(() => {
  if (!esAlCredito.value) return 0
  return props.pago.monto_total / props.pago.numero_cuotas
})

const getEstadoTexto = (estado) => {
  const estados = {
    'pendiente': 'Pendiente',
    'pagado_parcial': 'Pagado Parcialmente',
    'pagado_total': 'Pagado Totalmente',
    'vencido': 'Vencido'
  }
  return estados[estado] || estado
}

const getTipoPagoTexto = (tipoPago) => {
  const tipos = {
    'contado': 'Al Contado',
    'credito': 'Al Crédito'
  }
  return tipos[tipoPago] || tipoPago
}

const getMetodoPagoTexto = (metodoPago) => {
  const metodos = {
    'efectivo': 'Efectivo',
    'qr': 'QR'
  }
  return metodos[metodoPago] || metodoPago
}

const esEfectivo = (metodoPago) => {
  return metodoPago === 'efectivo'
}

const registrarPago = () => {
  formPago.post(route('admin.pagos.registrar', props.pago.id), {
    preserveScroll: true,
    onSuccess: () => {
      formPago.reset()
    }
  })
}

const getEstadoBadgeClass = (estado) => {
  const classes = {
    'pendiente': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    'pagado_parcial': 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'pagado_total': 'bg-green-50 text-green-700 ring-green-600/20',
    'vencido': 'bg-red-50 text-red-700 ring-red-600/20'
  }
  return `inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset ${classes[estado] || 'bg-gray-50 text-gray-600'}`
}

const formatMoney = (amount) => {
  if (!amount) return 'S/. 0.00'
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount)
}

const formatDateTime = (date, time) => {
  if (!date) return '-'
  const dateObj = new Date(date + 'T00:00:00')
  return `${dateObj.toLocaleDateString('es-ES')} ${time || ''}`
}
</script>

<template>
  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">

      <div class="mb-6">
        <Link :href="route('admin.pagos.index')" class="flex items-center text-sm text-gray-500 hover:text-taller-blue-dark transition-colors">
            <ArrowLeftIcon class="h-4 w-4 mr-1" />
            Volver a Pagos
        </Link>
      </div>

      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
          <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight flex items-center gap-3">
            <div class="p-2 bg-white rounded-lg border border-gray-200 shadow-sm">
                <BanknotesIcon class="h-8 w-8 text-taller-blue-dark" />
            </div>
            Detalle de Pago <span class="text-gray-400 font-mono text-xl ml-2">{{ pago.codigo }}</span>
          </h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
          <Link
            :href="route('admin.pagos.edit', pago.id)"
            class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
          >
            <PencilSquareIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" />
            Editar Pago
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-6">

          <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <div>
                        <span :class="getEstadoBadgeClass(pago.estado)">
                            {{ getEstadoTexto(pago.estado) }}
                        </span>
                        <p class="mt-2 text-sm text-gray-500 flex items-center gap-2">
                            <CreditCardIcon class="h-4 w-4" />
                            {{ getTipoPagoTexto(pago.tipo_pago) }}
                            <span v-if="esAlCredito" class="text-xs bg-gray-100 px-2 py-0.5 rounded-full">
                                {{ pago.numero_cuotas }} cuotas
                            </span>
                        </p>
                    </div>
                    <div class="mt-4 sm:mt-0 text-right">
                        <p class="text-sm text-gray-500 mb-1">Monto Total</p>
                        <p class="text-3xl font-bold text-gray-900">{{ formatMoney(pago.monto_total) }}</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <div class="flex justify-between text-sm font-medium mb-2">
                        <span class="text-gray-700">Progreso del pago</span>
                        <span class="text-taller-blue-dark">{{ porcentajePagado }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div
                            class="h-3 rounded-full transition-all duration-500 shadow-sm"
                            :class="{
                                'bg-green-500': porcentajePagado >= 100,
                                'bg-taller-blue-dark': porcentajePagado < 100
                            }"
                            :style="{ width: porcentajePagado + '%' }"
                        ></div>
                    </div>
                    <div class="mt-3 flex justify-between text-sm">
                        <div class="text-green-600 font-semibold flex items-center gap-1">
                            <CheckCircleIcon class="h-4 w-4" />
                            Pagado: {{ formatMoney(pago.monto_pagado || 0) }}
                        </div>
                        <div class="text-red-600 font-semibold flex items-center gap-1">
                            <ExclamationTriangleIcon class="h-4 w-4" />
                            Pendiente: {{ formatMoney(pago.monto_pendiente || 0) }}
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4 border-b border-gray-100 pb-2">
                Contexto de la Orden
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-blue-50 rounded-lg text-taller-blue-dark">
                        <UserIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Cliente</p>
                        <p class="font-medium text-gray-900">{{ pago.orden_trabajo?.diagnostico?.cita?.cliente?.nombre || 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ pago.orden_trabajo?.diagnostico?.cita?.cliente?.email || 'N/A' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                        <TruckIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Vehículo</p>
                        <p class="font-medium text-gray-900">
                            {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.marca || 'N/A' }}
                            {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.modelo || '' }}
                        </p>
                        <p class="text-xs font-mono bg-gray-100 inline-block px-1 rounded text-gray-600">
                            {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.placa || 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2 bg-gray-50 rounded-lg text-gray-600">
                        <WrenchScrewdriverIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Mecánico Responsable</p>
                        <p class="font-medium text-gray-900">{{ pago.orden_trabajo?.mecanico?.nombre || 'N/A' }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <ClipboardDocumentCheckIcon class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase">Orden de Trabajo</p>
                        <Link
                          v-if="pago.orden_trabajo?.id"
                          :href="route('admin.ordenes.show', pago.orden_trabajo.id)"
                          class="font-medium text-taller-blue-dark hover:underline"
                        >
                            {{ pago.orden_trabajo?.codigo || 'N/A' }}
                        </Link>
                        <span v-else class="font-medium text-gray-500">N/A</span>
                    </div>
                </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <h3 class="text-base font-semibold leading-6 text-gray-900 flex items-center gap-2">
                    <ClockIcon class="h-5 w-5 text-gray-500" />
                    Historial de Transacciones
                </h3>
            </div>

            <div v-if="pago.detalles && pago.detalles.length > 0" class="divide-y divide-gray-100">
                <div v-for="detalle in pago.detalles" :key="detalle.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex gap-3">
                            <div class="mt-1">
                                <div v-if="esEfectivo(detalle.metodo_pago)" class="p-1.5 rounded-full bg-green-100 text-green-600">
                                    <BanknotesIcon class="h-4 w-4" />
                                </div>
                                <div v-else class="p-1.5 rounded-full bg-purple-100 text-purple-600">
                                    <QrCodeIcon class="h-4 w-4" />
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Pago de Cuota {{ detalle.numero_cuota }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ formatDateTime(detalle.fecha_pago, detalle.hora_pago) }}
                                </p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset bg-gray-50 text-gray-600 ring-gray-500/10">
                                        {{ getMetodoPagoTexto(detalle.metodo_pago) }}
                                    </span>
                                    <span v-if="detalle.numero_comprobante" class="text-xs text-gray-500">
                                        Ref: {{ detalle.numero_comprobante }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">{{ formatMoney(detalle.monto) }}</p>
                            <p class="text-xs text-gray-500">Recibido por: {{ detalle.recibido_por?.nombre || 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="px-6 py-10 text-center text-gray-500">
                <CurrencyDollarIcon class="h-10 w-10 mx-auto text-gray-300 mb-2" />
                <p>No se han registrado pagos todavía.</p>
            </div>
          </div>

          <div v-if="esAlCredito && planPagos && planPagos.length > 0" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
             <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-base font-semibold leading-6 text-gray-900 flex items-center gap-2">
                    <CalendarDaysIcon class="h-5 w-5 text-gray-500" />
                    Cronograma de Cuotas
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                <div v-for="cuota in planPagos" :key="cuota.numero_cuota"
                     class="px-6 py-3 flex items-center justify-between"
                     :class="{'bg-green-50/50': cuota.pagada}">
                    <div class="flex items-center gap-3">
                        <CheckCircleIcon v-if="cuota.pagada" class="h-5 w-5 text-green-500" />
                        <ClockIcon v-else class="h-5 w-5 text-gray-300" />
                        <div>
                            <p class="text-sm font-medium text-gray-900">Cuota #{{ cuota.numero_cuota }}</p>
                            <p class="text-xs text-gray-500">Vence: {{ cuota.fecha_vencimiento }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">{{ formatMoney(cuota.monto) }}</p>
                        <span v-if="cuota.pagada" class="text-xs text-green-600 font-medium">Pagado</span>
                        <span v-else class="text-xs text-orange-600 font-medium">Pendiente</span>
                    </div>
                </div>
            </div>
          </div>

        </div>

        <div class="space-y-6">

          <div class="bg-white rounded-xl shadow-lg ring-1 ring-gray-900/5 p-6 border-t-4 border-t-taller-blue-dark">
            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <CurrencyDollarIcon class="h-6 w-6 text-taller-blue-dark" />
                Registrar Nuevo Pago
            </h3>

            <form @submit.prevent="registrarPago" class="space-y-4">
              <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Monto a Pagar</label>
                <div class="relative mt-2 rounded-md shadow-sm">
                  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="text-gray-500 sm:text-sm">S/.</span>
                  </div>
                  <input
                    type="number"
                    v-model="formPago.monto"
                    :max="pago.monto_pendiente"
                    step="0.01"
                    class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6 font-bold"
                    placeholder="0.00"
                    required
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 text-right">
                    Máx: {{ formatMoney(pago.monto_pendiente || 0) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Método de Pago</label>
                <select v-model="formPago.metodo_pago" class="mt-2 block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm sm:leading-6" required>
                  <option value="">Seleccionar...</option>
                  <option v-for="(label, value) in metodosPago" :key="value" :value="value">{{ label }}</option>
                </select>
              </div>

              <div v-if="formPago.metodo_pago === 'qr'" class="p-3 bg-gray-50 rounded-md space-y-3 border border-gray-200">
                <div>
                    <label class="block text-xs font-medium text-gray-700">Banco Emisor</label>
                    <input type="text" v-model="formPago.banco" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark text-xs" required />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Código de Referencia</label>
                    <input type="text" v-model="formPago.referencia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark text-xs" required />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">N° Comprobante (Opcional)</label>
                <div class="relative mt-2">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <DocumentTextIcon class="h-4 w-4 text-gray-400" />
                    </div>
                    <input type="text" v-model="formPago.numero_comprobante" class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm sm:leading-6" />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Recibido por</label>
                <div class="relative mt-2">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <UserIcon class="h-4 w-4 text-gray-400" />
                    </div>
                    <select v-model="formPago.recibido_por" class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm sm:leading-6" required>
                        <option value="">Seleccionar personal...</option>
                        <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                            {{ usuario.nombre }}
                        </option>
                    </select>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Observaciones</label>
                <textarea v-model="formPago.observaciones" rows="2" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"></textarea>
              </div>

              <button
                type="submit"
                :disabled="formPago.processing || !pago.monto_pendiente || pago.monto_pendiente <= 0"
                class="flex w-full justify-center rounded-md bg-taller-blue-dark px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-taller-blue-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-taller-blue-dark disabled:opacity-50 transition-all duration-200"
              >
                <BanknotesIcon class="h-5 w-5 mr-2" />
                {{ formPago.processing ? 'Procesando...' : 'Confirmar Pago' }}
              </button>
            </form>
          </div>

          <div v-if="esAlCredito" class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Resumen del Crédito</h3>
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase">Total Cuotas</p>
                    <p class="text-lg font-bold text-gray-900">{{ pago.numero_cuotas }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xs text-green-600 uppercase">Pagadas</p>
                    <p class="text-lg font-bold text-green-700">{{ pago.cuotas_pagadas || 0 }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-3">
                    <p class="text-xs text-red-600 uppercase">Pendientes</p>
                    <p class="text-lg font-bold text-red-700">{{ cuotasPendientes }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-600 uppercase">Valor Cuota</p>
                    <p class="text-sm font-bold text-blue-700">{{ formatMoney(montoCuota) }}</p>
                </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>
</template>
