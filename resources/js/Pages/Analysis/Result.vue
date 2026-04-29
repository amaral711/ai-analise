<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    analysis: Object,
});

const scorePercent = computed(() => Math.round(props.analysis.ai_score * 100));

const classification = computed(() => ({
    human: {
        label: 'Humano',
        bg: 'bg-green-100',
        text: 'text-green-800',
        bar: 'bg-green-500',
        border: 'border-green-200',
    },
    inconclusive: {
        label: 'Inconclusivo',
        bg: 'bg-yellow-100',
        text: 'text-yellow-800',
        bar: 'bg-yellow-500',
        border: 'border-yellow-200',
    },
    ai: {
        label: 'Gerado por IA',
        bg: 'bg-red-100',
        text: 'text-red-800',
        bar: 'bg-red-500',
        border: 'border-red-200',
    },
}[props.analysis.classification]));

const textPreview = computed(() =>
    props.analysis.text.length > 200
        ? props.analysis.text.slice(0, 200) + '...'
        : props.analysis.text
);
</script>

<template>
    <Head title="Resultado da Análise" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Resultado da Análise
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8 space-y-6">

                <!-- Score + Classificação -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Probabilidade de IA</p>
                                <p class="text-5xl font-bold text-gray-800">{{ scorePercent }}%</p>
                            </div>

                            <span
                                class="inline-flex items-center rounded-full px-5 py-2 text-base font-semibold border"
                                :class="[classification.bg, classification.text, classification.border]"
                            >
                                {{ classification.label }}
                            </span>
                        </div>

                        <!-- Barra de score -->
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="h-3 rounded-full transition-all duration-500"
                                :class="classification.bar"
                                :style="{ width: scorePercent + '%' }"
                            ></div>
                        </div>

                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                            <span>Humano</span>
                            <span>Inconclusivo</span>
                            <span>IA</span>
                        </div>
                    </div>
                </div>

                <!-- Explicações -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">
                            Fatores identificados
                        </h3>
                        <ul class="space-y-2">
                            <li
                                v-for="(item, i) in analysis.explanation"
                                :key="i"
                                class="flex items-start gap-2 text-sm text-gray-700"
                            >
                                <span class="mt-0.5 text-gray-400">→</span>
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Texto analisado -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">
                            Texto analisado
                        </h3>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap leading-relaxed">{{ textPreview }}</p>
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex gap-3">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Nova Análise
                    </Link>
                    <Link
                        :href="route('analyses.index')"
                        class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50"
                    >
                        Ver Histórico
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
