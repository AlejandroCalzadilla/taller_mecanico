<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

// Heroicons
import {
  CalendarDaysIcon,
  UserIcon,
  CurrencyDollarIcon,
  PencilSquareIcon,
  Cog6ToothIcon,
  PlusIcon,
  TrashIcon,
  TruckIcon,
  ArrowLeftIcon,
  WrenchScrewdriverIcon,
  ClipboardDocumentCheckIcon,
  BanknotesIcon,
  XMarkIcon,
  CheckCircleIcon,
  ClockIcon,
  NoSymbolIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  orden: Object,
  estados: Object,
  servicios: Array
})

// Estados para Modales
const showAgregarServicio = ref(false)
const showCancelarModal = ref(false)

const nuevoServicio = ref({
  servicio_id: '',
  cantidad: 1,
  precio_unitario: 0,
  descripcion: ''
})

const serviciosDisponibles = computed(() => props.servicios)

// Formularios
const formEstado = useForm({ estado: props.orden.estado })
const formServicio = useForm({ ...nuevoServicio.value })

// Acciones
const updateEstado = () => {
  formEstado.patch(route('admin.ordenes.update-status', props.orden.id), {
    preserveScroll: true,
    onSuccess: () => router.reload()
  })
}

const onServicioChange = () => {
    const serv = props.servicios.find(s => s.id == formServicio.servicio_id);
    if(serv) {
        formServicio.precio_unitario = serv.precio_base;
        formServicio.descripcion = serv.nombre;
    }
}

const agregarNuevoServicio = () => {
  formServicio.post(route('admin.ordenes.add-service', props.orden.id), {
    preserveScroll: true,
    onSuccess: () => {
      showAgregarServicio.value = false;
      formServicio.reset();
      formServicio.servicio_id = ''; // Reset manual
      router.reload();
    }
  })
}

const eliminarServicio = (servicioId) => {
  if(confirm('¿Eliminar este servicio de la orden?')) {
      router.delete(route('admin.ordenes.remove-service', servicioId), {
        preserveScroll: true,
        onSuccess: () => router.reload()
      })
  }
}

const crearPago = () => {
  router.visit(route('admin.pagos.create', { orden_trabajo_id: props.orden.id }))
}

const cancelarOrden = () => {
    formEstado.estado = 'cancelada';
    formEstado.patch(route('admin.ordenes.update-status', props.orden.id), {
        onSuccess: () => showCancelarModal.value = false
    });
}

// Utilidades
const formatDate = (date) => new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' })
const formatMoney = (amount) => new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(amount)

const pasos = ['presupuestada', 'aprobada', 'en_proceso', 'completada', 'entregada']
const pasoActualIndex = computed(() => pasos.indexOf(props.orden.estado))

const getEstadoBadgeClass = (estado) => {
  const classes = {
    'presupuestada': 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
    'aprobada': 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'en_proceso': 'bg-purple-50 text-purple-700 ring-purple-600/20',
    'completada': 'bg-green-50 text-green-700 ring-green-600/20',
    'entregada': 'bg-gray-50 text-gray-700 ring-gray-600/20',
    'cancelada': 'bg-red-50 text-red-700 ring-red-600/20'
  }
  return `inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ${classes[estado] || 'bg-gray-50 text-gray-600'}`
}
</script>

<template>
  <AdminLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mb-6">
            <Link :href="route('admin.ordenes.index')" class="flex items-center text-sm text-gray-500 hover:text-taller-blue-dark transition-colors">
                <ArrowLeftIcon class="h-4 w-4 mr-1" />
                Volver a Órdenes
            </Link>
        </div>

        <div class="md:flex md:items-center md:justify-between mb-8">
          <div class="min-w-0 flex-1">
            <h2 class="text-3xl font-bold leading-7 text-gray-900 sm:truncate sm:tracking-tight flex items-center gap-3">
               <div class="p-2 bg-white rounded-lg border border-gray-200 shadow-sm">
                   <ClipboardDocumentCheckIcon class="h-8 w-8 text-taller-blue-dark" />
               </div>
               Orden #{{ orden.codigo }}
            </h2>
            <div class="mt-2 flex items-center gap-4 text-sm text-gray-500 ml-14">
                <span class="flex items-center">
                    <UserIcon class="mr-1.5 h-4 w-4 text-gray-400" />
                    {{ orden.mecanico.nombre }}
                </span>
                <span class="flex items-center">
                    <CalendarDaysIcon class="mr-1.5 h-4 w-4 text-gray-400" />
                    {{ formatDate(orden.created_at) }}
                </span>
                <span :class="getEstadoBadgeClass(orden.estado)">
                    {{ estados[orden.estado] }}
                </span>
            </div>
          </div>

          <div class="mt-4 flex flex-shrink-0 md:ml-4 md:mt-0 gap-3">
            <Link
              :href="route('admin.ordenes.edit', orden.id)"
              class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
            >
              <PencilSquareIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" />
              Editar
            </Link>

            <button
              v-if="!['entregada', 'cancelada'].includes(orden.estado)"
              @click="showCancelarModal = true"
              class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 shadow-sm ring-1 ring-inset ring-red-300 hover:bg-red-50 transition-colors"
            >
              <NoSymbolIcon class="-ml-0.5 mr-1.5 h-5 w-5" />
              Cancelar
            </button>
          </div>
        </div>

        <div class="mb-8" v-if="orden.estado !== 'cancelada'">
            <div class="relative">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-between">
                    <div v-for="(paso, index) in pasos" :key="paso" class="flex flex-col items-center">
                        <div
                            class="h-8 w-8 rounded-full flex items-center justify-center ring-4 ring-white"
                            :class="[
                                index <= pasoActualIndex ? 'bg-taller-blue-dark text-white' : 'bg-gray-200 text-gray-500'
                            ]"
                        >
                            <CheckCircleIcon v-if="index < pasoActualIndex" class="h-5 w-5" />
                            <span v-else class="text-xs font-bold">{{ index + 1 }}</span>
                        </div>
                        <span class="mt-2 text-xs font-medium text-gray-500 uppercase">{{ estados[paso] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

          <div class="space-y-6 lg:col-span-2">

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-6 flex items-center gap-2">
                    <TruckIcon class="h-5 w-5 text-gray-500" />
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Vehículo y Cliente</h3>
                </div>
                <div class="px-4 py-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-bold">Vehículo</p>
                        <p class="text-lg font-bold text-gray-900 mt-1">{{ orden.diagnostico.cita.vehiculo.marca }} {{ orden.diagnostico.cita.vehiculo.modelo }}</p>
                        <p class="text-sm text-gray-600 bg-gray-100 inline-block px-2 py-0.5 rounded font-mono">{{ orden.diagnostico.cita.vehiculo.placa }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-bold">Cliente</p>
                        <p class="text-lg font-medium text-gray-900 mt-1">{{ orden.diagnostico.cita.cliente.nombre }}</p>
                        <p class="text-sm text-gray-500">{{ orden.diagnostico.cita.cliente.email }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
                <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-4 sm:px-6 flex justify-between items-center">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 flex items-center gap-2">
                        <WrenchScrewdriverIcon class="h-5 w-5 text-gray-500" />
                        Servicios y Repuestos
                    </h3>
                    <button v-if="orden.estado !== 'entregada'" @click="showAgregarServicio = true" class="text-sm text-taller-blue-dark font-medium flex items-center hover:underline">
                        <PlusIcon class="h-4 w-4 mr-1" /> Agregar Ítem
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cant.</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unitario</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="servicio in orden.servicios" :key="servicio.id">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium">{{ servicio.descripcion }}</div>
                                    <div class="text-xs text-gray-500" v-if="servicio.servicio">{{ servicio.servicio.nombre }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ servicio.cantidad }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 text-right">{{ formatMoney(servicio.precio_unitario) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">{{ formatMoney(servicio.subtotal) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <button @click="eliminarServicio(servicio.id)" class="text-red-400 hover:text-red-600 transition-colors">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-sm font-medium text-gray-500 text-right">Mano de Obra:</td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900 text-right">{{ formatMoney(orden.costo_mano_obra) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-base font-bold text-gray-900 text-right">Total Final:</td>
                                <td class="px-6 py-3 text-base font-bold text-taller-blue-dark text-right">{{ formatMoney(orden.subtotal) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 p-6">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Notas / Observaciones</h3>
                <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    {{ orden.observaciones || 'Sin observaciones registradas.' }}
                </p>
            </div>

          </div>

          <div class="space-y-6">

            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <Cog6ToothIcon class="h-5 w-5 text-gray-400" />
                    Gestión de Estado
                </h3>

                <form @submit.prevent="updateEstado">
                    <select
                        v-model="formEstado.estado"
                        @change="updateEstado"
                        :disabled="orden.estado === 'entregada' || orden.estado === 'cancelada'"
                        class="block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-taller-blue-dark sm:text-sm sm:leading-6 disabled:bg-gray-100"
                    >
                        <option v-for="(label, value) in estados" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                </form>
            </div>

            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <BanknotesIcon class="h-5 w-5 text-gray-400" />
                        Pagos
                    </h3>
                    <button
                        v-if="orden.estado === 'completada'"
                        @click="crearPago"
                        class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded-full font-medium hover:bg-green-100 transition-colors"
                    >
                        + Nuevo Pago
                    </button>
                </div>

                <div v-if="orden.pagos && orden.pagos.length > 0" class="space-y-3">
                    <div v-for="pago in orden.pagos" :key="pago.id" class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500">Total:</span>
                            <span class="font-medium">{{ formatMoney(pago.monto_total) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-500">Pagado:</span>
                            <span class="font-bold text-green-600">{{ formatMoney(pago.monto_pagado) }}</span>
                        </div>
                        <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                            <span class="text-gray-500">Pendiente:</span>
                            <span class="font-bold text-red-500">{{ formatMoney(pago.monto_pendiente) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-gray-400 text-sm">
                    No hay registros de pago.
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <ClockIcon class="h-5 w-5 text-gray-400" />
                    Cronograma
                </h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Inicio:</dt>
                        <dd class="text-gray-900 font-medium">{{ formatDate(orden.fecha_creacion) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Estimado:</dt>
                        <dd class="text-gray-900 font-medium">{{ formatDate(orden.fecha_fin_estimada) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2" v-if="orden.fecha_fin_real">
                        <dt class="text-gray-500">Real:</dt>
                        <dd class="text-green-600 font-bold">{{ formatDate(orden.fecha_fin_real) }}</dd>
                    </div>
                </dl>
            </div>

          </div>
        </div>
      </div>

      <div v-if="showAgregarServicio" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4" id="modal-title">Agregar Servicio Adicional</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Servicio</label>
                        <select v-model="formServicio.servicio_id" @change="onServicioChange" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm">
                            <option value="">Seleccionar...</option>
                            <option v-for="s in serviciosDisponibles" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cantidad</label>
                            <input type="number" v-model="formServicio.cantidad" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Precio Unit.</label>
                            <input type="number" v-model="formServicio.precio_unitario" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <input type="text" v-model="formServicio.descripcion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark sm:text-sm">
                    </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" @click="agregarNuevoServicio" :disabled="formServicio.processing" class="inline-flex w-full justify-center rounded-md bg-taller-blue-dark px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-taller-blue-light sm:ml-3 sm:w-auto">Agregar</button>
                <button type="button" @click="showAgregarServicio = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancelar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showCancelarModal" class="relative z-50">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <NoSymbolIcon class="h-6 w-6 text-red-600" />
                  </div>
                  <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Cancelar Orden de Trabajo</h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500">¿Estás seguro de que deseas cancelar esta orden? Esta acción detendrá el flujo de trabajo y no se podrá revertir.</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" @click="cancelarOrden" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">Sí, Cancelar</button>
                <button type="button" @click="showCancelarModal = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Volver</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </AdminLayout>
</template>
