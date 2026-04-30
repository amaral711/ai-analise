<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    analysis: Object,
    imageUrl: String,
});

const scorePercent = computed(() => Math.round(props.analysis.ai_score * 100));

const classificationConfig = {
    human: {
        label: 'Humano',
        sublabel: 'Imagem provavelmente gerada por humano',
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
        sublabel: 'Alta probabilidade de geração por IA',
        badgeBg: 'bg-red-500/15',
        badgeText: 'text-red-400',
        badgeBorder: 'border-red-500/25',
        ringColor: '#ef4444',
        dotColor: 'bg-red-400',
    },
};

const cfg = computed(() => classificationConfig[props.analysis.classification]);

const RADIUS = 52;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;
const strokeDashoffset = computed(() => CIRCUMFERENCE * (1 - props.analysis.ai_score));
</script>

<template>
    <Head title="Resultado da Análise" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-zinc-100 text-base font-semibold">Resultado da Análise</h1>
                    <p class="text-zinc-500 text-sm mt-0.5">Análise concluída com sucesso</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('analyses.index')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 transition-colors border border-zinc-700"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                        Histórico
                    </Link>
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
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-3xl mx-auto space-y-5">

                <!-- Score card -->
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 p-6 lg:p-8">
                    <div class="flex flex-col sm:flex-row items-center gap-8">

                        <!-- Gauge -->
                        <div class="relative flex-shrink-0">
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
                                <span class="text-4xl font-bold text-white tabular-nums">{{ scorePercent }}<span class="text-2xl text-zinc-400">%</span></span>
                                <span class="text-xs text-zinc-500 mt-0.5">prob. IA</span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 text-center sm:text-left">
                            <span
                                class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold border"
                                :class="[cfg.badgeBg, cfg.badgeText, cfg.badgeBorder]"
                            >
                                <span class="w-2 h-2 rounded-full" :class="cfg.dotColor"></span>
                                {{ cfg.label }}
                            </span>
                            <p class="text-zinc-400 text-sm mt-3">{{ cfg.sublabel }}</p>

                            <!-- Scale bar -->
                            <div class="mt-4">
                                <div class="relative h-2 w-full rounded-full overflow-hidden bg-zinc-800">
                                    <div class="absolute inset-y-0 left-0 w-2/5 bg-emerald-500/40 rounded-full"></div>
                                    <div class="absolute inset-y-0 left-[40%] w-[30%] bg-amber-500/40 rounded-full"></div>
                                    <div class="absolute inset-y-0 left-[70%] right-0 bg-red-500/40 rounded-full"></div>
                                    <div
                                        class="absolute top-1/2 -translate-y-1/2 w-3 h-3 rounded-full border-2 border-zinc-900 shadow-lg transition-all duration-700"
                                        :class="cfg.dotColor"
                                        :style="{ left: `calc(${scorePercent}% - 6px)` }"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-xs text-zinc-600 mt-1.5">
                                    <span>Humano</span>
                                    <span>Inconclusivo</span>
                                    <span>IA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Factors -->
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <h2 class="text-zinc-300 text-sm font-medium">Fatores identificados</h2>
                    </div>
                    <ul class="divide-y divide-zinc-800/50">
                        <li
                            v-for="(item, i) in analysis.explanation"
                            :key="i"
                            class="flex items-start gap-3 px-6 py-3.5 hover:bg-zinc-800/30 transition-colors"
                        >
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-zinc-800 flex items-center justify-center mt-0.5">
                                <span class="text-zinc-400 text-xs font-medium">{{ i + 1 }}</span>
                            </span>
                            <span class="text-zinc-300 text-sm leading-relaxed">{{ item }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Image preview -->
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        <h2 class="text-zinc-300 text-sm font-medium">Imagem analisada</h2>
                        <span class="ml-auto text-xs text-zinc-600">{{ analysis.text }}</span>
                    </div>
                    <div class="p-4 flex justify-center bg-zinc-950">
                        <img
                            v-if="imageUrl"
                            :src="imageUrl"
                            :alt="analysis.text"
                            class="max-h-96 max-w-full object-contain rounded-lg"
                        />
                        <p v-else class="text-zinc-600 text-sm py-8">Imagem não disponível</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
