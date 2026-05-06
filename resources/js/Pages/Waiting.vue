<script setup>
import { computed, onMounted } from 'vue'
import { router, usePage, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePendingAnalysis } from '@/composables/usePendingAnalysis.js'

const props = defineProps({ analyses: Array })
const page = usePage()

const { analyses, setFromServer, removePending } = usePendingAnalysis()

onMounted(() => setFromServer(props.analyses))

const resultRoutes = {
    text:  'text-analyses.show',
    image: 'image-analyses.show',
    audio: 'audio-analyses.show',
}

const typeLabels = {
    text:  'Texto',
    image: 'Imagem',
    audio: 'Áudio',
}

const typeIcons = {
    text:  'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
    image: 'M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
    audio: 'M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z',
}

function goToResult(item) {
    removePending(item.id, item.type)
    router.visit(route(resultRoutes[item.type], item.id))
}

const hasAnalyzing = computed(() => analyses.value.some(a => a.status === 'analyzing'))
</script>

<template>
    <Head title="Análises em andamento" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">Análises em andamento</h2>
        </template>

        <div class="py-10 px-4 lg:px-10 max-w-2xl mx-auto">

            <!-- Empty state -->
            <div v-if="analyses.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-accent">
                    <svg class="h-7 w-7 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="text-foreground font-medium mb-1">Nenhuma análise em andamento</p>
                <p class="text-sm text-muted-foreground">Todas as análises foram concluídas.</p>
            </div>

            <!-- List -->
            <div v-else class="space-y-3">
                <p v-if="hasAnalyzing" class="text-sm text-muted-foreground mb-4">
                    Você pode navegar pelo site enquanto as análises são processadas. O widget no canto inferior direito mostrará o progresso.
                </p>

                <div
                    v-for="item in analyses"
                    :key="`${item.type}-${item.id}`"
                    class="flex items-center gap-4 rounded-xl border border-border bg-background p-4 shadow-sm"
                >
                    <!-- Type icon -->
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent">
                        <svg class="h-5 w-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="typeIcons[item.type]" />
                        </svg>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-0.5">
                            {{ typeLabels[item.type] }}
                        </p>
                        <p class="text-sm text-foreground truncate">{{ item.label || `Análise #${item.id}` }}</p>
                    </div>

                    <!-- Status -->
                    <div class="shrink-0">
                        <!-- Analyzing -->
                        <div v-if="item.status === 'analyzing'" class="flex items-center gap-2 text-muted-foreground">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                            </svg>
                            <span class="text-xs">Analisando...</span>
                        </div>

                        <!-- Completed -->
                        <button
                            v-else-if="item.status === 'completed'"
                            @click="goToResult(item)"
                            class="flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90 transition-colors"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            Ver resultado
                        </button>

                        <!-- Failed -->
                        <span v-else-if="item.status === 'failed'" class="flex items-center gap-1.5 text-xs text-red-500">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            Falhou
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
