<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

// Importamos los Heroicons
import {
  WrenchScrewdriverIcon,
  UserIcon,
  TruckIcon,
  CalendarDaysIcon,
  ClockIcon,
  MapPinIcon,
  PhoneIcon,
  EnvelopeIcon,
  PencilSquareIcon,
  TrashIcon,
  ClipboardDocumentCheckIcon,
  ExclamationTriangleIcon,
  LightBulbIcon,
  ArrowLeftIcon,
  DocumentPlusIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  diagnostico: Object,
  estados: Object
})

const form = useForm({
  estado: props.diagnostico.estado
})

// Función para actualizar estado usando un SELECT nativo (más simple que Headless UI)
const updateEstado = () => {
  form.patch(route('admin.diagnosticos.update-status', props.diagnostico.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Opcional: Notificación
    }
  })
}

const deleteForm = useForm({});

const confirmDelete = () => {
  if (confirm('¿Está seguro de que desea eliminar este diagnóstico? Esta acción no se puede deshacer.')) {
    deleteForm.delete(route('admin.diagnosticos.destroy', props.diagnostico.id));
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  })
}

// Clases para badges de estado del Diagnóstico
const getEstadoBadgeClass = (estado) => {
  const classes = {
    'en_revision': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    'completado': 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'aprobado_cliente': 'bg-green-50 text-green-700 ring-green-600/20',
    'rechazado_cliente': 'bg-red-50 text-red-700 ring-red-600/20'
  }
  return `inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${classes[estado] || 'bg-gray-50 text-gray-600 ring-gray-500/10'}`
}

// Clases para badges de estado de la Cita
const getCitaEstadoBadgeClass = (estado) => {
  const classes = {
    'pendiente': 'text-yellow-600 bg-yellow-50 ring-yellow-500/10',
    'confirmada': 'text-blue-600 bg-blue-50 ring-blue-500/10',
    'en_proceso': 'text-purple-600 bg-purple-50 ring-purple-500/10',
    'completada': 'text-green-600 bg-green-50 ring-green-500/10',
    'cancelada': 'text-red-600 bg-red-50 ring-red-500/10'
  }
  return `inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset ${classes[estado] || 'text-gray-600 bg-gray-50'}`
}
</script>

<template>
  <AdminLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Navegación Superior -->
        <div class="mb-6 flex items-center justify-between">
            <Link :href="route('admin.diagnosticos.index')" class="flex items-center text-sm text-gray-500 hover:text-taller-blue-dark transition-colors">
                <ArrowLeftIcon class="h-4 w-4 mr-1" />
                Volver a la lista
            </Link>
            <div class="text-sm text-gray-400">
                Creado el {{ formatDate(diagnostico.created_at) }}
            </div>
        </div>

        <!-- Encabezado Principal -->
        <div class="md:flex md:items-center md:justify-between mb-8">
          <div class="min-w-0 flex-1">
            <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight flex items-center gap-3">
               <div class="p-2 bg-white rounded-lg border border-gray-200 shadow-sm">
                   <ClipboardDocumentCheckIcon class="h-8 w-8 text-taller-blue-dark" />
               </div>
               {{ diagnostico.codigo }}
            </h2>
            <div class="mt-2 flex items-center gap-4 text-sm text-gray-500 ml-14">
                <span class="flex items-center">
                    <UserIcon class="mr-1.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                    Técnico: {{ diagnostico.mecanico.nombre }}
                </span>
                <span :class="getEstadoBadgeClass(diagnostico.estado)">
                    {{ estados[diagnostico.estado] }}
                </span>
            </div>
          </div>

          <!-- Botones de Acción Primaria -->
          <div class="mt-4 flex flex-shrink-0 md:ml-4 md:mt-0 gap-3">
             <Link
              v-if="diagnostico.estado === 'aprobado_cliente' && !diagnostico.orden_trabajo"
              :href="route('admin.ordenes.create', { diagnostico_id: diagnostico.id })"
              class="inline-flex items-center rounded-md bg-taller-blue-dark px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-taller-blue-dark"
            >
              <DocumentPlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
              Crear Orden de Trabajo
            </Link>

            <Link
              :href="route('admin.diagnosticos.edit', diagnostico.id)"
              class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
              <PencilSquareIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
              Editar
            </Link>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

          <!-- COLUMNA IZQUIERDA (2/3): Detalles del Diagnóstico -->
          <div class="space-y-6 lg:col-span-2">

            <!-- Tarjeta 1: Cliente y Vehículo -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 flex items-center gap-2">
                        <TruckIcon class="h-5 w-5 text-gray-500" />
                        Información del Vehículo y Cliente
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Vehículo -->
                        <div class="relative pl-4 border-l-2 border-taller-blue-dark">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Vehículo</h4>
                            <p class="text-lg font-bold text-gray-900">{{ diagnostico.cita.vehiculo.marca }} {{ diagnostico.cita.vehiculo.modelo }}</p>
                            <p class="text-sm text-gray-600 bg-gray-100 inline-block px-2 py-0.5 rounded mt-1 font-mono">
                                {{ diagnostico.cita.vehiculo.placa }}
                            </p>
                            <div class="mt-2 text-sm text-gray-500 flex flex-col gap-1">
                                <span v-if="diagnostico.cita.vehiculo.color">Color: {{ diagnostico.cita.vehiculo.color }}</span>
                                <span v-if="diagnostico.cita.vehiculo.anio">Año: {{ diagnostico.cita.vehiculo.anio }}</span>
                            </div>
                        </div>

                        <!-- Cliente -->
                        <div class="relative pl-4 border-l-2 border-gray-200">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Cliente</h4>
                            <div class="flex items-center gap-2">
                                <UserIcon class="h-4 w-4 text-gray-400" />
                                <span class="font-semibold text-gray-900">{{ diagnostico.cita.cliente.nombre }}</span>
                            </div>
                            <div class="mt-2 space-y-1">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <EnvelopeIcon class="h-4 w-4 text-gray-400" />
                                    {{ diagnostico.cita.cliente.email }}
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <PhoneIcon class="h-4 w-4 text-gray-400" />
                                    {{ diagnostico.cita.cliente.telefono || 'Sin teléfono' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Detalles Técnicos -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 flex items-center gap-2">
                        <WrenchScrewdriverIcon class="h-5 w-5 text-gray-500" />
                        Informe Técnico
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">

                    <!-- Problema -->
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-sm font-medium text-red-600 flex items-center gap-2 mb-3">
                            <ExclamationTriangleIcon class="h-5 w-5" />
                            Problema Reportado
                        </h4>
                        <div class="rounded-md bg-red-50 p-4 text-sm text-gray-900 whitespace-pre-wrap leading-relaxed border border-red-100">
                            {{ diagnostico.descripcion_problema }}
                        </div>
                    </div>

                    <!-- Diagnóstico -->
                    <div class="px-4 py-5 sm:p-6">
                        <h4 class="text-sm font-medium text-taller-blue-dark flex items-center gap-2 mb-3">
                            <ClipboardDocumentCheckIcon class="h-5 w-5" />
                            Diagnóstico Realizado
                        </h4>
                        <div class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed">
                            {{ diagnostico.diagnostico }}
                        </div>
                    </div>

                    <!-- Recomendaciones -->
                    <div class="px-4 py-5 sm:p-6 bg-green-50/30">
                        <h4 class="text-sm font-medium text-green-700 flex items-center gap-2 mb-3">
                            <LightBulbIcon class="h-5 w-5" />
                            Recomendaciones y Solución
                        </h4>
                        <div class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed">
                            {{ diagnostico.recomendaciones || 'No se registraron recomendaciones adicionales.' }}
                        </div>
                    </div>

                </div>
            </div>

          </div>

          <!-- COLUMNA DERECHA (1/3): Sidebar -->
          <div class="space-y-6">

            <!-- Panel de Estado -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 p-6">
                <h3 class="text-sm font-semibold leading-6 text-gray-900 mb-4">Gestión de Estado</h3>

                <form @submit.prevent="updateEstado">
                    <label for="estado" class="sr-only">Estado</label>
                    <select
                        v-model="form.estado"
                        @change="updateEstado"
                        id="estado"
                        class="block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                    >
                        <option v-for="(label, value) in estados" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        Cambiar el estado actualiza el flujo de trabajo del vehículo.
                    </p>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100">
                     <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Cita Asociada</h4>
                     <div class="flex items-center justify-between">
                         <span class="text-sm font-medium text-gray-900">{{ diagnostico.cita.codigo }}</span>
                         <span :class="getCitaEstadoBadgeClass(diagnostico.cita.estado)">
                             {{ diagnostico.cita.estado }}
                         </span>
                     </div>
                     <div class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                         <CalendarDaysIcon class="h-3 w-3" />
                         {{ formatDate(diagnostico.cita.fecha) }} - {{ diagnostico.cita.hora }}
                     </div>
                </div>
            </div>

            <!-- Panel de Información Meta -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 p-6">
                <h3 class="text-sm font-semibold leading-6 text-gray-900 mb-4">Detalles del Registro</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs text-gray-500">Fecha del Diagnóstico</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1 flex items-center gap-2">
                            <CalendarDaysIcon class="h-4 w-4 text-gray-400" />
                            {{ formatDate(diagnostico.fecha_diagnostico) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Mecánico Responsable</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1 flex items-center gap-2">
                             <UserIcon class="h-4 w-4 text-gray-400" />
                            {{ diagnostico.mecanico.nombre }}
                        </dd>
                    </div>
                     <div>
                        <dt class="text-xs text-gray-500">Ubicación Cliente</dt>
                        <dd class="text-sm font-medium text-gray-900 mt-1 flex items-center gap-2">
                             <MapPinIcon class="h-4 w-4 text-gray-400" />
                            {{ diagnostico.cita.cliente.direccion || 'No registrada' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Zona de Peligro -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-red-100 p-6">
                <h3 class="text-sm font-semibold leading-6 text-red-600 mb-2">Zona de Peligro</h3>
                <p class="text-xs text-gray-500 mb-4">
                    Eliminar este diagnóstico es irreversible.
                </p>
                <button
                    @click="confirmDelete"
                    :disabled="deleteForm.processing"
                    class="w-full flex w-full justify-center items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 disabled:opacity-50"
                >
                    <TrashIcon class="h-4 w-4 mr-2" />
                    Eliminar Diagnóstico
                </button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
