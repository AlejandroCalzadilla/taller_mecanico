<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

// Importamos los iconos
import {
  WrenchScrewdriverIcon,
  XMarkIcon,
  CheckCircleIcon,
  UserIcon,
  CalendarDaysIcon,
  TruckIcon,
  TagIcon,
  ExclamationTriangleIcon,
  ClipboardDocumentCheckIcon,
  LightBulbIcon,
  PencilSquareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  diagnostico: Object,
  mecanicos: Array,
  estados: Object
})

// Inicializamos el formulario con los datos existentes
const form = useForm({
  mecanico_id: props.diagnostico.mecanico_id,
  // Aseguramos formato YYYY-MM-DD para el input date
  fecha_diagnostico: props.diagnostico.fecha_diagnostico.split('T')[0],
  estado: props.diagnostico.estado,
  descripcion_problema: props.diagnostico.descripcion_problema,
  diagnostico: props.diagnostico.diagnostico,
  recomendaciones: props.diagnostico.recomendaciones
})

const submit = () => {
  form.put(route('admin.diagnosticos.update', props.diagnostico.id))
}
</script>

<template>
  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">

      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight flex items-center gap-3">
            <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm">
              <PencilSquareIcon class="h-6 w-6 text-taller-blue-dark" />
            </div>
            Editar Diagnóstico: <span class="text-gray-500 font-mono ml-2">{{ diagnostico.codigo }}</span>
          </h2>
          <p class="mt-2 text-sm text-gray-500 ml-12">
            Modifique los detalles técnicos y el estado del diagnóstico.
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <Link
            :href="route('admin.diagnosticos.show', diagnostico.id)"
            class="group inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
          >
            <XMarkIcon class="h-4 w-4 text-gray-500" />
            Cancelar
          </Link>
        </div>
      </div>

      <div class="max-w-5xl mx-auto space-y-6">

        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 sm:p-6 flex flex-col sm:flex-row gap-6 items-start sm:items-center justify-between">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-white rounded-full shadow-sm">
                    <TruckIcon class="h-6 w-6 text-taller-blue-dark" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-blue-900">Vehículo en Revisión</h3>
                    <p class="text-lg font-bold text-gray-900">
                        {{ diagnostico.cita.vehiculo.marca }} {{ diagnostico.cita.vehiculo.modelo }}
                    </p>
                    <p class="text-sm text-gray-600 font-mono">{{ diagnostico.cita.vehiculo.placa }}</p>
                </div>
            </div>

            <div class="w-full sm:w-auto border-t sm:border-t-0 sm:border-l border-blue-200 pt-4 sm:pt-0 sm:pl-6">
                 <div class="flex items-center gap-2 text-sm text-gray-700 mb-1">
                    <UserIcon class="h-4 w-4 text-gray-400" />
                    <span class="font-medium">Cliente:</span> {{ diagnostico.cita.cliente.nombre }}
                 </div>
                 <div class="flex items-center gap-2 text-sm text-gray-700">
                    <CalendarDaysIcon class="h-4 w-4 text-gray-400" />
                    <span class="font-medium">Cita Original:</span> {{ diagnostico.cita.fecha }}
                 </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-2">
                    <WrenchScrewdriverIcon class="h-5 w-5 text-gray-500" />
                    Actualización Técnica
                </h3>

                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                    <div class="sm:col-span-2">
                        <label for="mecanico_id" class="block text-sm font-medium leading-6 text-gray-900">Técnico Responsable</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <UserIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                id="mecanico_id"
                                v-model="form.mecanico_id"
                                class="block w-full rounded-md border-0 py-2.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                            >
                                <option v-for="mecanico in mecanicos" :key="mecanico.id" :value="mecanico.id">
                                    {{ mecanico.nombre }}
                                </option>
                            </select>
                        </div>
                        <p v-if="form.errors.mecanico_id" class="mt-2 text-sm text-red-600">{{ form.errors.mecanico_id }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="fecha_diagnostico" class="block text-sm font-medium leading-6 text-gray-900">Fecha Actualización</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <CalendarDaysIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="date"
                                id="fecha_diagnostico"
                                v-model="form.fecha_diagnostico"
                                class="block w-full rounded-md border-0 py-2.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                            />
                        </div>
                        <p v-if="form.errors.fecha_diagnostico" class="mt-2 text-sm text-red-600">{{ form.errors.fecha_diagnostico }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="estado" class="block text-sm font-medium leading-6 text-gray-900">Estado Actual</label>
                        <div class="relative mt-2">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <TagIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <select
                                id="estado"
                                v-model="form.estado"
                                class="block w-full rounded-md border-0 py-2.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                            >
                                <option v-for="(label, value) in estados" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <p v-if="form.errors.estado" class="mt-2 text-sm text-red-600">{{ form.errors.estado }}</p>
                    </div>

                    <div class="col-span-full">
                        <label for="descripcion_problema" class="block text-sm font-medium leading-6 text-gray-900 flex items-center gap-2">
                            <ExclamationTriangleIcon class="h-4 w-4 text-orange-500" />
                            Problema Reportado / Síntomas
                        </label>
                        <div class="mt-2">
                            <textarea
                                id="descripcion_problema"
                                v-model="form.descripcion_problema"
                                rows="3"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                            />
                        </div>
                        <p v-if="form.errors.descripcion_problema" class="mt-2 text-sm text-red-600">{{ form.errors.descripcion_problema }}</p>
                    </div>

                    <div class="col-span-full">
                        <label for="diagnostico" class="block text-sm font-medium leading-6 text-gray-900 flex items-center gap-2">
                            <ClipboardDocumentCheckIcon class="h-4 w-4 text-taller-blue-dark" />
                            Diagnóstico Técnico Detallado
                        </label>
                        <div class="mt-2">
                            <textarea
                                id="diagnostico"
                                v-model="form.diagnostico"
                                rows="5"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                            />
                        </div>
                        <p v-if="form.errors.diagnostico" class="mt-2 text-sm text-red-600">{{ form.errors.diagnostico }}</p>
                    </div>

                    <div class="col-span-full">
                        <label for="recomendaciones" class="block text-sm font-medium leading-6 text-gray-900 flex items-center gap-2">
                            <LightBulbIcon class="h-4 w-4 text-green-600" />
                            Recomendaciones y Solución Propuesta
                        </label>
                        <div class="mt-2">
                            <textarea
                                id="recomendaciones"
                                v-model="form.recomendaciones"
                                rows="3"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6 bg-green-50/20"
                            />
                        </div>
                        <p v-if="form.errors.recomendaciones" class="mt-2 text-sm text-red-600">{{ form.errors.recomendaciones }}</p>
                    </div>

                </div>
            </div>

            <div class="bg-gray-50 px-4 py-4 sm:px-6 flex items-center justify-end gap-x-4 border-t border-gray-100">
                <Link
                    :href="route('admin.diagnosticos.show', diagnostico.id)"
                    class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700"
                >
                    Cancelar
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex justify-center rounded-md bg-taller-blue-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-taller-blue-dark disabled:opacity-50 transition-all duration-200"
                >
                    <div v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Guardando...
                    </div>
                    <div v-else class="flex items-center gap-2">
                        <CheckCircleIcon class="h-5 w-5" />
                        Actualizar Cambios
                    </div>
                </button>
            </div>
        </form>

      </div>
    </div>
  </AdminLayout>
</template>
