<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    analyses: Object,
});

const classificationConfig = {
    human:        { label: 'Humano',        bg: 'bg-emerald-500/15', text: 'text-emerald-400', dot: 'bg-emerald-400', border: 'border-emerald-500/25' },
    inconclusive: { label: 'Inconclusivo',  bg: 'bg-amber-500/15',   text: 'text-amber-400',   dot: 'bg-amber-400',   border: 'border-amber-500/25'   },
    ai:           { label: 'Gerado por IA', bg: 'bg-red-500/15',     text: 'text-red-400',     dot: 'bg-red-400',     border: 'border-red-500/25'     },
};

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function truncate(text, len = 90) {
    return text.length > len ? text.slice(0, len) + '...' : text;
}
</script>

<template>
    <Head title="Histórico de Análises" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-zinc-100 text-base font-semibold">Histórico de Análises</h1>
                    <p class="text-zinc-500 text-sm mt-0.5">
                        {{ analyses.total }} análise{{ analyses.total !== 1 ? 's' : '' }} realizadas
                    </p>
                </div>
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-violet-600 text-white hover:bg-violet-500 transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nova análise
                </Link>
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-5xl mx-auto">

                <!-- Empty state -->
                <div v-if="analyses.data.length === 0" class="rounded-xl border border-zinc-800 bg-zinc-900">
                    <div class="py-20 flex flex-col items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-zinc-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.25">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="text-zinc-300 font-medium">Nenhuma análise ainda</p>
                            <p class="text-zinc-500 text-sm mt-1">Faça sua primeira análise para ver o histórico aqui.</p>
                        </div>
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-violet-600 text-white hover:bg-violet-500 transition-colors mt-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                            </svg>
                            Fazer primeira análise
                        </Link>
                    </div>
                </div>

                <!-- Table -->
                <div v-else class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-zinc-800">
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Imagem</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Classificação</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider hidden sm:table-cell">Data</th>
                                <th class="px-4 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            <tr
                                v-for="item in analyses.data"
                                :key="item.id"
                                class="hover:bg-zinc-800/30 transition-colors group"
                            >
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-sm text-zinc-300 truncate">{{ truncate(item.text) }}</p>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-12 h-1.5 rounded-full bg-zinc-800 overflow-hidden">
                                            <div
                                                class="h-full rounded-full"
                                                :class="classificationConfig[item.classification].dot"
                                                :style="{ width: Math.round(item.ai_score * 100) + '%', opacity: 0.7 }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-semibold text-zinc-200 tabular-nums">
                                            {{ Math.round(item.ai_score * 100) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium border"
                                        :class="[
                                            classificationConfig[item.classification].bg,
                                            classificationConfig[item.classification].text,
                                            classificationConfig[item.classification].border,
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="classificationConfig[item.classification].dot"></span>
                                        {{ classificationConfig[item.classification].label }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-xs text-zinc-500">{{ formatDate(item.created_at) }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <Link
                                        :href="route('image-analyses.show', item.id)"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-zinc-500 hover:text-violet-400 group-hover:text-zinc-300 transition-colors"
                                    >
                                        Ver
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div
                        v-if="analyses.last_page > 1"
                        class="border-t border-zinc-800 px-6 py-3.5 flex items-center justify-between"
                    >
                        <p class="text-xs text-zinc-500">
                            {{ analyses.total }} análise{{ analyses.total !== 1 ? 's' : '' }} no total
                        </p>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in analyses.links"
                                :key="link.label"
                                :href="link.url ?? ''"
                                v-html="link.label"
                                class="px-3 py-1.5 text-xs rounded-lg border transition-colors"
                                :class="link.active
                                    ? 'bg-violet-600 text-white border-violet-600'
                                    : 'text-zinc-400 border-zinc-700 hover:bg-zinc-800 hover:text-zinc-100'"
                                :aria-disabled="!link.url"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
