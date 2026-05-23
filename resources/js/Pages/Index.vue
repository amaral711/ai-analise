<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

const tools = [
    {
        label: 'Análise de Texto',
        description: 'Identifique padrões característicos de texto gerado por ChatGPT, Claude, Gemini e outros LLMs.',
        routeName: 'text.upload',
        color: 'blue',
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        badge: '100 – 5.000 caracteres',
    },
];

const colorMap = {
    blue: {
        icon: 'bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400',
        hover: 'hover:border-blue-300 dark:hover:border-blue-500/40 ',
        badge: 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20',
        arrow: 'text-blue-500 dark:text-blue-400',
    },
};
</script>

<template>
    <Head title="Início" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-foreground text-base font-semibold">Olá, {{ user.name.split(' ')[0] }}</h1>
                <p class="text-muted-foreground text-sm mt-0.5">Escolha um tipo de análise para começar</p>
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-4xl mx-auto">

                <!-- Cards de análise -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <Link
                        v-for="tool in tools"
                        :key="tool.routeName"
                        :href="route(tool.routeName)"
                        class="group flex flex-col p-6 rounded-2xl bg-card border border-border shadow-sm transition-all duration-200 hover:shadow-md"
                        :class="colorMap[tool.color].hover"
                    >
                        <!-- Icon -->
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5 transition-colors"
                            :class="colorMap[tool.color].icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="tool.icon" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <h3 class="text-foreground font-semibold text-sm mb-2">{{ tool.label }}</h3>
                        <p class="text-muted-foreground text-xs leading-relaxed flex-1">{{ tool.description }}</p>

                        <!-- Footer -->
                        <div class="mt-5 flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border"
                                :class="colorMap[tool.color].badge">
                                {{ tool.badge }}
                            </span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5"
                                :class="colorMap[tool.color].arrow"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </Link>
                </div>

                <!-- Históricos rápidos -->
                <div class="mt-10 grid grid-cols-1 md:grid-cols-1 gap-4">
                    <a :href="route('text-analyses.index')"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl bg-card border border-border text-muted-foreground hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-500/30 transition-all text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Histórico de textos
                    </a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
