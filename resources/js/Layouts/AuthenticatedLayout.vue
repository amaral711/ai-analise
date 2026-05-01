<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
const showMobileSidebar = ref(false);

const navItems = [
    {
        label: 'Nova Análise',
        routeName: 'dashboard',
        icon: 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z',
    },
    {
        label: 'Histórico',
        routeName: 'analyses.index',
        icon: 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z',
    },
];
</script>

<template>
    <div class="flex min-h-screen bg-zinc-950">

        <!-- Mobile overlay -->
        <div
            v-if="showMobileSidebar"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            @click="showMobileSidebar = false"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-zinc-900 border-r border-zinc-800 transition-transform duration-200
                   lg:relative lg:translate-x-0 lg:z-auto"
            :class="showMobileSidebar ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-4 border-b border-zinc-800">
                <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                </div>
                <div>
                    <span class="text-white font-semibold text-sm tracking-tight">VeriCord</span>
                    <p class="text-zinc-500 text-[10px] leading-none mt-0.5">Detector de conteúdo IA</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-0.5">
                <Link
                    v-for="item in navItems"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150"
                    :class="route().current(item.routeName)
                        ? 'bg-violet-600/15 text-violet-400 ring-1 ring-violet-500/20'
                        : 'text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800'"
                    @click="showMobileSidebar = false"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User -->
            <div class="border-t border-zinc-800 p-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-violet-500/20 flex items-center justify-center flex-shrink-0 ring-1 ring-violet-500/30">
                        <span class="text-violet-300 text-xs font-bold uppercase">{{ user.name[0] }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-zinc-200 text-sm font-medium truncate leading-tight">{{ user.name }}</p>
                        <p class="text-zinc-500 text-xs truncate leading-tight">{{ user.email }}</p>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    Sair da conta
                </Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Mobile topbar -->
            <div class="lg:hidden flex items-center gap-4 px-4 py-3 border-b border-zinc-800 bg-zinc-900">
                <button
                    @click="showMobileSidebar = true"
                    class="p-1.5 rounded-md text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <span class="text-white font-semibold text-sm">VeriCord</span>
            </div>

            <!-- Page header -->
            <header v-if="$slots.header" class="px-6 lg:px-10 py-5 border-b border-zinc-800">
                <slot name="header" />
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
