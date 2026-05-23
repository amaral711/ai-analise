<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

function printResult() {
    window.print();
}

const props = defineProps({
    analysis: Object,
});

const scorePercent = computed(() => Math.round(props.analysis.ai_score * 100));

const classificationConfig = {
    human: {
        label: 'Humano',
        sublabel: 'Texto provavelmente escrito por um ser humano',
        badgeBg: 'bg-emerald-500/15',
        badgeText: 'text-emerald-400',
        badgeBorder: 'border-emerald-500/25',
        ringColor: '#10b981',
        dotColor: 'bg-emerald-400',
    },
    inconclusive: {
        label: 'Inconclusivo',
        sublabel: 'Resultado não conclusivo para classificação',
        badgeBg: 'bg-amber-500/15',
        badgeText: 'text-amber-400',
        badgeBorder: 'border-amber-500/25',
        ringColor: '#f59e0b',
        dotColor: 'bg-amber-400',
    },
    ai: {
        label: 'Gerado por IA',
        sublabel: 'Alta probabilidade de texto gerado por modelo de linguagem',
        badgeBg: 'bg-red-500/15',
        badgeText: 'text-red-400',
        badgeBorder: 'border-red-500/25',
        ringColor: '#ef4444',
        dotColor: 'bg-red-400',
    },
};

const cfg = computed(() => classificationConfig[props.analysis.classification] ?? classificationConfig.inconclusive);

const RADIUS = 52;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;
const strokeDashoffset = computed(() => CIRCUMFERENCE * (1 - props.analysis.ai_score));

const textPreview = computed(() => props.analysis.content ?? '');

const modelConfig = {
    bert:         { label: 'BERT Português',          cls: 'bg-primary/15 text-primary border-primary/25' },
    detecting_ai: { label: 'Detecting-AI',            cls: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/25' },
    claude:       { label: 'Claude Haiku (Premium)',  cls: 'bg-amber-500/15 text-amber-400 border-amber-500/25' },
};

const modelLabel     = computed(() => (modelConfig[props.analysis.model] ?? modelConfig.detecting_ai).label);
const modelBadgeClass = computed(() => (modelConfig[props.analysis.model] ?? modelConfig.detecting_ai).cls);
</script>

<template>
    <Head title="Resultado da Análise de Texto" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-foreground text-base font-semibold">Resultado da Análise</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">Análise concluída com sucesso</p>
                </div>
                <div class="flex items-center gap-2 print:hidden">
                    <button
                        @click="printResult"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-secondary transition-colors border border-border"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                        Exportar PDF
                    </button>
                    <Link
                        :href="route('text-analyses.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-foreground hover:bg-secondary transition-colors border border-border"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                        Histórico
                    </Link>
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nova análise
                    </Link>
                </div>
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-3xl mx-auto space-y-5">

                <!-- Score card -->
                <div class="rounded-xl border border-border bg-card p-6 lg:p-8">
                    <div class="flex flex-col sm:flex-row items-center gap-8">

                        <!-- Gauge -->
                        <div class="relative shrink-0">
                            <svg width="140" height="140" class="-rotate-90">
                                <circle cx="70" cy="70" :r="RADIUS" fill="none" stroke="#27272a" stroke-width="10" />
                                <circle
                                    cx="70" cy="70" :r="RADIUS"
                                    fill="none"
                                    :stroke="cfg.ringColor"
                                    stroke-width="10"
                                    stroke-linecap="round"
                                    :stroke-dasharray="CIRCUMFERENCE"
                                    :stroke-dashoffset="strokeDashoffset"
                                    style="transition: stroke-dashoffset 0.8s ease"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-bold text-white tabular-nums">{{ scorePercent }}<span class="text-2xl text-muted-foreground">%</span></span>
                                <span class="text-xs text-muted-foreground mt-0.5">prob. IA</span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 text-center sm:text-left">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold border"
                                    :class="[cfg.badgeBg, cfg.badgeText, cfg.badgeBorder]"
                                >
                                    <span class="w-2 h-2 rounded-full" :class="cfg.dotColor"></span>
                                    {{ cfg.label }}
                                </span>
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium border"
                                    :class="modelBadgeClass"
                                >{{ modelLabel }}</span>
                            </div>
                            <p class="text-muted-foreground text-sm mt-3">{{ cfg.sublabel }}</p>

                            <!-- Scale bar -->
                            <div class="mt-4">
                                <div class="relative h-2 w-full rounded-full overflow-hidden bg-secondary">
                                    <div class="absolute inset-y-0 left-0 w-2/5 bg-emerald-500/40 rounded-full"></div>
                                    <div class="absolute inset-y-0 left-[40%] w-[30%] bg-amber-500/40 rounded-full"></div>
                                    <div class="absolute inset-y-0 left-[70%] right-0 bg-red-500/40 rounded-full"></div>
                                    <div
                                        class="absolute top-1/2 -translate-y-1/2 w-3 h-3 rounded-full border-2 border-background shadow-lg transition-all duration-700"
                                        :class="cfg.dotColor"
                                        :style="{ left: `calc(${scorePercent}% - 6px)` }"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-xs text-muted-foreground mt-1.5">
                                    <span>Humano</span>
                                    <span>Inconclusivo</span>
                                    <span>IA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Factors -->
                <div class="rounded-xl border border-border bg-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-border flex items-center gap-2">
                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <h2 class="text-foreground/80 text-sm font-medium">Fatores identificados</h2>
                    </div>
                    <ul class="divide-y divide-border/50">
                        <li
                            v-for="(item, i) in analysis.explanation"
                            :key="i"
                            class="flex items-start gap-3 px-6 py-3.5 hover:bg-accent/30 transition-colors"
                        >
                            <span class="shrink-0 w-5 h-5 rounded-full bg-secondary flex items-center justify-center mt-0.5">
                                <span class="text-muted-foreground text-xs font-medium">{{ i + 1 }}</span>
                            </span>
                            <span class="text-foreground/80 text-sm leading-relaxed">{{ item }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Text preview -->
                <div class="rounded-xl border border-border bg-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-border flex items-center gap-2">
                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <h2 class="text-foreground/80 text-sm font-medium">Texto analisado</h2>
                        <span class="ml-auto text-xs text-muted-foreground">{{ analysis.content?.length?.toLocaleString('pt-BR') }} caracteres</span>
                    </div>
                    <div class="p-6 bg-background">
                        <p class="text-muted-foreground text-sm leading-relaxed whitespace-pre-wrap">{{ textPreview }}</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    /* Ocultar sidebar, topbar, header e widget flutuante */
    aside,
    [data-layout-header],
    [data-content] > div > *:not(.print-area) { }

    aside { display: none !important; }

    /* Topbar */
    .flex.items-center.gap-4.px-4.py-3.border-b { display: none !important; }

    /* Header da página */
    header[data-layout-header] { display: none !important; }

    /* Widget flutuante */
    [class*="AnalysisProgress"] { display: none !important; }

    /* Layout: remover flex do shell, mostrar só o conteúdo */
    .flex.h-screen { display: block !important; }
    main[data-content] { overflow: visible !important; }

    /* Fundo branco, texto preto */
    body, .bg-background, .bg-card { background: #fff !important; color: #111 !important; }
    .text-foreground, .text-foreground\/80, .text-foreground\/90 { color: #111 !important; }
    .text-muted-foreground { color: #555 !important; }

    /* Bordas visíveis sem sombra */
    .border, .border-border { border-color: #e5e7eb !important; }
    .shadow-sm, .shadow-lg { box-shadow: none !important; }

    /* Garantir que círculo SVG apareça com cor certa */
    circle { print-color-adjust: exact; -webkit-print-color-adjust: exact; }

    /* Texto analisado: evitar quebra de página */
    .whitespace-pre-wrap { white-space: pre-wrap; }

    /* Margem da página */
    @page { margin: 20mm; }
}
</style>
