<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { debounce } from 'lodash'
import Pagination from '@/Components/Pagination.vue'

import {
  PlusIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  UserIcon,
  WrenchScrewdriverIcon,
  CalendarDaysIcon,
  TagIcon,
  TruckIcon,
  EyeIcon,
  PencilSquareIcon,
  XMarkIcon,
  ClipboardDocumentListIcon,
  CurrencyDollarIcon,
  ClockIcon,
  BriefcaseIcon,
  CheckCircleIcon,
  InboxArrowDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  ordenes: Object,
  mecanicos: Array,
  estados: Object,
  filters: Object
})

const filters = useForm({
  search: props.filters.search || '',
  estado: props.filters.estado || '',
  mecanico_id: props.filters.mecanico_id || '',
  fecha_inicio: props.filters.fecha_inicio || '',
  fecha_fin: props.filters.fecha_fin || ''
})

const estadisticas = computed(() => ({
  total: props.ordenes.data.length,
  presupuestadas: props.ordenes.data.filter(o => o.estado === 'presupuestada').length,
  aprobadas: props.ordenes.data.filter(o => o.estado === 'aprobada').length,
  en_proceso: props.ordenes.data.filter(o => o.estado === 'en_proceso').length,
  completadas: props.ordenes.data.filter(o => o.estado === 'completada').length,
  entregadas: props.ordenes.data.filter(o => o.estado === 'entregada').length,
}))

const handleFilter = debounce(() => {
  filters.get(route('admin.ordenes.index'), { preserveState: true, replace: true })
}, 300)

const clearFilters = () => {
  filters.reset();
  handleFilter();
}

const formatDate = (dateString) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(amount)
}

const getEstadoBadgeClass = (estado) => {
  const classes = {
    'presupuestada': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    'aprobada': 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'en_proceso': 'bg-purple-50 text-purple-700 ring-purple-600/20',
    'completada': 'bg-teal-50 text-teal-700 ring-teal-600/20',
    'entregada': 'bg-gray-50 text-gray-700 ring-gray-600/20',
    'cancelada': 'bg-red-50 text-red-700 ring-red-600/20'
  }
  return `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ${classes[estado] || 'bg-gray-50 text-gray-600'}`
}
</script>

<template>
  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 bg-gray-50/50 min-h-screen">

      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 class="text-2xl font-bold leading-7 text-gray-900 flex items-center gap-3">
            <div class="p-2 bg-white rounded-lg shadow-sm border border-gray-200">
                <ClipboardDocumentListIcon class="h-6 w-6 text-taller-blue-dark" />
            </div>
            Órdenes de Trabajo
          </h1>
          <p class="mt-1 text-sm text-gray-500 ml-12">
            Control de flujo de reparaciones y facturación.
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <Link
            :href="route('admin.ordenes.create')"
            class="inline-flex items-center justify-center rounded-lg bg-taller-blue-dark px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-taller-blue-dark transition-all"
          >
            <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
            Nueva Orden
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
          <div v-for="(count, key) in estadisticas" :key="key"
               class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center hover:border-gray-300 transition-colors group">
              <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                  {{ key.replace('_', ' ') }}
              </span>
              <div class="flex items-center gap-2">
                  <span class="text-2xl font-bold text-gray-900">{{ count }}</span>
                  <InboxArrowDownIcon v-if="key === 'total'" class="h-4 w-4 text-gray-400" />
                  <ClockIcon v-if="key === 'presupuestadas'" class="h-4 w-4 text-yellow-500" />
                  <CheckCircleIcon v-if="key === 'aprobadas'" class="h-4 w-4 text-blue-500" />
                  <WrenchScrewdriverIcon v-if="key === 'en_proceso'" class="h-4 w-4 text-purple-500" />
                  <CurrencyDollarIcon v-if="key === 'completada' || key === 'entregada'" class="h-4 w-4 text-green-500" />
              </div>
          </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="p-5 border-b border-gray-100 bg-white flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center">

            <div class="relative w-full lg:max-w-md group">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400 group-focus-within:text-taller-blue-dark transition-colors" />
                </div>
                <input
                    type="text"
                    v-model="filters.search"
                    @input="handleFilter"
                    placeholder="Buscar por código, cliente, placa..."
                    class="block w-full rounded-lg border-0 py-2.5 pl-10 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6 bg-gray-50 focus:bg-white transition-colors"
                />
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <FunnelIcon class="h-4 w-4 text-gray-400 hidden sm:block" />

                    <select v-model="filters.estado" @change="handleFilter" class="w-full sm:w-40 rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm">
                        <option value="">Estado: Todos</option>
                        <option v-for="(label, value) in estados" :key="value" :value="value">{{ label }}</option>
                    </select>

                    <select v-model="filters.mecanico_id" @change="handleFilter" class="w-full sm:w-40 rounded-lg border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm">
                        <option value="">Técnico: Todos</option>
                        <option v-for="mecanico in mecanicos" :key="mecanico.id" :value="mecanico.id">{{ mecanico.nombre }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto bg-gray-50 rounded-lg p-1 ring-1 ring-inset ring-gray-300">
                    <input type="date" v-model="filters.fecha_inicio" @change="handleFilter" class="border-none bg-transparent py-1 text-xs text-gray-600 focus:ring-0 p-0" />
                    <span class="text-gray-400 text-xs">a</span>
                    <input type="date" v-model="filters.fecha_fin" @change="handleFilter" class="border-none bg-transparent py-1 text-xs text-gray-600 focus:ring-0 p-0" />
                </div>

                <button v-if="filters.search || filters.estado || filters.mecanico_id || filters.fecha_inicio"
                        @click="clearFilters"
                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Limpiar Filtros">
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Orden #</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cliente / Vehículo</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Responsable</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cronograma</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Importe</th>
                        <th scope="col" class="px-3 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th scope="col" class="relative py-4 pl-3 pr-6"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <tr v-for="orden in ordenes.data" :key="orden.id" class="hover:bg-gray-50/80 transition-colors group">

                        <td class="whitespace-nowrap py-4 pl-6 pr-3">
                            <div class="flex items-center">
                                <span class="font-mono text-sm font-semibold text-gray-900 bg-gray-100 px-2 py-1 rounded border border-gray-200">
                                    {{ orden.codigo }}
                                </span>
                            </div>
                        </td>

                        <td class="px-3 py-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-1 h-8 w-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs border border-blue-100">
                                    {{ orden.diagnostico.cita.cliente.nombre.charAt(0) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ orden.diagnostico.cita.cliente.nombre }}</div>
                                    <div class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                        <TruckIcon class="h-3 w-3" />
                                        {{ orden.diagnostico.cita.vehiculo.marca }} {{ orden.diagnostico.cita.vehiculo.modelo }}
                                        <span class="text-gray-300">|</span>
                                        {{ orden.diagnostico.cita.vehiculo.placa }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-3 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs">
                                    <UserIcon class="h-3 w-3" />
                                </div>
                                <span class="text-sm text-gray-700">{{ orden.mecanico.nombre }}</span>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-3 py-4">
                            <div class="flex flex-col gap-1 text-xs">
                                <div class="flex items-center gap-1.5 text-gray-600">
                                    <CalendarDaysIcon class="h-3.5 w-3.5 text-gray-400" />
                                    <span>In: {{ formatDate(orden.fecha_creacion) }}</span>
                                </div>
                                <div v-if="orden.fecha_fin_estimada" class="flex items-center gap-1.5 text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded w-fit">
                                    <ClockIcon class="h-3.5 w-3.5" />
                                    <span>Est: {{ formatDate(orden.fecha_fin_estimada) }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-3 py-4">
                             <div class="flex items-center gap-1">
                                <span class="text-sm font-bold text-gray-900">{{ formatCurrency(orden.subtotal) }}</span>
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-3 py-4">
                            <span :class="getEstadoBadgeClass(orden.estado)">
                                {{ estados[orden.estado] }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <Link
                                    :href="route('admin.ordenes.show', orden.id)"
                                    class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-taller-blue-dark hover:border-taller-blue-dark hover:shadow-sm transition-all"
                                    title="Ver Detalles"
                                >
                                    <EyeIcon class="h-4 w-4" />
                                </Link>
                                <Link
                                    :href="route('admin.ordenes.edit', orden.id)"
                                    class="p-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-green-600 hover:border-green-600 hover:shadow-sm transition-all"
                                    title="Editar"
                                >
                                    <PencilSquareIcon class="h-4 w-4" />
                                </Link>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="ordenes.data.length === 0">
                        <td colspan="7" class="px-6 py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <BriefcaseIcon class="h-8 w-8 text-gray-300" />
                                </div>
                                <h3 class="text-sm font-semibold text-gray-900">No se encontraron órdenes</h3>
                                <p class="mt-1 text-sm text-gray-500 max-w-sm">
                                    No hay registros que coincidan con tu búsqueda. Intenta ajustar los filtros.
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 bg-gray-50/50">
            <Pagination :links="ordenes.links" class="py-4 px-6" />
        </div>

      </div>
    </div>
  </AdminLayout>
</template>
