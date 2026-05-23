<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user:     Object,
    analyses: Object,
});

const classificationConfig = {
    human:        { label: 'Humano',        bg: 'bg-emerald-500/15', text: 'text-emerald-400', dot: 'bg-emerald-400', border: 'border-emerald-500/25' },
    inconclusive: { label: 'Inconclusivo',  bg: 'bg-amber-500/15',   text: 'text-amber-400',   dot: 'bg-amber-400',   border: 'border-amber-500/25'   },
    ai:           { label: 'Gerado por IA', bg: 'bg-red-500/15',     text: 'text-red-400',     dot: 'bg-red-400',     border: 'border-red-500/25'     },
};

function getClassConfig(c) {
    return classificationConfig[c] ?? classificationConfig['inconclusive'];
}

const modelConfig = {
    bert:         { label: 'Básico',    cls: 'bg-primary/15 text-primary border-primary/25' },
    detecting_ai: { label: 'Detecting', cls: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/25' },
    claude:       { label: 'Avançado',  cls: 'bg-amber-500/15 text-amber-400 border-amber-500/25' },
};

function modelBadge(model) {
    return modelConfig[model] ?? modelConfig['bert'];
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function truncate(text, len = 80) {
    return text?.length > len ? text.slice(0, len) + '...' : text;
}
</script>

<template>
    <Head :title="`Análises — ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-foreground text-base font-semibold">{{ user.name }}</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">{{ user.email }} · {{ user.credits }} créditos</p>
                </div>
                <Link :href="route('admin.users.index')" class="text-sm text-primary hover:underline">← Usuários</Link>
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-5xl mx-auto">

                <!-- Empty -->
                <div v-if="analyses.data.length === 0" class="rounded-xl border border-border bg-card py-20 flex flex-col items-center gap-3">
                    <p class="text-foreground/80 font-medium">Nenhuma análise encontrada</p>
                    <p class="text-muted-foreground text-sm">Este usuário ainda não realizou análises.</p>
                </div>

                <!-- Table -->
                <div v-else class="rounded-xl border border-border bg-card overflow-hidden">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Texto</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Score</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Classificação</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider hidden md:table-cell">Modelo</th>
                                <th class="px-4 py-3.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider hidden sm:table-cell">Data</th>
                                <th class="px-4 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <tr
                                v-for="item in analyses.data"
                                :key="item.id"
                                class="hover:bg-accent/30 transition-colors group"
                            >
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-sm text-foreground/80 truncate">{{ truncate(item.content) }}</p>
                                    <span v-if="item.status !== 'completed'" class="text-xs text-muted-foreground italic">{{ item.status }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <template v-if="item.ai_score !== null">
                                        <div class="flex items-center gap-2">
                                            <div class="w-12 h-1.5 rounded-full bg-secondary overflow-hidden">
                                                <div
                                                    class="h-full rounded-full"
                                                    :class="getClassConfig(item.classification).dot"
                                                    :style="{ width: Math.round(item.ai_score * 100) + '%', opacity: 0.7 }"
                                                ></div>
                                            </div>
                                            <span class="text-sm font-semibold text-foreground/90 tabular-nums">
                                                {{ Math.round(item.ai_score * 100) }}%
                                            </span>
                                        </div>
                                    </template>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span
                                        v-if="item.classification"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium border"
                                        :class="[getClassConfig(item.classification).bg, getClassConfig(item.classification).text, getClassConfig(item.classification).border]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="getClassConfig(item.classification).dot"></span>
                                        {{ getClassConfig(item.classification).label }}
                                    </span>
                                    <span v-else class="text-xs text-muted-foreground">—</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden md:table-cell">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border"
                                        :class="modelBadge(item.model).cls"
                                    >{{ modelBadge(item.model).label }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="text-xs text-muted-foreground">{{ formatDate(item.created_at) }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <Link
                                        v-if="item.status === 'completed'"
                                        :href="route('text-analyses.show', item.id)"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-muted-foreground hover:text-primary transition-colors"
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
                    <div v-if="analyses.last_page > 1" class="border-t border-border px-6 py-3.5 flex items-center justify-between">
                        <p class="text-xs text-muted-foreground">{{ analyses.total }} análise{{ analyses.total !== 1 ? 's' : '' }} no total</p>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in analyses.links"
                                :key="link.label"
                                :href="link.url ?? ''"
                                v-html="link.label"
                                class="px-3 py-1.5 text-xs rounded-lg border transition-colors"
                                :class="link.active
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'text-muted-foreground border-border hover:bg-secondary hover:text-foreground'"
                                :aria-disabled="!link.url"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
