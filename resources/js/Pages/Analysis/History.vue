<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    analyses: Object,
});

const classificationMap = {
    human:        { label: 'Humano',        bg: 'bg-green-100',  text: 'text-green-700'  },
    inconclusive: { label: 'Inconclusivo',  bg: 'bg-yellow-100', text: 'text-yellow-700' },
    ai:           { label: 'Gerado por IA', bg: 'bg-red-100',    text: 'text-red-700'    },
};

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function truncate(text, len = 100) {
    return text.length > len ? text.slice(0, len) + '...' : text;
}
</script>

<template>
    <Head title="Histórico de Análises" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Histórico de Análises
                </h2>
                <Link
                    :href="route('dashboard')"
                    class="text-sm text-indigo-600 hover:text-indigo-800 underline"
                >
                    Nova análise
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

                <!-- Estado vazio -->
                <div
                    v-if="analyses.data.length === 0"
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-12 text-center">
                        <p class="text-gray-500 mb-4">Nenhuma análise realizada ainda.</p>
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Fazer primeira análise
                        </Link>
                    </div>
                </div>

                <!-- Tabela -->
                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Texto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Score
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Classificação
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Data
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="item in analyses.data"
                                :key="item.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-sm text-gray-700 truncate">{{ truncate(item.text) }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ Math.round(item.ai_score * 100) }}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="[classificationMap[item.classification].bg, classificationMap[item.classification].text]"
                                    >
                                        {{ classificationMap[item.classification].label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(item.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <Link
                                        :href="route('analyses.show', item.id)"
                                        class="text-indigo-600 hover:text-indigo-900 font-medium"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Paginação -->
                    <div
                        v-if="analyses.last_page > 1"
                        class="border-t border-gray-200 px-6 py-3 flex items-center justify-between"
                    >
                        <p class="text-sm text-gray-500">
                            {{ analyses.total }} análise{{ analyses.total !== 1 ? 's' : '' }} no total
                        </p>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in analyses.links"
                                :key="link.label"
                                :href="link.url ?? ''"
                                v-html="link.label"
                                class="px-3 py-1 text-sm rounded border"
                                :class="link.active
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'text-gray-600 border-gray-300 hover:bg-gray-50'"
                                :aria-disabled="!link.url"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
