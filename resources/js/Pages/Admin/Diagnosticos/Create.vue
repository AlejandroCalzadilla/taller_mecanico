<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

// Importamos los iconos necesarios
import {
  WrenchScrewdriverIcon,
  XMarkIcon,
  CheckIcon,
  UserIcon,
  TruckIcon,
  CalendarDaysIcon,
  ClockIcon,
  ChatBubbleBottomCenterTextIcon,
  ClipboardDocumentCheckIcon,
  DocumentTextIcon,
  ExclamationCircleIcon,
  FingerPrintIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  citas: Array,
  mecanicos: Array,
  estados: Object,
  cita_preseleccionada: Object
})

const form = useForm({
  cita_id: props.cita_preseleccionada?.id || '',
  mecanico_id: '',
  fecha_diagnostico: new Date().toISOString().split('T')[0],
  descripcion_problema: '',
  diagnostico: '',
  recomendaciones: '',
  estado: 'en_revision'
})

const citaSeleccionada = ref(props.cita_preseleccionada || null)

const onCitaChange = () => {
  const cita = props.citas.find(c => c.id == form.cita_id)
  citaSeleccionada.value = cita || null

  // Si hay una cita preseleccionada, llenar automáticamente algunos campos
  if (cita && cita.motivo) {
    form.descripcion_problema = cita.motivo
  }
}

const submit = () => {
  form.post(route('admin.diagnosticos.store'))
}
</script>

<template>
  <AdminLayout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 bg-gray-50 min-h-screen">

      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight flex items-center gap-3">
            <div class="p-2 bg-taller-blue-dark rounded-lg shadow-sm">
              <WrenchScrewdriverIcon class="h-6 w-6 text-white" />
            </div>
            Crear Diagnóstico
          </h2>
          <p class="mt-2 text-sm text-gray-500 ml-12">
            Complete el formulario técnico para registrar un nuevo diagnóstico vehicular.
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <Link
            :href="route('admin.diagnosticos.index')"
            class="group inline-flex items-center gap-2 rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 hover:text-red-600 transition-colors"
          >
            <XMarkIcon class="h-4 w-4 text-gray-500 group-hover:text-red-600 transition-colors" />
            Cancelar
          </Link>
        </div>
      </div>

      <div class="max-w-4xl mx-auto">
        <form @submit.prevent="submit" class="space-y-6">

          <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center gap-2 mb-4 border-b border-gray-100 pb-2">
                <CalendarDaysIcon class="h-5 w-5 text-taller-blue-dark" />
                <h3 class="text-base font-semibold leading-6 text-gray-900">Origen del Servicio</h3>
              </div>

              <div class="grid grid-cols-1 gap-6">
                <div>
                  <label for="cita_id" class="block text-sm font-medium leading-6 text-gray-900">Seleccionar Cita Pendiente</label>
                  <div class="relative mt-2">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <FingerPrintIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </div>
                    <select
                      id="cita_id"
                      v-model="form.cita_id"
                      @change="onCitaChange"
                      :class="{'ring-red-300 focus:ring-red-500': form.errors.cita_id}"
                      class="block w-full rounded-md border-0 py-2.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                    >
                      <option value="">-- Buscar cita por código o cliente --</option>
                      <option
                        v-for="cita in citas"
                        :key="cita.id"
                        :value="cita.id"
                      >
                        {{ cita.codigo }} | {{ cita.vehiculo.marca }} {{ cita.vehiculo.modelo }} | {{ cita.cliente.nombre }}
                      </option>
                    </select>
                  </div>
                  <p v-if="form.errors.cita_id" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                    <ExclamationCircleIcon class="h-4 w-4" /> {{ form.errors.cita_id }}
                  </p>
                </div>

                <transition
                  enter-active-class="transition ease-out duration-200"
                  enter-from-class="opacity-0 translate-y-1"
                  enter-to-class="opacity-100 translate-y-0"
                >
                  <div v-if="citaSeleccionada" class="rounded-lg bg-blue-50/50 border border-blue-100 p-4">
                    <div class="flex items-center gap-2 mb-3">
                      <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                      <h4 class="text-sm font-semibold text-blue-900">Resumen de la Cita Seleccionada</h4>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
                      <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-gray-500 flex items-center gap-1">
                          <UserIcon class="h-3 w-3" /> Cliente
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ citaSeleccionada.cliente.nombre }}</dd>
                      </div>
                      <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-gray-500 flex items-center gap-1">
                          <TruckIcon class="h-3 w-3" /> Vehículo
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">
                          {{ citaSeleccionada.vehiculo.marca }} {{ citaSeleccionada.vehiculo.modelo }}
                          <span class="block text-xs text-gray-500 font-normal">{{ citaSeleccionada.vehiculo.placa }}</span>
                        </dd>
                      </div>
                      <div class="sm:col-span-1">
                        <dt class="text-xs font-medium text-gray-500 flex items-center gap-1">
                          <CalendarDaysIcon class="h-3 w-3" /> Fecha Programada
                        </dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900">
                          {{ citaSeleccionada.fecha }}
                          <span class="text-gray-500 font-normal text-xs flex items-center mt-0.5">
                            <ClockIcon class="h-3 w-3 mr-1" /> {{ citaSeleccionada.hora }}
                          </span>
                        </dd>
                      </div>
                      <div class="sm:col-span-2 lg:col-span-1 lg:col-start-4 lg:row-start-1 bg-white p-2 rounded border border-blue-100">
                        <dt class="text-xs font-medium text-gray-500">Motivo Inicial</dt>
                        <dd class="mt-1 text-xs text-gray-700 italic">"{{ citaSeleccionada.motivo }}"</dd>
                      </div>
                    </dl>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center gap-2 mb-6 border-b border-gray-100 pb-2">
                <ClipboardDocumentCheckIcon class="h-5 w-5 text-taller-blue-dark" />
                <h3 class="text-base font-semibold leading-6 text-gray-900">Evaluación Técnica</h3>
              </div>

              <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                <div class="sm:col-span-3">
                  <label for="mecanico_id" class="block text-sm font-medium leading-6 text-gray-900">Mecánico Responsable *</label>
                  <div class="relative mt-2">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <UserIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <select
                      id="mecanico_id"
                      v-model="form.mecanico_id"
                      class="block w-full rounded-md border-0 py-2.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                    >
                      <option value="">Asignar técnico...</option>
                      <option v-for="mecanico in mecanicos" :key="mecanico.id" :value="mecanico.id">
                        {{ mecanico.nombre }}
                      </option>
                    </select>
                  </div>
                  <p v-if="form.errors.mecanico_id" class="mt-2 text-sm text-red-600">{{ form.errors.mecanico_id }}</p>
                </div>

                <div class="sm:col-span-3">
                  <label for="fecha_diagnostico" class="block text-sm font-medium leading-6 text-gray-900">Fecha de Realización *</label>
                  <div class="relative mt-2">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <CalendarDaysIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                      type="date"
                      id="fecha_diagnostico"
                      v-model="form.fecha_diagnostico"
                      class="block w-full rounded-md border-0 py-2.5 pl-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                    />
                  </div>
                  <p v-if="form.errors.fecha_diagnostico" class="mt-2 text-sm text-red-600">{{ form.errors.fecha_diagnostico }}</p>
                </div>

                <div class="sm:col-span-3">
                  <label for="estado" class="block text-sm font-medium leading-6 text-gray-900">Estado Inicial *</label>
                  <div class="mt-2">
                    <select
                      id="estado"
                      v-model="form.estado"
                      class="block w-full rounded-md border-0 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                    >
                      <option v-for="(label, value) in estados" :key="value" :value="value">{{ label }}</option>
                    </select>
                  </div>
                  <p v-if="form.errors.estado" class="mt-2 text-sm text-red-600">{{ form.errors.estado }}</p>
                </div>

                <div class="col-span-full">
                  <label for="descripcion_problema" class="block text-sm font-medium leading-6 text-gray-900 flex items-center gap-2">
                    <ExclamationCircleIcon class="h-4 w-4 text-orange-500" />
                    Descripción del Problema (Reporte Cliente) *
                  </label>
                  <div class="mt-2">
                    <textarea
                      id="descripcion_problema"
                      v-model="form.descripcion_problema"
                      rows="3"
                      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6 bg-orange-50/30"
                      placeholder="Detalles sobre ruidos, fallas o síntomas reportados..."
                    />
                  </div>
                  <p v-if="form.errors.descripcion_problema" class="mt-2 text-sm text-red-600">{{ form.errors.descripcion_problema }}</p>
                </div>

                <div class="col-span-full">
                  <label for="diagnostico" class="block text-sm font-medium leading-6 text-gray-900 flex items-center gap-2">
                    <WrenchScrewdriverIcon class="h-4 w-4 text-taller-blue-dark" />
                    Hallazgos Técnicos (Diagnóstico) *
                  </label>
                  <div class="mt-2">
                    <textarea
                      id="diagnostico"
                      v-model="form.diagnostico"
                      rows="4"
                      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                      placeholder="Describa el origen de la falla y los sistemas afectados..."
                    />
                  </div>
                  <p v-if="form.errors.diagnostico" class="mt-2 text-sm text-red-600">{{ form.errors.diagnostico }}</p>
                </div>

                <div class="col-span-full">
                  <label for="recomendaciones" class="block text-sm font-medium leading-6 text-gray-900 flex items-center gap-2">
                    <ChatBubbleBottomCenterTextIcon class="h-4 w-4 text-green-600" />
                    Recomendaciones / Solución Propuesta
                  </label>
                  <div class="mt-2">
                    <textarea
                      id="recomendaciones"
                      v-model="form.recomendaciones"
                      rows="3"
                      class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-taller-blue-dark sm:text-sm sm:leading-6"
                      placeholder="Lista de repuestos sugeridos o pasos a seguir..."
                    />
                  </div>
                  <p v-if="form.errors.recomendaciones" class="mt-2 text-sm text-red-600">{{ form.errors.recomendaciones }}</p>
                </div>
              </div>
            </div>

            <div class="bg-gray-50 px-4 py-4 sm:px-6 flex items-center justify-end gap-x-4 border-t border-gray-100">
              <Link
                :href="route('admin.diagnosticos.index')"
                class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center rounded-md bg-taller-blue-dark px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-taller-blue-dark disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
              >
                <div v-if="form.processing" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Guardando...
                </div>
                <div v-else class="flex items-center gap-2">
                    <CheckIcon class="h-4 w-4" />
                    Guardar Diagnóstico
                </div>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>
