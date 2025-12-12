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
    EyeIcon,
    ChartPieIcon,
    ArrowTrendingUpIcon
} from '@heroicons/vue/24/outline'
import { Line, Bar, Pie, Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
)

const props = defineProps({
    kpis: Object,
    datos: Object,
    filtros: Object,
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
    { id: 'citas', nombre: 'Citas', icono: CalendarDaysIcon, color: 'purple' },
    { id: 'mecanicos', nombre: 'Mecánicos', icono: UserGroupIcon, color: 'orange' },
]

const periodos = [
    { id: 'diario', nombre: 'Diario' },
    { id: 'semanal', nombre: 'Semanal' },
    { id: 'mensual', nombre: 'Mensual' },
    { id: 'anual', nombre: 'Anual' },
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

// Computed para gráficos
const chartIngresosPeriodo = computed(() => {
    const labels = Object.keys(props.datos.ingresos_periodo || {})
    const data = Object.values(props.datos.ingresos_periodo || {})
    
    return {
        labels,
        datasets: [
            {
                label: 'Ingresos',
                data,
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#059669',
            }
        ]
    }
})

const chartIngresosPorMetodo = computed(() => {
    const metodos = props.datos.ingresos_por_metodo?.map(m => m.name) || []
    const totales = props.datos.ingresos_por_metodo?.map(m => m.total) || []
    const colores = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
    
    return {
        labels: metodos,
        datasets: [
            {
                label: 'Monto (S/.)',
                data: totales,
                backgroundColor: colores.slice(0, metodos.length),
                borderColor: colores.slice(0, metodos.length),
                borderWidth: 1,
            }
        ]
    }
})

const chartEstadoPagos = computed(() => {
    const estados = props.datos.estado_pagos?.map(e => e.estado) || []
    const cantidades = props.datos.estado_pagos?.map(e => e.cantidad) || []
    
    return {
        labels: estados,
        datasets: [
            {
                label: 'Cantidad de Pagos',
                data: cantidades,
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#06b6d4'
                ].slice(0, estados.length),
            }
        ]
    }
})

const chartServiciosPeriodo = computed(() => {
    const labels = Object.keys(props.datos.servicios_periodo || {})
    const data = Object.values(props.datos.servicios_periodo || {})
    
    return {
        labels,
        datasets: [
            {
                label: 'Servicios Realizados',
                data,
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: '#3b82f6',
                borderWidth: 2,
            }
        ]
    }
})

const chartTasaConversion = computed(() => {
    const labels = Object.keys(props.datos?.tasa_conversion_periodo || {})
    const data = Object.values(props.datos?.tasa_conversion_periodo || {})
    
    return {
        labels: labels.length > 0 ? labels : ['Sin datos'],
        datasets: [
            {
                label: 'Tasa de Conversión (%)',
                data: data.length > 0 ? data : [0],
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#8b5cf6',
            }
        ]
    }
})

const chartRendimientoMecanicos = computed(() => {
    const mecanicos = props.datos.rendimiento_mecanicos?.map(m => m.nombre) || []
    const ingresos = props.datos.rendimiento_mecanicos?.map(m => m.ingresos_generados) || []
    const ordenes = props.datos.rendimiento_mecanicos?.map(m => m.total_ordenes) || []
    
    return {
        labels: mecanicos,
        datasets: [
            {
                label: 'Ingresos Generados (S/.)',
                data: ingresos,
                backgroundColor: '#3b82f6',
                borderColor: '#1e40af',
                borderWidth: 1,
                yAxisID: 'y',
            },
            {
                label: 'Total Órdenes',
                data: ordenes,
                backgroundColor: '#10b981',
                borderColor: '#065f46',
                borderWidth: 1,
                yAxisID: 'y1',
            }
        ]
    }
})

const chartCitasPorEstado = computed(() => {
    const estados = props.datos?.citas_por_estado?.map(c => c.estado) || []
    const cantidades = props.datos?.citas_por_estado?.map(c => c.cantidad) || []
    const colores = ['#10b981', '#f59e0b', '#ef4444']
    
    return {
        labels: estados.length > 0 ? estados : ['Sin datos'],
        datasets: [
            {
                label: 'Cantidad de Citas',
                data: cantidades.length > 0 ? cantidades : [0],
                backgroundColor: colores.slice(0, estados.length || 1),
            }
        ]
    }
})

const chartCitasPeriodo = computed(() => {
    const labels = Object.keys(props.datos?.citas_periodo || {})
    const data = Object.values(props.datos?.citas_periodo || {})
    
    return {
        labels: labels.length > 0 ? labels : ['Sin datos'],
        datasets: [
            {
                label: 'Citas',
                data: data.length > 0 ? data : [0],
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#06b6d4',
            }
        ]
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                font: {
                    size: 12,
                    weight: '500'
                },
                padding: 15,
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: '#e5e7eb',
            },
            ticks: {
                font: {
                    size: 11
                }
            }
        },
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: {
                    size: 11
                }
            }
        }
    }
}

const chartOptionsPie = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
        legend: {
            position: 'right',
            labels: {
                font: {
                    size: 12,
                    weight: '500'
                },
                padding: 15,
            }
        }
    }
}
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
                        Reportes y Análisis
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 ml-12">
                        Visualización detallada del rendimiento del taller
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

            <!-- KPIs Principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Ingresos Totales</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ formatMoney(kpis.ingresos_totales) }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-lg">
                            <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Por Cobrar</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ formatMoney(kpis.pendiente_cobrar) }}
                            </p>
                        </div>
                        <div class="p-3 bg-orange-100 rounded-lg">
                            <ArrowTrendingUpIcon class="h-6 w-6 text-orange-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Órdenes Completadas</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ formatNumber(kpis.ordenes_completadas) }}
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <WrenchScrewdriverIcon class="h-6 w-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tasa Conversión</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ kpis.tasa_conversion }}%
                            </p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <ChartPieIcon class="h-6 w-6 text-purple-600" />
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

            <!-- Gráficos según tipo de reporte -->
            <div class="space-y-8">

                <!-- Reporte Financiero -->
                <div v-if="form.tipo_reporte === 'financiero'" class="space-y-8">
                    <!-- Ingresos por Período -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Evolución de Ingresos</h3>
                        <div style="height: 300px;">
                            <Line :data="chartIngresosPeriodo" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Ingresos por Método -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ingresos por Método de Pago</h3>
                            <div style="height: 300px;">
                                <Bar :data="chartIngresosPorMetodo" :options="chartOptions" />
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado de Pagos</h3>
                            <div style="height: 300px;">
                                <Doughnut :data="chartEstadoPagos" :options="chartOptionsPie" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Servicios -->
                <div v-if="form.tipo_reporte === 'servicios'" class="space-y-8">
                    <!-- Servicios por Período -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Servicios Realizados por Período</h3>
                        <div style="height: 300px;">
                            <Bar :data="chartServiciosPeriodo" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Tabla de Servicios Más Solicitados -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Servicios Más Solicitados</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="servicio in datos.servicios_mas_usados" :key="servicio.nombre">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ servicio.nombre }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ servicio.tipo }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ formatNumber(servicio.cantidad) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">{{ formatMoney(servicio.ingresos) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Citas -->
                <div v-if="form.tipo_reporte === 'citas'" class="space-y-8">
                    <!-- Citas por Período -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Citas por Período</h3>
                            <div style="height: 300px;">
                                <Line :data="chartCitasPeriodo" :options="chartOptions" />
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Estado</h3>
                            <div style="height: 300px;">
                                <Pie :data="chartCitasPorEstado" :options="chartOptionsPie" />
                            </div>
                        </div>
                    </div>

                    <!-- Tasa de Conversión -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tasa de Conversión por Período (citas confirmadas / citas totales)</h3>
                        <div style="height: 300px;">
                            <Line :data="chartTasaConversion" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Reporte de Mecánicos -->
                <div v-if="form.tipo_reporte === 'mecanicos'" class="space-y-8">
                    <!-- Tabla de Rendimiento -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rendimiento de Mecánicos</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mecánico</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Órdenes</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Completadas</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ingresos</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Completación (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="mecanico in datos.rendimiento_mecanicos" :key="mecanico.id">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ mecanico.nombre }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ formatNumber(mecanico.total_ordenes) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ formatNumber(mecanico.ordenes_completadas) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">{{ formatMoney(mecanico.ingresos_generados) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="mecanico.tasa_completacion >= 80 ? 'bg-green-100 text-green-800' : mecanico.tasa_completacion >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'"
                                            >
                                                {{ mecanico.tasa_completacion }}%
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Mensaje cuando no hay datos -->
                <div
                    v-if="!datos || Object.keys(datos).length === 0"
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
