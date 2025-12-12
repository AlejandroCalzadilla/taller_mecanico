<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'

// Importación de Iconos
import {
  BanknotesIcon,
  DocumentTextIcon,
  UserIcon,
  TruckIcon,
  CreditCardIcon,
  CalendarDaysIcon,
  XMarkIcon,
  CheckCircleIcon,
  CurrencyDollarIcon,
  ClipboardDocumentCheckIcon,
  CalculatorIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  ordenes: Array,
  tiposPago: Object,
  metodosPago: Object
})

const form = useForm({
  orden_trabajo_id: '',
  tipo_pago: 'contado',
  numero_cuotas: 1,
  fecha_vencimiento: '',
  observaciones: ''
})

const ordenSeleccionada = ref(null)

// Computed para verificar si hay órdenes disponibles
const hayOrdenesDisponibles = computed(() => {
  return props.ordenes && props.ordenes.length > 0
})

// Computed para el monto de la cuota
const montoCuota = computed(() => {
  if (!ordenSeleccionada.value || form.tipo_pago !== 'credito') return 0
  return ordenSeleccionada.value.subtotal / form.numero_cuotas
})

// Actualizar tarjeta de resumen al seleccionar orden
const onOrdenChange = () => {
  const orden = props.ordenes.find(o => o.id == form.orden_trabajo_id)
  ordenSeleccionada.value = orden || null

  // Si es crédito, establecer fecha de vencimiento por defecto (30 días desde hoy)
  if (form.tipo_pago === 'credito' && !form.fecha_vencimiento) {
    const fecha = new Date()
    fecha.setDate(fecha.getDate() + 30)
    form.fecha_vencimiento = fecha.toISOString().split('T')[0]
  }
}

// Formatear moneda
const formatMoney = (amount) => {
  if (!amount) return 'S/. 0.00'
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount)
}

// Formatear fecha
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const submit = () => {
  form.post(route('admin.pagos.store'))
}

// Inicializar fecha de vencimiento por defecto para crédito
onMounted(() => {
  if (form.tipo_pago === 'credito' && !form.fecha_vencimiento) {
    const fecha = new Date()
    fecha.setDate(fecha.getDate() + 30)
    form.fecha_vencimiento = fecha.toISOString().split('T')[0]
  }
})
</script>

<template>
  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">

      <!-- Mostrar errores flash -->
      <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-center gap-3">
          <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
          <p class="text-red-800 font-medium">{{ $page.props.flash.error }}</p>
        </div>
      </div>

      <!-- Mostrar mensajes de éxito flash -->
      <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
        <div class="flex items-center gap-3">
          <CheckCircleIcon class="h-6 w-6 text-green-600" />
          <p class="text-green-800 font-medium">{{ $page.props.flash.success }}</p>
        </div>
      </div>

      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight flex items-center gap-3">
            <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                <BanknotesIcon class="h-6 w-6 text-taller-blue-dark" />
            </div>
            Nuevo Registro de Pago
          </h2>
          <p class="mt-2 text-sm text-gray-500 ml-12">
            Inicia el proceso de cobranza para una orden de trabajo completada.
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <Link
            :href="route('admin.pagos.index')"
            class="group inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-red-600 transition-colors"
          >
            <XMarkIcon class="h-4 w-4 text-gray-500 group-hover:text-red-600" />
            Cancelar
          </Link>
        </div>
      </div>

      <div class="max-w-5xl mx-auto">
        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">

                <!-- Alerta si no hay órdenes disponibles -->
                <div v-if="!hayOrdenesDisponibles" class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                  <div class="flex items-start gap-3">
                    <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600 mt-0.5" />
                    <div>
                      <h3 class="text-sm font-semibold text-yellow-800">No hay órdenes disponibles</h3>
                      <p class="text-sm text-yellow-700 mt-1">
                        No se encontraron órdenes de trabajo completadas sin pagos asociados.
                      </p>
                      <div class="mt-3">
                        <Link
                          :href="route('admin.ordenes.index')"
                          class="inline-flex items-center gap-2 text-sm font-medium text-yellow-700 hover:text-yellow-800"
                        >
                          <DocumentTextIcon class="h-4 w-4" />
                          Ir a Órdenes de Trabajo
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4 flex items-center gap-2 border-b border-gray-100 pb-2">
                            <ClipboardDocumentCheckIcon class="h-5 w-5 text-gray-500" />
                            Orden de Trabajo Origen
                        </h3>

                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Seleccionar Orden Completada *</label>
                            <select
                                v-model="form.orden_trabajo_id"
                                @change="onOrdenChange"
                                class="block w-full rounded-md border-0 py-3 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                                :class="{ 'border-red-300': form.errors.orden_trabajo_id }"
                            >
                                <option value="">-- Selecciona una orden completada --</option>
                                <option
                                  v-for="orden in ordenes"
                                  :key="orden.id"
                                  :value="orden.id"
                                >
                                    {{ orden.codigo }} |
                                    {{ orden.diagnostico.cita.cliente.nombre }} -
                                    {{ formatMoney(orden.subtotal) }}
                                </option>
                            </select>
                            <p v-if="form.errors.orden_trabajo_id" class="mt-2 text-sm text-red-600">{{ form.errors.orden_trabajo_id }}</p>
                        </div>

                        <transition
                          enter-active-class="transition ease-out duration-200"
                          enter-from-class="opacity-0 translate-y-2"
                          enter-to-class="opacity-100 translate-y-0"
                        >
                            <div v-if="ordenSeleccionada" class="mt-6 rounded-xl border border-blue-100 bg-blue-50/50 p-5">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Resumen de la Orden</span>
                                    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-0.5 rounded border border-blue-200">
                                        {{ ordenSeleccionada.codigo }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="p-2 bg-white rounded-lg shadow-sm text-gray-500">
                                            <UserIcon class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Cliente</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ordenSeleccionada.diagnostico.cita.cliente.nombre }}</p>
                                            <p class="text-xs text-gray-500">{{ ordenSeleccionada.diagnostico.cita.cliente.email }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="p-2 bg-white rounded-lg shadow-sm text-gray-500">
                                            <TruckIcon class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Vehículo</p>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ ordenSeleccionada.diagnostico.cita.vehiculo.marca }} {{ ordenSeleccionada.diagnostico.cita.vehiculo.modelo }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ ordenSeleccionada.diagnostico.cita.vehiculo.placa }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="p-2 bg-white rounded-lg shadow-sm text-gray-500">
                                            <CalculatorIcon class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Servicios</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ ordenSeleccionada.servicios?.length || 0 }} servicios</p>
                                            <p class="text-xs text-gray-500">Mano de obra: {{ formatMoney(ordenSeleccionada.costo_mano_obra) }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3">
                                        <div class="p-2 bg-white rounded-lg shadow-sm text-gray-500">
                                            <CalendarDaysIcon class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Fecha Creación</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ formatDate(ordenSeleccionada.fecha_creacion) }}</p>
                                            <p class="text-xs text-gray-500">Estado: {{ ordenSeleccionada.estado }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-blue-100 flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-600">Monto Total a Cobrar:</span>
                                    <span class="text-2xl font-bold text-taller-blue-dark">{{ formatMoney(ordenSeleccionada.subtotal) }}</span>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-base font-semibold leading-6 text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-2">
                            <CreditCardIcon class="h-5 w-5 text-gray-500" />
                            Condiciones de Pago
                        </h3>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium leading-6 text-gray-900 mb-3">Modalidad *</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="tipo_pago" value="contado" v-model="form.tipo_pago" class="peer sr-only">
                                        <div class="rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 peer-checked:border-taller-blue-dark peer-checked:ring-1 peer-checked:ring-taller-blue-dark transition-all text-center">
                                            <CurrencyDollarIcon class="h-6 w-6 mx-auto mb-1 text-gray-400 peer-checked:text-taller-blue-dark" />
                                            <span class="block text-sm font-medium text-gray-900">Al Contado</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="tipo_pago" value="credito" v-model="form.tipo_pago" class="peer sr-only">
                                        <div class="rounded-lg border border-gray-200 bg-white p-4 hover:bg-gray-50 peer-checked:border-taller-blue-dark peer-checked:ring-1 peer-checked:ring-taller-blue-dark transition-all text-center">
                                            <CalendarDaysIcon class="h-6 w-6 mx-auto mb-1 text-gray-400 peer-checked:text-taller-blue-dark" />
                                            <span class="block text-sm font-medium text-gray-900">Crédito / Plazos</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <transition
                              enter-active-class="transition ease-out duration-200"
                              enter-from-class="opacity-0 -translate-y-2"
                              enter-to-class="opacity-100 translate-y-0"
                            >
                                <div v-if="form.tipo_pago === 'credito'" class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-yellow-50/50 p-4 rounded-lg border border-yellow-100">
                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-gray-900">Número de Cuotas *</label>
                                        <div class="relative mt-2">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <CalculatorIcon class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input
                                              type="number"
                                              v-model="form.numero_cuotas"
                                              min="2"
                                              max="24"
                                              class="block w-full rounded-md border-0 py-2.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                                            />
                                        </div>
                                        <p v-if="form.errors.numero_cuotas" class="mt-1 text-xs text-red-600">{{ form.errors.numero_cuotas }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium leading-6 text-gray-900">Fecha 1er Vencimiento *</label>
                                        <div class="relative mt-2">
                                            <input
                                              type="date"
                                              v-model="form.fecha_vencimiento"
                                              :min="new Date().toISOString().split('T')[0]"
                                              class="block w-full rounded-md border-0 py-2.5 pl-3 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                                            />
                                        </div>
                                        <p v-if="form.errors.fecha_vencimiento" class="mt-1 text-xs text-red-600">{{ form.errors.fecha_vencimiento }}</p>
                                    </div>
                                </div>
                            </transition>

                            <div>
                                <label class="block text-sm font-medium leading-6 text-gray-900 mb-2">Notas Adicionales</label>
                                <textarea
                                  v-model="form.observaciones"
                                  rows="3"
                                  class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                                  placeholder="Acuerdos especiales, condiciones de pago, observaciones..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden sticky top-6">
                    <div class="px-4 py-5 sm:p-6 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Resumen de Apertura</h3>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Monto Original</span>
                            <span class="font-medium text-gray-900">{{ ordenSeleccionada ? formatMoney(ordenSeleccionada.subtotal) : 'S/. 0.00' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Modalidad</span>
                            <span class="font-medium text-gray-900 capitalize">{{ form.tipo_pago === 'contado' ? 'Al Contado' : 'Crédito' }}</span>
                        </div>

                        <div v-if="form.tipo_pago === 'credito' && ordenSeleccionada" class="pt-3 border-t border-dashed border-gray-200 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">N° de Cuotas</span>
                                <span class="font-medium text-gray-900">{{ form.numero_cuotas }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-orange-600 font-medium">
                                <span>Monto por cuota</span>
                                <span>{{ formatMoney(montoCuota) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Primer vencimiento</span>
                                <span class="font-medium text-gray-900">{{ formatDate(form.fecha_vencimiento) }}</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                :disabled="form.processing || !ordenSeleccionada || !hayOrdenesDisponibles"
                                class="flex w-full justify-center items-center rounded-md bg-taller-blue-dark px-3 py-3 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-taller-blue-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-taller-blue-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                            >
                                <div v-if="form.processing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Generando...
                                </div>
                                <div v-else class="flex items-center gap-2">
                                    <CheckCircleIcon class="h-5 w-5" />
                                    Crear Registro de Pago
                                </div>
                            </button>
                            <p v-if="!ordenSeleccionada" class="text-xs text-center text-gray-400 mt-2">
                                Selecciona una orden primero
                            </p>
                            <p v-if="!hayOrdenesDisponibles" class="text-xs text-center text-red-400 mt-2">
                                No hay órdenes disponibles
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
