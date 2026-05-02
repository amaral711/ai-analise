<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme.js';

const page = usePage();
const user = page.props.auth.user;
const showMobileSidebar = ref(false);
const { isDark, toggle } = useTheme();

const navItems = [
    {
        label: 'Análise de Imagem',
        routeName: 'dashboard',
        icon: 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z',
    },
    {
        label: 'Histórico de Imagens',
        routeName: 'analyses.index',
        icon: 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z',
    },
    {
        label: 'Análise de Texto',
        routeName: 'text.upload',
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
    },
    {
        label: 'Histórico de Textos',
        routeName: 'text-analyses.index',
        icon: 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z',
    },
    {
        label: 'Análise de Áudio',
        routeName: 'audio.upload',
        icon: 'M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z',
    },
    {
        label: 'Histórico de Áudios',
        routeName: 'audio-analyses.index',
        icon: 'M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z',
    },
];
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gradient-to-br from-slate-100 via-white to-slate-50 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 relative">

        <!-- Background orbs -->
        <div class="absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full bg-violet-500/5 dark:bg-violet-600/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full bg-violet-400/5 dark:bg-violet-800/10 blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/3 right-1/4 w-72 h-72 rounded-full bg-indigo-400/3 dark:bg-indigo-700/5 blur-3xl pointer-events-none"></div>

        <!-- Mobile overlay -->
        <div
            v-if="showMobileSidebar"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            @click="showMobileSidebar = false"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 flex-shrink-0 flex flex-col bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 transition-transform duration-200
                   lg:static lg:translate-x-0 lg:z-auto lg:h-full"
            :class="showMobileSidebar ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-200 dark:border-zinc-800">
                <img src="/logo.jpeg" alt="DeepScan" class="w-8 h-8 rounded-lg object-cover flex-shrink-0" />
                <div>
                    <span class="text-zinc-900 dark:text-white font-semibold text-sm tracking-tight">DeepScan</span>
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
                        ? 'bg-violet-50 dark:bg-violet-600/15 text-violet-700 dark:text-violet-400 ring-1 ring-violet-200 dark:ring-violet-500/20'
                        : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800'"
                    @click="showMobileSidebar = false"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User -->
            <div class="border-t border-slate-200 dark:border-zinc-800 p-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-violet-100 dark:bg-violet-500/20 flex items-center justify-center flex-shrink-0 ring-1 ring-violet-300 dark:ring-violet-500/30 overflow-hidden">
                        <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                        <span v-else class="text-violet-700 dark:text-violet-300 text-xs font-bold uppercase">{{ user.name[0] }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-zinc-800 dark:text-zinc-200 text-sm font-medium truncate leading-tight">{{ user.name }}</p>
                        <p class="text-zinc-500 text-xs truncate leading-tight">{{ user.email }}</p>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                    </svg>
                    Sair da conta
                </Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative z-10">

            <!-- Topbar (all screen sizes) -->
            <div class="flex items-center gap-4 px-4 py-3 border-b border-slate-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-sm">

                <!-- Mobile: hamburger -->
                <button
                    @click="showMobileSidebar = true"
                    class="lg:hidden p-1.5 rounded-md text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Mobile: logo -->
                <div class="lg:hidden flex items-center gap-2">
                    <img src="/logo.jpeg" alt="DeepScan" class="w-6 h-6 rounded-md object-cover" />
                    <span class="text-zinc-900 dark:text-white font-semibold text-sm">DeepScan</span>
                </div>

                <!-- Spacer -->
                <div class="flex-1"></div>

                <!-- Theme toggle (always visible, icon only) -->
                <button
                    @click="toggle"
                    class="p-2 rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                    :title="isDark ? 'Mudar para tema claro' : 'Mudar para tema escuro'"
                >
                    <!-- Sol: aparece no modo escuro para indicar "ir para claro" -->
                    <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <!-- Lua: aparece no modo claro para indicar "ir para escuro" -->
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>
            </div>

            <!-- Page header -->
            <header v-if="$slots.header" data-layout-header class="px-6 lg:px-10 py-5 border-b border-slate-200/80 dark:border-zinc-800">
                <slot name="header" />
            </header>

            <!-- Content -->
            <main data-content class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
