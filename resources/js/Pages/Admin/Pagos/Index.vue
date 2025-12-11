<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { debounce } from 'lodash'
import Pagination from '@/Components/Pagination.vue'

// Importación directa de Heroicons
import {
  PlusIcon,
  ExclamationTriangleIcon,
  BanknotesIcon,
  CheckCircleIcon,
  ClockIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  EyeIcon,
  PencilSquareIcon,
  XMarkIcon,
  CurrencyDollarIcon,
  CalendarDaysIcon,
  UserIcon,
  QrCodeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  pagos: Object,
  filters: Object,
  estados: Object,
  tiposPago: Object,
  estadisticas: Object
})

// Uso de useForm para manejar el estado de los filtros reactivamente
const filtersForm = ref({
  search: props.filters.search || '',
  estado: props.filters.estado || '',
  tipo_pago: props.filters.tipo_pago || ''
})

// Lógica de filtrado optimizada (Inertia Router)
const updateFilters = debounce(() => {
  router.get(route('admin.pagos.index'), filtersForm.value, {
    preserveState: true,
    replace: true,
    preserveScroll: true
  })
}, 300)

// Observador para cambios en los filtros
watch(filtersForm, () => {
  updateFilters()
}, { deep: true })

const clearFilters = () => {
  filtersForm.value = { search: '', estado: '', tipo_pago: '' }
}

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatMoney = (amount) => {
  if (!amount) return 'S/. 0.00'
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2
  }).format(amount)
}

// Computed properties para las propiedades que faltan en el modelo
const getPorcentajePagado = (pago) => {
  if (!pago.monto_total || pago.monto_total === 0) return 0
  const porcentaje = (pago.monto_pagado / pago.monto_total) * 100
  return Math.min(Math.round(porcentaje), 100) // Asegurar que no pase del 100%
}

const getEstaVencido = (pago) => {
  if (!pago.fecha_vencimiento) return false
  const hoy = new Date()
  const vencimiento = new Date(pago.fecha_vencimiento)
  // Considerar vencido si la fecha pasó y no está pagado totalmente
  return vencimiento < hoy && pago.estado !== 'pagado_total'
}

const getTipoPagoTexto = (tipoPago) => {
  const tipos = {
    'contado': 'Al Contado',
    'credito': 'Al Crédito'
  }
  return tipos[tipoPago] || tipoPago
}

const getEstadoTexto = (estado) => {
  const estados = {
    'pendiente': 'Pendiente',
    'pagado_parcial': 'Pagado Parcialmente',
    'pagado_total': 'Pagado Totalmente',
    'vencido': 'Vencido'
  }
  return estados[estado] || estado
}

// Estilos de Badges
const getEstadoBadgeClass = (estado) => {
  const classes = {
    'pendiente': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    'pagado_parcial': 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'pagado_total': 'bg-green-50 text-green-700 ring-green-600/20',
    'vencido': 'bg-red-50 text-red-700 ring-red-600/20'
  }
  return `inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${classes[estado] || 'bg-gray-50 text-gray-600 ring-gray-500/10'}`
}

const getTipoPagoBadgeClass = (tipo) => {
  return tipo === 'contado'
    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/10'
    : 'bg-indigo-50 text-indigo-700 ring-indigo-600/10'
}

// Computed para nombres de estadísticas (compatibilidad)
const estadisticasDisplay = computed(() => {
  return {
    total: props.estadisticas.total_pagos || props.estadisticas.total || 0,
    completos: props.estadisticas.pagos_completos || props.estadisticas.completos || 0,
    parciales: props.estadisticas.pagos_parciales || props.estadisticas.parciales || 0,
    vencidos: props.estadisticas.pagos_vencidos || props.estadisticas.vencidos || 0
  }
})
</script>

<template>
  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">

      <!-- Header -->
      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold leading-7 text-gray-900 flex items-center gap-2">
            <BanknotesIcon class="h-8 w-8 text-taller-blue-dark" />
            Gestión de Pagos
          </h1>
          <p class="mt-2 text-sm text-gray-500">
            Administra y monitorea el flujo de caja y cobranzas.
          </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
          <Link
            :href="route('admin.pagos.vencidos')"
            class="inline-flex items-center justify-center rounded-lg bg-white border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 shadow-sm hover:bg-red-50 transition-all gap-2"
          >
            <ExclamationTriangleIcon class="h-5 w-5" />
            Ver Vencidos
          </Link>
          <Link
            :href="route('admin.pagos.create')"
            class="inline-flex items-center justify-center rounded-lg bg-taller-blue-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-taller-blue-dark transition-all gap-2"
          >
            <PlusIcon class="h-5 w-5" />
            Registrar Pago
          </Link>
        </div>
      </div>

      <!-- Estadísticas (KPI Cards) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between group hover:border-taller-blue-light transition-colors">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Registrado</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ estadisticasDisplay.total }}</p>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg text-taller-blue-dark group-hover:bg-blue-100 transition-colors">
            <CurrencyDollarIcon class="h-6 w-6" />
          </div>
        </div>

        <!-- Completados -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between border-b-4 border-b-green-500">
          <div>
            <p class="text-sm font-medium text-gray-500">Pagados Total</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ estadisticasDisplay.completos }}</p>
          </div>
          <div class="p-3 bg-green-50 rounded-lg text-green-600">
            <CheckCircleIcon class="h-6 w-6" />
          </div>
        </div>

        <!-- Parciales -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between border-b-4 border-b-yellow-400">
          <div>
            <p class="text-sm font-medium text-gray-500">Pagos Parciales</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ estadisticasDisplay.parciales }}</p>
          </div>
          <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600">
            <ClockIcon class="h-6 w-6" />
          </div>
        </div>

        <!-- Vencidos -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between border-b-4 border-b-red-500 bg-red-50/30">
          <div>
            <p class="text-sm font-medium text-red-600">Pagos Vencidos</p>
            <p class="text-2xl font-bold text-red-700 mt-1">{{ estadisticasDisplay.vencidos }}</p>
          </div>
          <div class="p-3 bg-white rounded-lg text-red-600 shadow-sm">
            <ExclamationTriangleIcon class="h-6 w-6" />
          </div>
        </div>
      </div>

      <!-- Filtros y Tabla -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Barra de Filtros -->
        <div class="p-5 border-b border-gray-100 bg-white flex flex-col md:flex-row gap-4 justify-between items-center">

          <!-- Búsqueda -->
          <div class="relative w-full md:max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
            </div>
            <input
              type="text"
              v-model="filtersForm.search"
              placeholder="Buscar por código, cliente o placa..."
              class="block w-full rounded-lg border-0 py-2.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6 bg-gray-50 focus:bg-white transition-colors"
            />
          </div>

          <!-- Selects -->
          <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="flex items-center gap-2 w-full">
              <FunnelIcon class="h-5 w-5 text-gray-400 hidden sm:block" />

              <select
                v-model="filtersForm.estado"
                class="block w-full md:w-40 rounded-lg border-0 py-2.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
              >
                <option value="">Estado: Todos</option>
                <option v-for="(label, value) in estados" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>

              <select
                v-model="filtersForm.tipo_pago"
                class="block w-full md:w-40 rounded-lg border-0 py-2.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
              >
                <option value="">Tipo: Todos</option>
                <option v-for="(label, value) in tiposPago" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Botón Limpiar -->
            <button
              v-if="filtersForm.search || filtersForm.estado || filtersForm.tipo_pago"
              @click="clearFilters"
              class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
              title="Limpiar filtros"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
              <tr>
                <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Código</th>
                <th scope="col" class="px-3 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cliente / Vehículo</th>
                <th scope="col" class="px-3 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
                <th scope="col" class="px-3 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 min-w-[150px]">Progreso Pago</th>
                <th scope="col" class="px-3 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo</th>
                <th scope="col" class="px-3 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
                <th scope="col" class="px-3 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Vencimiento</th>
                <th scope="col" class="relative py-4 pl-3 pr-6"><span class="sr-only">Acciones</span></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr
                v-for="pago in pagos.data"
                :key="pago.id"
                class="hover:bg-gray-50/80 transition-colors group"
              >
                <!-- Código -->
                <td class="whitespace-nowrap py-4 pl-6 pr-3">
                  <div class="flex items-center gap-2">
                    <QrCodeIcon class="h-4 w-4 text-gray-400" />
                    <span class="font-mono text-sm font-medium text-gray-900">
                      {{ pago.codigo || 'N/A' }}
                    </span>
                  </div>
                </td>

                <!-- Cliente y Vehículo -->
                <td class="whitespace-nowrap px-3 py-4">
                  <div class="flex flex-col">
                    <div class="flex items-center gap-1.5 text-sm font-medium text-gray-900">
                      <UserIcon class="h-3.5 w-3.5 text-gray-400" />
                      {{ pago.orden_trabajo?.diagnostico?.cita?.cliente?.nombre || 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5 ml-5">
                      {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.marca || 'N/A' }}
                      {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.modelo || '' }}
                      <span class="text-gray-300 mx-1">|</span>
                      {{ pago.orden_trabajo?.diagnostico?.cita?.vehiculo?.placa || 'N/A' }}
                    </div>
                  </div>
                </td>

                <!-- Monto Total -->
                <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-gray-900">
                  {{ formatMoney(pago.monto_total) }}
                </td>

                <!-- Barra de Progreso de Pago -->
                <td class="whitespace-nowrap px-3 py-4">
                  <div class="w-full max-w-[140px]">
                    <div class="flex justify-between text-xs mb-1">
                      <span class="font-medium text-gray-700">
                        {{ formatMoney(pago.monto_pagado) }}
                      </span>
                      <span class="text-gray-500">
                        {{ getPorcentajePagado(pago) }}%
                      </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                      <div
                        class="h-2 rounded-full transition-all duration-500"
                        :class="{
                          'bg-green-500': getPorcentajePagado(pago) >= 100,
                          'bg-yellow-500': getPorcentajePagado(pago) < 100 && getPorcentajePagado(pago) > 0,
                          'bg-gray-400': getPorcentajePagado(pago) == 0
                        }"
                        :style="{ width: `${getPorcentajePagado(pago)}%` }"
                      ></div>
                    </div>
                  </div>
                </td>

                <!-- Tipo -->
                <td class="whitespace-nowrap px-3 py-4">
                  <span
                    :class="`inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${getTipoPagoBadgeClass(pago.tipo_pago)}`"
                  >
                    {{ getTipoPagoTexto(pago.tipo_pago) }}
                  </span>
                </td>

                <!-- Estado -->
                <td class="whitespace-nowrap px-3 py-4">
                  <span :class="getEstadoBadgeClass(pago.estado)">
                    {{ getEstadoTexto(pago.estado) }}
                  </span>
                </td>

                <!-- Vencimiento -->
                <td class="whitespace-nowrap px-3 py-4">
                  <div
                    v-if="pago.fecha_vencimiento"
                    class="flex items-center gap-1.5 text-sm"
                    :class="getEstaVencido(pago) ? 'text-red-600 font-semibold' : 'text-gray-600'"
                  >
                    <CalendarDaysIcon class="h-4 w-4" />
                    {{ formatDate(pago.fecha_vencimiento) }}
                    <ExclamationTriangleIcon
                      v-if="getEstaVencido(pago)"
                      class="h-4 w-4 text-red-500"
                      title="Vencido"
                    />
                  </div>
                  <span v-else class="text-gray-400 text-xs">-</span>
                </td>

                <!-- Acciones -->
                <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                  <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                    <Link
                      :href="route('admin.pagos.show', pago.id)"
                      class="p-1.5 text-gray-400 hover:text-taller-blue-dark hover:bg-blue-50 rounded-md transition-colors"
                      title="Ver Detalles"
                    >
                      <EyeIcon class="h-5 w-5" />
                    </Link>
                    <!-- <Link
                      :href="route('admin.pagos.edit', pago.id)"
                      class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-md transition-colors"
                      title="Editar Pago"
                    >
                      <PencilSquareIcon class="h-5 w-5" />
                    </Link> -->
                    <Link
                    :href="route('admin.pagos.cobrar', pago.id)"
                    class="flex items-center gap-1 px-3 py-2 bg-taller-blue-dark border border-transparent rounded-lg text-white text-xs hover:bg-taller-blue-light shadow-sm transition-all"
                    title="Registrar Cobro"
                    >
                    <BanknotesIcon class="h-4 w-4" />
                    </Link>
                  </div>
                </td>
              </tr>

              <!-- Estado Vacío -->
              <tr v-if="pagos.data.length === 0">
                <td colspan="8" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <div class="p-3 rounded-full bg-gray-100 mb-4">
                      <BanknotesIcon class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-medium text-gray-900">No se encontraron pagos</h3>
                    <p class="mt-1 text-sm text-gray-500">
                      {{ filtersForm.search || filtersForm.estado || filtersForm.tipo_pago ?
                         'Intenta ajustar los filtros de búsqueda.' :
                         'No hay pagos registrados en el sistema.' }}
                    </p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="border-t border-gray-200 bg-gray-50">
          <Pagination :links="pagos.links" class="px-6 py-4" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
