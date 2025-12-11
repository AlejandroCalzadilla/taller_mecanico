<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    cliente: Object,
    tipos: Object, // Recibir los tipos del controlador
});

const form = useForm({
    nombre: props.cliente.nombre,
    email: props.cliente.email,
    telefono: props.cliente.telefono || '',
    direccion: props.cliente.direccion || '',
    tipo: props.cliente.tipo,
    estado: props.cliente.estado,
});

const tiposUsuario = props.tipos;;

const submit = () => {
    form.put(route('admin.clientes.update', props.cliente.id));
};
</script>

<template>
    <Head :title="`Editar - ${cliente.nombre}`" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Editar Usuario: {{ cliente.nombre }}</h1>
                <Link
                    :href="route('admin.clientes.index')"
                    class="ml-4 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200"
                >
                    Volver a la lista
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Información Personal -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="nombre" value="Nombre Completo *" />
                                    <TextInput
                                        id="nombre"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.nombre"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.nombre" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Correo Electrónico *" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                            </div>

                            <!-- Contacto y Tipo de Usuario -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="telefono" value="Teléfono" />
                                    <TextInput
                                        id="telefono"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.telefono"
                                    />
                                    <InputError class="mt-2" :message="form.errors.telefono" />
                                </div>

                                <div>
                                    <InputLabel for="tipo" value="Tipo de Usuario *" />
                                    <select
                                        id="tipo"
                                        v-model="form.tipo"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                        required
                                    >
                                        <option v-for="(label, value) in tiposUsuario" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.tipo" />
                                </div>
                            </div>

                            <!-- Estado y Información Adicional -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="estado" value="Estado *" />
                                    <select
                                        id="estado"
                                        v-model="form.estado"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-taller-blue-dark focus:ring-taller-blue-dark"
                                        required
                                    >
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.estado" />
                                </div>

                                <div class="flex items-end">
                                    <div class="w-full">
                                        <p class="text-sm text-gray-500 mb-2">Usuario Actual</p>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                            :class="{
                                                'bg-blue-100 text-blue-800': cliente.tipo === 'cliente',
                                                'bg-green-100 text-green-800': cliente.tipo === 'mecanico',
                                                'bg-purple-100 text-purple-800': cliente.tipo === 'secretaria',
                                                'bg-orange-100 text-orange-800': cliente.tipo === 'propietario'
                                            }">
                                            {{ tiposUsuario[cliente.tipo] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <InputLabel for="direccion" value="Dirección" />
                                <Textarea
                                    id="direccion"
                                    class="mt-1 block w-full"
                                    v-model="form.direccion"
                                    rows="3"
                                />
                                <InputError class="mt-2" :message="form.errors.direccion" />
                            </div>

                            <!-- Información de Cambio de Tipo -->
                            <div v-if="form.tipo !== cliente.tipo"
                                 class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Cambio de Tipo de Usuario
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>
                                                Estás cambiando el tipo de usuario de
                                                <strong>{{ tiposUsuario[cliente.tipo] }}</strong> a
                                                <strong>{{ tiposUsuario[form.tipo] }}</strong>.
                                            </p>
                                            <p class="mt-1">
                                                <strong>Nota:</strong> Este cambio afectará los permisos y accesos del usuario en el sistema.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end space-x-4 pt-6">
                                <Link
                                    :href="route('admin.clientes.index')"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition duration-200"
                                >
                                    Cancelar
                                </Link>
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    class="bg-taller-blue-dark hover:bg-taller-blue-light text-white"
                                >
                                    <span v-if="form.processing">Actualizando...</span>
                                    <span v-else>Actualizar Usuario</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
