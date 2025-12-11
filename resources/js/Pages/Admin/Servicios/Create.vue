<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tipos: Object,
});

const form = useForm({
    nombre: '',
    descripcion: '',
    tipo: '',
    precio_base: '',
    duracion_estimada: '',
    estado: 'activo',
});

const submit = () => {
    form.post(route('admin.servicios.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Crear Nuevo Servicio" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Crear Nuevo Servicio</h1>
                    <p class="text-sm text-gray-600 mt-1">Agrega un nuevo servicio al catálogo del taller</p>
                </div>
                <Link :href="route('admin.servicios.index')"
                    class="ml-4 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Formulario -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Información del Servicio</h2>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">
                                Nombre del Servicio *
                            </label>
                            <input
                                type="text"
                                id="nombre"
                                v-model="form.nombre"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                :class="{ 'border-red-300': form.errors.nombre }"
                                placeholder="Ej: Cambio de aceite"
                            />
                            <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">
                                {{ form.errors.nombre }}
                            </p>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700">
                                Descripción
                            </label>
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                :class="{ 'border-red-300': form.errors.descripcion }"
                                placeholder="Descripción detallada del servicio..."
                            />
                            <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">
                                {{ form.errors.descripcion }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tipo -->
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700">
                                    Tipo de Servicio *
                                </label>
                                <select
                                    id="tipo"
                                    v-model="form.tipo"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.tipo }"
                                >
                                    <option value="">Seleccione un tipo</option>
                                    <option v-for="(label, value) in tipos" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.tipo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.tipo }}
                                </p>
                            </div>

                            <!-- Precio Base -->
                            <div>
                                <label for="precio_base" class="block text-sm font-medium text-gray-700">
                                    Precio Base (S/.) *
                                </label>
                                <input
                                    type="number"
                                    id="precio_base"
                                    v-model="form.precio_base"
                                    step="0.01"
                                    min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.precio_base }"
                                    placeholder="0.00"
                                />
                                <p v-if="form.errors.precio_base" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.precio_base }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Duración Estimada -->
                            <div>
                                <label for="duracion_estimada" class="block text-sm font-medium text-gray-700">
                                    Duración Estimada (minutos)
                                </label>
                                <input
                                    type="number"
                                    id="duracion_estimada"
                                    v-model="form.duracion_estimada"
                                    min="1"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.duracion_estimada }"
                                    placeholder="Ej: 60"
                                />
                                <p v-if="form.errors.duracion_estimada" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.duracion_estimada }}
                                </p>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700">
                                    Estado *
                                </label>
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                    :class="{ 'border-red-300': form.errors.estado }"
                                >
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                                <p v-if="form.errors.estado" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.estado }}
                                </p>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('admin.servicios.index')"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-medium transition duration-200"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-taller-blue-dark hover:bg-taller-blue-light text-white px-6 py-2 rounded-md font-medium transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Creando...' : 'Crear Servicio' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
