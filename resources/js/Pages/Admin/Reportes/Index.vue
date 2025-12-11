<!-- resources/js/Pages/Admin/Reportes/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, computed, watch, onMounted } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import {
    ChartBarIcon,
    CurrencyDollarIcon,
    WrenchScrewdriverIcon,
    UserGroupIcon,
    TruckIcon,
    CalendarDaysIcon,
    DocumentChartBarIcon,
    ArrowDownTrayIcon,
    EyeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    datos: Object,
    filtros: Object,
    estadisticasGenerales: Object
})

const form = useForm({
    fecha_inicio: props.filtros.fecha_inicio,
    fecha_fin: props.filtros.fecha_fin,
    tipo_reporte: props.filtros.tipo_reporte,
    periodo: props.filtros.periodo
})

const tiposReporte = [
    { id: 'financiero', nombre: 'Financiero', icono: CurrencyDollarIcon, color: 'green' },
    { id: 'servicios', nombre: 'Servicios', icono: WrenchScrewdriverIcon, color: 'blue' },
    { id: 'mecanicos', nombre: 'Mecánicos', icono: UserGroupIcon, color: 'purple' },
    { id: 'clientes', nombre: 'Clientes', icono: UserGroupIcon, color: 'orange' },
    { id: 'vehiculos', nombre: 'Vehículos', icono: TruckIcon, color: 'indigo' }
]

const periodos = [
    { id: 'diario', nombre: 'Diario' },
    { id: 'semanal', nombre: 'Semanal' },
    { id: 'mensual', nombre: 'Mensual' },
    { id: 'anual', nombre: 'Anual' },
    { id: 'personalizado', nombre: 'Personalizado' }
]

// Métodos de formato
const formatMoney = (amount) => {
    if (!amount) return 'S/. 0.00'
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(amount)
}

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-BO').format(number)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES')
}

// Filtrado automático
watch(() => form.data(), (value) => {
    form.get(route('admin.reportes.index'), {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}, { deep: true })

const exportarReporte = () => {
    form.post(route('admin.reportes.exportar'), {
        preserveScroll: true
    })
}

// Computed para datos del reporte actual
const datosReporte = computed(() => {
    return props.datos || {}
})

const tituloReporte = computed(() => {
    const tipo = tiposReporte.find(t => t.id === form.tipo_reporte)
    return tipo ? tipo.nombre : 'Reporte'
})
</script>

<template>
    <AdminLayout>
        <div class="px-4 sm:px-6 lg:px-8 py-8 bg-taller-cream min-h-screen">

            <!-- Header -->
            <div class="md:flex md:items-center md:justify-between mb-8">
                <div class="min-w-0 flex-1">
                    <h2 class="text-2xl font-bold leading-7 text-taller-black sm:truncate sm:text-3xl sm:tracking-tight flex items-center gap-3">
                        <div class="p-2 bg-taller-blue-light rounded-lg border border-taller-blue-dark shadow-sm">
                            <ChartBarIcon class="h-6 w-6 text-taller-blue-dark" />
                        </div>
                        Reportes y Estadísticas
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 ml-12">
                        Análisis detallado del rendimiento del taller
                    </p>
                </div>

                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <button
                        @click="exportarReporte"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-taller-blue-dark text-sm font-medium rounded-lg text-taller-blue-dark bg-white hover:bg-taller-blue-light transition-colors"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4" />
                        Exportar
                    </button>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                            <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Ingresos Hoy</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatMoney(estadisticasGenerales.ingresos_hoy) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                            <CalendarDaysIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Citas Hoy</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatNumber(estadisticasGenerales.citas_hoy) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-purple-100 rounded-lg">
                            <WrenchScrewdriverIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Órdenes Pendientes</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatNumber(estadisticasGenerales.ordenes_pendientes) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-orange-100 rounded-lg">
                            <UserGroupIcon class="h-6 w-6 text-orange-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Clientes Activos</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatNumber(estadisticasGenerales.clientes_activos) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros del Reporte</h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Tipo de Reporte -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Reporte
                        </label>
                        <select
                            v-model="form.tipo_reporte"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                        >
                            <option
                                v-for="tipo in tiposReporte"
                                :key="tipo.id"
                                :value="tipo.id"
                            >
                                {{ tipo.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Periodo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Período
                        </label>
                        <select
                            v-model="form.periodo"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                        >
                            <option
                                v-for="periodo in periodos"
                                :key="periodo.id"
                                :value="periodo.id"
                            >
                                {{ periodo.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Fecha Inicio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha Inicio
                        </label>
                        <input
                            type="date"
                            v-model="form.fecha_inicio"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                        />
                    </div>

                    <!-- Fecha Fin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha Fin
                        </label>
                        <input
                            type="date"
                            v-model="form.fecha_fin"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-taller-blue-dark focus:border-taller-blue-dark"
                        />
                    </div>
                </div>
            </div>

            <!-- Contenido del Reporte -->
            <div class="space-y-6">

                <!-- Reporte Financiero -->
                <div v-if="form.tipo_reporte === 'financiero'" class="space-y-6">
                    <!-- Resumen Financiero -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
                            <p class="text-sm text-gray-500 mb-2">Ingresos Totales</p>
                            <p class="text-2xl font-bold text-taller-black">
                                {{ formatMoney(datosReporte.resumen?.ingresos_totales) }}
                            </p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
                            <p class="text-sm text-gray-500 mb-2">Total de Pagos</p>
                            <p class="text-2xl font-bold text-taller-black">
                                {{ formatNumber(datosReporte.resumen?.pagos_totales) }}
                            </p>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
                            <p class="text-sm text-gray-500 mb-2">Promedio por Pago</p>
                            <p class="text-2xl font-bold text-taller-black">
                                {{ formatMoney(datosReporte.resumen?.promedio_pago) }}
                            </p>
                        </div>
                    </div>

                    <!-- Ingresos por Método de Pago -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Ingresos por Método de Pago</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div
                                v-for="metodo in datosReporte.ingresos_por_metodo"
                                :key="metodo.metodo_pago"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-medium text-gray-900 capitalize">
                                        {{ metodo.metodo_pago }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        {{ metodo.cantidad }} pagos
                                    </span>
                                </div>
                                <p class="text-xl font-bold text-taller-blue-dark">
                                    {{ formatMoney(metodo.total) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Estado de Pagos -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Estado de Pagos</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Estado
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Cantidad
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Monto Total
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Pagado
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Pendiente
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr
                                        v-for="estado in datosReporte.estado_pagos"
                                        :key="estado.estado"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 capitalize">
                                            {{ estado.estado.replace('_', ' ') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatNumber(estado.cantidad) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatMoney(estado.monto_total) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatMoney(estado.monto_pagado) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatMoney((estado.monto_total || 0) - (estado.monto_pagado || 0)) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Servicios -->
                <div v-if="form.tipo_reporte === 'servicios'" class="space-y-6">
                    <!-- Servicios Más Solicitados -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Servicios Más Solicitados</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Servicio
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Tipo
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Cantidad
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Ingresos
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Precio Promedio
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr
                                        v-for="servicio in datosReporte.servicios_mas_solicitados"
                                        :key="servicio.nombre"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ servicio.nombre }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500 capitalize">
                                            {{ servicio.tipo }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatNumber(servicio.cantidad) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatMoney(servicio.ingresos) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatMoney(servicio.precio_promedio) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Mecánicos -->
                <div v-if="form.tipo_reporte === 'mecanicos'" class="space-y-6">
                    <!-- Rendimiento de Mecánicos -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Rendimiento de Mecánicos</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Mecánico
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Órdenes
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Completadas
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Ingresos
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Promedio/Orden
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr
                                        v-for="mecanico in datosReporte.rendimiento_mecanicos"
                                        :key="mecanico.id"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ mecanico.nombre }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatNumber(mecanico.total_ordenes) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatNumber(mecanico.ordenes_completadas) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatMoney(mecanico.ingresos_generados) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            {{ formatMoney(mecanico.promedio_por_orden) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Mensaje cuando no hay datos -->
                <div
                    v-if="!datosReporte || Object.keys(datosReporte).length === 0"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center"
                >
                    <DocumentChartBarIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay datos disponibles</h3>
                    <p class="text-gray-500">
                        No se encontraron datos para los filtros seleccionados.
                    </p>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
