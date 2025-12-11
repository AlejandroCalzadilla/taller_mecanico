<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import Pagination from '@/Components/Pagination.vue'

// Importación masiva de iconos para mejorar la UI
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
  DocumentMagnifyingGlassIcon,
  ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  diagnosticos: Object,
  mecanicos: Array,
  estados: Object,
  filters: Object
})

const filters = useForm({
  search: props.filters.search || '',
  estado: props.filters.estado || '',
  mecanico_id: props.filters.mecanico_id || '',
  fecha: props.filters.fecha || ''
})

// Debounced filter handler
const handleFilter = debounce(() => {
  filters.get(route('admin.diagnosticos.index'), {
    preserveState: true,
    replace: true
  })
}, 300)

const clearFilters = () => {
  filters.search = ''
  filters.estado = ''
  filters.mecanico_id = ''
  filters.fecha = ''
  handleFilter()
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

// Badges con anillos (rings) para un look más moderno
const getEstadoBadgeClass = (estado) => {
  const classes = {
    'en_revision': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    'completado': 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'aprobado_cliente': 'bg-green-50 text-green-700 ring-green-600/20',
    'rechazado_cliente': 'bg-red-50 text-red-700 ring-red-600/20'
  }
  return `inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${classes[estado] || 'bg-gray-50 text-gray-600 ring-gray-500/10'}`
}
</script>

<template>
  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">

      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div class="sm:flex-auto">
          <h1 class="text-2xl font-bold leading-7 text-gray-900 flex items-center gap-2">
            <ClipboardDocumentCheckIcon class="h-8 w-8 text-taller-blue-dark" />
            Diagnósticos
          </h1>
          <p class="mt-2 text-sm text-gray-500">
            Gestión y seguimiento de evaluaciones técnicas del taller.
          </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
          <Link
            :href="route('admin.diagnosticos.create')"
            class="inline-flex items-center justify-center rounded-lg bg-taller-blue-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-taller-blue-dark transition-all duration-200 gap-2"
          >
            <PlusIcon class="h-5 w-5" />
            Nuevo Diagnóstico
          </Link>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-8">
        <div class="flex items-center gap-2 mb-4 text-gray-700 font-medium border-b border-gray-100 pb-2">
            <FunnelIcon class="h-4 w-4 text-gray-500" />
            <span>Filtros de Búsqueda</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="relative">
             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
             </div>
             <input
              type="text"
              v-model="filters.search"
              @input="handleFilter"
              placeholder="Código, cliente, placa..."
              class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
            />
          </div>

          <div class="relative">
             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <TagIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
             </div>
            <select
              v-model="filters.estado"
              @change="handleFilter"
              class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
            >
              <option value="">Estado: Todos</option>
              <option v-for="(label, value) in estados" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div class="relative">
             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <WrenchScrewdriverIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
             </div>
            <select
              v-model="filters.mecanico_id"
              @change="handleFilter"
              class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
            >
              <option value="">Mecánico: Todos</option>
              <option v-for="mecanico in mecanicos" :key="mecanico.id" :value="mecanico.id">
                {{ mecanico.nombre }}
              </option>
            </select>
          </div>

          <div class="relative">
             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <CalendarDaysIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
             </div>
            <input
              type="date"
              v-model="filters.fecha"
              @change="handleFilter"
              class="block w-full rounded-md border-0 py-2 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
            />
          </div>
        </div>

        <div class="mt-4 flex justify-end" v-if="filters.search || filters.estado || filters.mecanico_id || filters.fecha">
          <button
            @click="clearFilters"
            class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-red-600 font-medium transition-colors"
          >
            <XMarkIcon class="h-4 w-4" />
            Limpiar filtros
          </button>
        </div>
      </div>

      <div class="flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 sm:rounded-xl bg-white">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:pl-6">
                      Código
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                      Cliente / Vehículo
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                      Mecánico
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                      Fecha
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                      Estado
                    </th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                      <span class="sr-only">Acciones</span>
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="diagnostico in diagnosticos.data" :key="diagnostico.id" class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                      <span class="font-mono bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                          {{ diagnostico.codigo }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                      <div class="flex flex-col gap-1">
                          <div class="flex items-center gap-1.5 text-gray-900 font-medium">
                              <UserIcon class="h-4 w-4 text-gray-400" />
                              {{ diagnostico.cita.cliente.nombre }}
                          </div>
                          <div class="flex items-center gap-1.5 text-xs">
                              <TruckIcon class="h-3.5 w-3.5 text-gray-400" />
                              {{ diagnostico.cita.vehiculo.marca }} {{ diagnostico.cita.vehiculo.modelo }}
                              <span class="text-gray-400">•</span> {{ diagnostico.cita.vehiculo.placa }}
                          </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <WrenchScrewdriverIcon class="h-4 w-4 text-gray-400" />
                            {{ diagnostico.mecanico.nombre }}
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                         <div class="flex items-center gap-1.5">
                            <CalendarDaysIcon class="h-4 w-4 text-gray-400" />
                            {{ formatDate(diagnostico.fecha_diagnostico) }}
                         </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                      <span :class="getEstadoBadgeClass(diagnostico.estado)">
                        {{ estados[diagnostico.estado] }}
                      </span>
                    </td>
                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                      <div class="flex justify-end gap-2">
                        <Link
                          :href="route('admin.diagnosticos.show', diagnostico.id)"
                          class="p-1.5 text-gray-400 hover:text-taller-blue-dark hover:bg-blue-50 rounded-md transition-colors"
                          title="Ver Detalle"
                        >
                          <EyeIcon class="h-5 w-5" />
                        </Link>
                        <Link
                          :href="route('admin.diagnosticos.edit', diagnostico.id)"
                          class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-md transition-colors"
                          title="Editar"
                        >
                          <PencilSquareIcon class="h-5 w-5" />
                        </Link>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="diagnosticos.data.length === 0">
                    <td colspan="6" class="px-6 py-16 text-center">
                      <div class="flex flex-col items-center">
                        <div class="p-3 rounded-full bg-gray-100 mb-4">
                            <DocumentMagnifyingGlassIcon class="h-10 w-10 text-gray-400" />
                        </div>
                        <h3 class="text-base font-semibold text-gray-900">No se encontraron diagnósticos</h3>
                        <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">
                          No hay registros que coincidan con tus filtros actuales o aún no se han creado diagnósticos.
                        </p>
                        <div class="mt-6">
                            <button @click="clearFilters" v-if="filters.search" class="text-sm font-medium text-taller-blue-dark hover:underline">
                                Limpiar búsqueda
                            </button>
                            <Link v-else :href="route('admin.diagnosticos.create')" class="text-sm font-medium text-taller-blue-dark hover:underline">
                                Crear el primer diagnóstico
                            </Link>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <Pagination :links="diagnosticos.links" class="mt-6" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
