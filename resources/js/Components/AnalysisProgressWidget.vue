<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { usePendingAnalysis } from '@/composables/usePendingAnalysis.js'

const page = usePage()
const { analyses, setFromServer, markCompleted, markFailed, removePending } = usePendingAnalysis()

let channel = null

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

const visible = computed(() => analyses.value.length > 0)
const analyzingCount = computed(() => analyses.value.filter(a => a.status === 'analyzing').length)

async function fetchPending() {
    try {
        const res = await window.axios.get(route('analyses.pending'))
        setFromServer(res.data)
    } catch {
        // silently ignore — widget is non-critical
    }
}

function subscribe() {
    const userId = page.props.auth?.user?.id
    if (!userId || !window.Echo) return

    channel = window.Echo.private(`App.User.${userId}`)
        .listen('.analysis.completed', (e) => markCompleted(e.id, e.type))
        .listen('.analysis.failed',    (e) => markFailed(e.id, e.type))
}

function unsubscribe() {
    if (channel) {
        channel.stopListening('.analysis.completed')
        channel.stopListening('.analysis.failed')
        channel = null
    }
}

function goToResult(item) {
    removePending(item.id, item.type)
    router.visit(route(resultRoutes[item.type], item.id))
}

function dismiss(item) {
    removePending(item.id, item.type)
}

onMounted(async () => {
    await fetchPending()
    subscribe()
})

onUnmounted(unsubscribe)
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <div
            v-if="visible"
            class="fixed bottom-5 right-5 z-50 w-80 rounded-xl border border-border bg-background/95 shadow-lg backdrop-blur-sm"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 pt-3 pb-2 border-b border-border">
                <div class="flex items-center gap-2">
                    <svg
                        v-if="analyzingCount > 0"
                        class="h-3.5 w-3.5 animate-spin text-primary"
                        fill="none" viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                    </svg>
                    <span class="text-xs font-semibold text-foreground">
                        {{ analyzingCount > 0 ? `${analyzingCount} análise${analyzingCount > 1 ? 's' : ''} em andamento` : 'Análises' }}
                    </span>
                </div>
                <a
                    :href="route('analyses.waiting')"
                    class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                >
                    Ver tela
                </a>
            </div>

            <!-- Items -->
            <ul class="divide-y divide-border max-h-60 overflow-y-auto">
                <li
                    v-for="item in analyses"
                    :key="`${item.type}-${item.id}`"
                    class="flex items-center gap-3 px-4 py-3"
                >
                    <!-- Status icon -->
                    <div class="shrink-0">
                        <svg
                            v-if="item.status === 'analyzing'"
                            class="h-4 w-4 animate-spin text-muted-foreground"
                            fill="none" viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                        <div v-else-if="item.status === 'completed'" class="flex h-4 w-4 items-center justify-center rounded-full bg-green-500/15">
                            <svg class="h-2.5 w-2.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <div v-else-if="item.status === 'failed'" class="flex h-4 w-4 items-center justify-center rounded-full bg-red-500/15">
                            <svg class="h-2.5 w-2.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>

                    <!-- Label -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-muted-foreground">{{ typeLabels[item.type] }}</p>
                        <p class="text-xs text-foreground truncate">
                            {{ item.label || `#${item.id}` }}
                        </p>
                    </div>

                    <!-- Action -->
                    <div class="shrink-0">
                        <span v-if="item.status === 'analyzing'" class="text-xs text-muted-foreground">
                            Aguardando...
                        </span>
                        <button
                            v-else-if="item.status === 'completed'"
                            @click="goToResult(item)"
                            class="rounded-md bg-primary px-2.5 py-1 text-xs font-medium text-primary-foreground hover:bg-primary/90 transition-colors"
                        >
                            Ver resultado
                        </button>
                        <button
                            v-else-if="item.status === 'failed'"
                            @click="dismiss(item)"
                            class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                        >
                            Dispensar
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </Transition>
</template>
