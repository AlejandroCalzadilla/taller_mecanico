<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-taller-cream">
        <nav class="bg-taller-black border-b border-taller-blue-dark">
            <!-- Primary Navigation Menu -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('dashboard')">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-18 h-12 bg-taller-blue-light rounded-full flex items-center justify-center">
                                    <img src="https://res.cloudinary.com/dcdhuwp0y/image/upload/v1762306771/ARDAYA_MOTORS_pojle3.png"
                                        alt="Logo" class="w-full h-full object-cover">
                                </div>
                                <span class="text-xl font-bold text-taller-blue-light">Ardaya Motors</span>
                            </div>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <Link :href="route('dashboard')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                :class="[$page.url === '/dashboard' ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                            Dashboard
                            </Link>

                            <!-- Links para Clientes -->
                            <template v-if="$page.props.auth.user.tipo === 'cliente'">
                                <Link :href="route('cliente.vehiculos.index')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/mis-vehiculos') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Mis Vehículos
                                </Link>
                                <Link :href="route('cliente.citas.index')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/mis-citas') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Mis Citas
                                </Link>
                                <Link :href="route('cliente.ordenes.index')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/mis-ordenes') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Mis Órdenes
                                </Link>
                            </template>

                            <!-- Links para Mecánicos -->
                            <template v-if="$page.props.auth.user.tipo === 'mecanico'">
                                <Link :href="route('mecanico.dashboard')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/diagnosticos') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Diagnosticos
                                </Link>
                                <Link :href="route('mecanico.ordenes.index')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/ordenes-trabajo') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Órdenes de Trabajo
                                </Link>
                            </template>

                            <!-- Links para Admin/Secretaria -->
                            <template v-if="['secretaria', 'propietario'].includes($page.props.auth.user.tipo)">
                                <Link :href="route('admin.citas.index')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/gestion-citas') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Gestión de Citas
                                </Link>
                                <Link :href="route('admin.clientes.index')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/gestion-clientes') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Gestión de Clientes
                                </Link>
                                <Link :href="route('admin.reportes.index')"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                                    :class="[$page.url.startsWith('/reportes') ? 'border-taller-blue-light text-white' : 'border-transparent text-gray-400 hover:text-white hover:border-gray-300']">
                                Reportes
                                </Link>
                            </template>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-300">
                                    {{ $page.props.auth.user.nombre }}
                                </span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-taller-blue-dark text-white">
                                    {{ $page.props.auth.user.tipo }}
                                </span>

                                <Link :href="route('profile.edit')"
                                    class="text-gray-300 hover:text-white transition duration-150 ease-in-out">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>

                                </Link>

                                <form @submit.prevent="logout">
                                    <button type="submit"
                                        class="text-gray-300 hover:text-white transition duration-150 ease-in-out">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                        </svg>

                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:bg-gray-700 focus:text-white transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path
                                    :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="route('dashboard')"
                        class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out focus:outline-none"
                        :class="[$page.url === '/dashboard' ? 'border-taller-blue-light bg-taller-blue-dark text-white' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-700 hover:border-gray-300']">
                    Dashboard
                    </Link>

                    <!-- Responsive links para cada tipo de usuario -->
                    <template v-if="$page.props.auth.user.tipo === 'cliente'">
                        <Link :href="route('cliente.vehiculos.index')"
                            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out focus:outline-none"
                            :class="[$page.url.startsWith('/mis-vehiculos') ? 'border-taller-blue-light bg-taller-blue-dark text-white' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-700 hover:border-gray-300']">
                        Mis Vehículos
                        </Link>
                        <Link :href="route('cliente.citas.index')"
                            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out focus:outline-none"
                            :class="[$page.url.startsWith('/mis-citas') ? 'border-taller-blue-light bg-taller-blue-dark text-white' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-700 hover:border-gray-300']">
                        Mis Citas
                        </Link>
                        <Link :href="route('cliente.ordenes.index')"
                            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out focus:outline-none"
                            :class="[$page.url.startsWith('/mis-ordenes') ? 'border-taller-blue-light bg-taller-blue-dark text-white' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-700 hover:border-gray-300']">
                        Mis Órdenes
                        </Link>
                    </template>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-700">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <svg class="h-10 w-10 fill-current text-gray-400" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white">{{ $page.props.auth.user.nombre }}</div>
                            <div class="text-sm font-medium text-gray-400">{{ $page.props.auth.user.email }}</div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <Link :href="route('profile.edit')"
                            class="block px-4 py-2 text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700 transition duration-150 ease-in-out">
                        Perfil
                        </Link>
                        <form @submit.prevent="logout">
                            <button type="submit"
                                class="block w-full text-left px-4 py-2 text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700 transition duration-150 ease-in-out">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
