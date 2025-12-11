<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';

const form = useForm({
    nombre: '',
    email: '',
    telefono: '',
    direccion: '',
    tipo: 'cliente',
    password: '',
    password_confirmation: '',
    estado: 'activo',
});

const tiposUsuario = {
    cliente: 'Cliente',
    mecanico: 'Mecánico',
    secretaria: 'Secretaria',
    propietario: 'Propietario'
};

const submit = () => {
    form.post(route('admin.clientes.store'));
};
</script>

<template>
    <Head title="Crear Usuario" />

    <AdminLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Crear Nuevo Usuario</h1>
                <Link
                    :href="route('admin.clientes.index')"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold transition duration-200"
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

                            <!-- Estado -->
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

                            <!-- Contraseña -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="password" value="Contraseña *" />
                                    <TextInput
                                        id="password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="form.password"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.password" />
                                </div>

                                <div>
                                    <InputLabel for="password_confirmation" value="Confirmar Contraseña *" />
                                    <TextInput
                                        id="password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="form.password_confirmation"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
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
                                    <span v-if="form.processing">Creando...</span>
                                    <span v-else>Crear Usuario</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
