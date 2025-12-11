<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

import {
  ClipboardDocumentCheckIcon,
  UserIcon,
  CalendarDaysIcon,
  TruckIcon,
  WrenchScrewdriverIcon,
  PlusIcon,
  TrashIcon,
  CalculatorIcon,
  CheckCircleIcon,
  XMarkIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  TagIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  diagnosticos: Array,
  mecanicos: Array,
  servicios: Array,
  estados: Object,
  diagnostico_preseleccionado: Object
})

const form = useForm({
  diagnostico_id: props.diagnostico_preseleccionado?.id || '',
  mecanico_id: '',
  fecha_creacion: new Date().toISOString().split('T')[0],
  fecha_fin_estimada: '',
  costo_mano_obra: 0,
  estado: 'presupuestada',
  observaciones: '',
  servicios: [
    { servicio_id: '', cantidad: 1, precio_unitario: 0, descripcion: '' }
  ]
})

const diagnosticoSeleccionado = ref(props.diagnostico_preseleccionado || null)

// Actualizar tarjeta de detalle al cambiar selección
const onDiagnosticoChange = () => {
  diagnosticoSeleccionado.value = props.diagnosticos.find(d => d.id == form.diagnostico_id) || null
}

const onServicioChange = (index) => {
  const servicioId = form.servicios[index].servicio_id
  const servicio = props.servicios.find(s => s.id == servicioId)
  if (servicio) {
    form.servicios[index].precio_unitario = servicio.precio_base
    form.servicios[index].descripcion = servicio.nombre
  }
}

const agregarServicio = () => {
  form.servicios.push({ servicio_id: '', cantidad: 1, precio_unitario: 0, descripcion: '' })
}

const eliminarServicio = (index) => {
  if (form.servicios.length > 1) form.servicios.splice(index, 1)
}

// Cálculos Computados
const subtotalRepuestos = computed(() => {
  return form.servicios.reduce((total, s) => total + (s.cantidad * s.precio_unitario), 0)
})

const totalGeneral = computed(() => {
  return subtotalRepuestos.value + parseFloat(form.costo_mano_obra || 0)
})

const submit = () => {
  console.log('=== DEBUG: Datos del formulario ===')
  console.log('Diagnóstico ID:', form.diagnostico_id)
  console.log('Mecánico ID:', form.mecanico_id)
  console.log('Fecha creación:', form.fecha_creacion)
  console.log('Fecha fin estimada:', form.fecha_fin_estimada)
  console.log('Costo mano de obra:', form.costo_mano_obra)
  console.log('Estado:', form.estado)
  console.log('Observaciones:', form.observaciones)
  console.log('Servicios:', JSON.stringify(form.servicios, null, 2))
  console.log('================================')

  form.post(route('admin.ordenes.store'), {
    onBefore: () => {
      console.log('🚀 Enviando formulario...')
    },
    onSuccess: (response) => {
      console.log('✅ Orden creada exitosamente:', response)
    },
    onError: (errors) => {
      console.log('❌ Errores de validación:', errors)
      console.log('Errores completos:', JSON.stringify(errors, null, 2))
    },
    onFinish: () => {
      console.log('🏁 Petición finalizada')
    }
  })
}
</script>

<template>
  <AdminLayout>
    <div class="min-h-screen bg-gray-50/50 pb-12">

      <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-taller-blue-dark/10 rounded-lg">
                <ClipboardDocumentCheckIcon class="h-6 w-6 text-taller-blue-dark" />
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">Nueva Orden de Trabajo</h1>
                <p class="text-xs text-gray-500">Creación de presupuesto y asignación</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link :href="route('admin.ordenes.index')" class="text-sm font-medium text-gray-500 hover:text-gray-700 px-3 py-2">
                Cancelar
            </Link>
            <button @click="submit" :disabled="form.processing"
                class="inline-flex items-center justify-center rounded-lg bg-taller-blue-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light focus:ring-2 focus:ring-offset-2 focus:ring-taller-blue-dark disabled:opacity-50 transition-all">
                <div v-if="form.processing" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Guardando...
                </div>
                <div v-else class="flex items-center gap-2">
                    <CheckCircleIcon class="h-5 w-5" />
                    Generar Orden
                </div>
            </button>
          </div>
        </div>
      </header>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Mostrar errores generales -->
        <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex items-start">
            <XMarkIcon class="h-5 w-5 text-red-400 mt-0.5 mr-2" />
            <div>
              <h3 class="text-sm font-medium text-red-800">Por favor, corrige los siguientes errores:</h3>
              <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">

                <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex items-center gap-2">
                        <DocumentTextIcon class="h-5 w-5 text-gray-400" />
                        <h2 class="font-semibold text-gray-900">Origen de la Orden</h2>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                              Seleccionar Diagnóstico Aprobado <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.diagnostico_id" @change="onDiagnosticoChange"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm py-2.5"
                                :class="{ 'border-red-500': form.errors.diagnostico_id }">
                                <option value="">-- Buscar por código o cliente --</option>
                                <option v-for="d in diagnosticos" :key="d.id" :value="d.id">
                                    {{ d.codigo }} | {{ d.cita.cliente.nombre }} - {{ d.cita.vehiculo.placa }}
                                </option>
                            </select>
                            <p v-if="form.errors.diagnostico_id" class="mt-1 text-xs text-red-600">{{ form.errors.diagnostico_id }}</p>
                        </div>

                        <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
                            <div v-if="diagnosticoSeleccionado" class="bg-blue-50/50 rounded-lg border border-blue-100 p-4 flex gap-4 items-start">
                                <div class="bg-white p-2 rounded-full shadow-sm shrink-0">
                                    <TruckIcon class="h-6 w-6 text-taller-blue-dark" />
                                </div>
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-blue-600 font-bold uppercase tracking-wider">Vehículo</p>
                                        <p class="font-medium text-gray-900">{{ diagnosticoSeleccionado.cita.vehiculo.marca }} {{ diagnosticoSeleccionado.cita.vehiculo.modelo }}</p>
                                        <p class="text-sm text-gray-500">{{ diagnosticoSeleccionado.cita.vehiculo.placa }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 font-bold uppercase tracking-wider">Cliente</p>
                                        <p class="font-medium text-gray-900">{{ diagnosticoSeleccionado.cita.cliente.nombre }}</p>
                                        <p class="text-xs text-gray-500 mt-1 truncate" title="Problema reportado">
                                            <span class="font-semibold">Falla:</span> {{ diagnosticoSeleccionado.descripcion_problema }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </section>

                <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/30 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <WrenchScrewdriverIcon class="h-5 w-5 text-gray-400" />
                            <h2 class="font-semibold text-gray-900">Detalle de Servicios</h2>
                        </div>
                        <button type="button" @click="agregarServicio" class="text-sm text-taller-blue-dark hover:text-blue-700 font-medium flex items-center gap-1">
                            <PlusIcon class="h-4 w-4" /> Agregar Ítem
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="hidden sm:grid sm:grid-cols-12 gap-4 text-xs font-medium text-gray-500 uppercase tracking-wider px-2">
                            <div class="sm:col-span-5">Descripción del Servicio</div>
                            <div class="sm:col-span-2 text-center">Cant.</div>
                            <div class="sm:col-span-2 text-right">Precio Unit.</div>
                            <div class="sm:col-span-2 text-right">Total</div>
                            <div class="sm:col-span-1"></div>
                        </div>

                        <div v-for="(servicio, index) in form.servicios" :key="index"
                             class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start bg-gray-50 rounded-lg p-3 sm:bg-transparent sm:p-0 border sm:border-0 border-gray-200">

                            <div class="sm:col-span-5 space-y-2">
                                <select v-model="servicio.servicio_id" @change="onServicioChange(index)"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm py-1.5 text-xs sm:text-sm"
                                    :class="{ 'border-red-500': form.errors[`servicios.${index}.servicio_id`] }">
                                    <option value="">-- Seleccionar Servicio --</option>
                                    <option v-for="s in servicios" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                                </select>
                                <input type="text" v-model="servicio.descripcion" placeholder="Detalles adicionales..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark text-xs py-1.5" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="sm:hidden text-xs font-bold text-gray-500">Cant:</label>
                                <input type="number" v-model="servicio.cantidad" min="1" step="0.01"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm py-1.5 text-center"
                                    :class="{ 'border-red-500': form.errors[`servicios.${index}.cantidad`] }" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="sm:hidden text-xs font-bold text-gray-500">Precio:</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1.5 text-gray-400 text-xs">$</span>
                                    <input type="number" v-model="servicio.precio_unitario" step="0.01" min="0"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm py-1.5 pl-6 text-right"
                                        :class="{ 'border-red-500': form.errors[`servicios.${index}.precio_unitario`] }" />
                                </div>
                            </div>

                            <div class="sm:col-span-2 text-right py-2 font-medium text-gray-900 sm:text-sm flex justify-between sm:block">
                                <span class="sm:hidden text-xs font-bold text-gray-500">Subtotal:</span>
                                ${{ (servicio.cantidad * servicio.precio_unitario).toFixed(2) }}
                            </div>

                            <div class="sm:col-span-1 flex justify-end sm:justify-center py-1">
                                <button type="button" @click="eliminarServicio(index)"
                                    :disabled="form.servicios.length === 1"
                                    class="text-red-400 hover:text-red-600 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notas u Observaciones</label>
                    <textarea v-model="form.observaciones" rows="3"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm"
                        placeholder="Condiciones de entrega, notas internas..."></textarea>
                </div>
            </div>

            <div class="space-y-6">

                <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/30">
                        <h2 class="font-semibold text-gray-900 text-sm">Configuración de la Orden</h2>
                    </div>
                    <div class="p-5 space-y-5">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                              Mecánico Asignado <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <UserIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                                <select v-model="form.mecanico_id"
                                    class="block w-full rounded-lg border-gray-300 pl-10 focus:ring-taller-blue-dark focus:border-taller-blue-dark sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.mecanico_id }">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="m in mecanicos" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                                </select>
                            </div>
                            <p v-if="form.errors.mecanico_id" class="mt-1 text-xs text-red-600">{{ form.errors.mecanico_id }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Estado Inicial</label>
                            <div class="relative">
                                <TagIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                                <select v-model="form.estado" class="block w-full rounded-lg border-gray-300 pl-10 focus:ring-taller-blue-dark focus:border-taller-blue-dark sm:text-sm">
                                    <option v-for="(label, key) in estados" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                  Fecha Inicio <span class="text-red-500">*</span>
                                </label>
                                <input type="date" v-model="form.fecha_creacion"
                                    class="block w-full rounded-lg border-gray-300 text-xs focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                    :class="{ 'border-red-500': form.errors.fecha_creacion }" />
                                <p v-if="form.errors.fecha_creacion" class="mt-1 text-xs text-red-600">{{ form.errors.fecha_creacion }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                  Fecha Fin (Est.) <span class="text-red-500">*</span>
                                </label>
                                <input type="date" v-model="form.fecha_fin_estimada"
                                    :min="form.fecha_creacion"
                                    class="block w-full rounded-lg border-gray-300 text-xs focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                                    :class="{ 'border-red-500': form.errors.fecha_fin_estimada }" />
                                <p v-if="form.errors.fecha_fin_estimada" class="mt-1 text-xs text-red-600">{{ form.errors.fecha_fin_estimada }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-bold text-gray-900 flex items-center gap-2">
                            <CalculatorIcon class="h-5 w-5 text-taller-blue-dark" />
                            Resumen de Costos
                        </h2>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Servicios / Repuestos</span>
                            <span class="font-medium text-gray-900">${{ subtotalRepuestos.toFixed(2) }}</span>
                        </div>

                        <div class="pt-2 border-t border-dashed border-gray-200">
                            <label class="block text-xs font-medium text-gray-500 mb-1">
                              Mano de Obra <span class="text-red-500">*</span>
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" v-model="form.costo_mano_obra" min="0" step="0.01"
                                    class="block w-full rounded-md border-gray-300 pl-7 pr-3 text-right focus:ring-taller-blue-dark focus:border-taller-blue-dark sm:text-sm font-medium"
                                    :class="{ 'border-red-500': form.errors.costo_mano_obra }" />
                            </div>
                            <p v-if="form.errors.costo_mano_obra" class="mt-1 text-xs text-red-600">{{ form.errors.costo_mano_obra }}</p>
                        </div>

                        <div class="pt-4 border-t border-gray-200 flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Total Estimado</span>
                            <span class="text-2xl font-bold text-taller-blue-dark">${{ totalGeneral.toFixed(2) }}</span>
                        </div>
                    </div>
                </section>

            </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
