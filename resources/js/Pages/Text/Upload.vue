<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form      = useForm({ content: '', model: 'bert' });
const charCount = computed(() => form.content.length);
const isValid   = computed(() => charCount.value >= 100 && charCount.value <= 5000);

function submit() {
    form.post(route('text-analyses.store'));
}
</script>

<template>
    <Head title="Análise de Texto" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-foreground text-base font-semibold">Análise de Texto</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">Detecte se um texto foi gerado por inteligência artificial</p>
                </div>
                <a :href="route('text-analyses.index')" class="text-sm text-muted-foreground hover:text-primary transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Histórico
                </a>
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-2xl mx-auto space-y-4">

                <!-- Info pills -->
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary border border-border text-muted-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Análise síncrona
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary border border-border text-muted-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Modelos treinados em PT-BR
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary border border-border text-muted-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                        100 – 5.000 caracteres
                    </span>
                </div>

                <!-- Card -->
                <div class="rounded-xl border border-border bg-card overflow-hidden shadow-sm">
                    <form @submit.prevent="submit">

                        <!-- Textarea -->
                        <textarea
                            v-model="form.content"
                            rows="10"
                            maxlength="5000"
                            placeholder="Cole aqui o texto que deseja analisar..."
                            class="w-full bg-transparent border-0 text-foreground text-sm
                                   placeholder:text-muted-foreground
                                   focus:outline-none focus:ring-0
                                   resize-none p-5"
                        ></textarea>

                        <!-- Toolbar -->
                        <div class="flex items-center justify-end gap-3 px-4 py-3 border-t border-border">

                            <!-- Right: counter + submit -->
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-xs transition-colors hidden sm:block tabular-nums"
                                    :class="charCount < 100
                                        ? 'text-amber-500'
                                        : 'text-muted-foreground'"
                                >
                                    <template v-if="charCount < 100">{{ 100 - charCount }} para o mínimo</template>
                                    <template v-else>{{ charCount.toLocaleString('pt-BR') }} / 5.000</template>
                                </span>

                                <button
                                    type="submit"
                                    :disabled="form.processing || !isValid"
                                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                                           bg-primary text-primary-foreground hover:bg-primary/90 active:bg-primary/80
                                           disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                    {{ form.processing ? 'Analisando...' : 'Analisar' }}
                                </button>
                            </div>
                        </div>

                        <!-- Errors -->
                        <div v-if="form.errors.content || form.errors.model" class="px-5 pb-4 space-y-1">
                            <InputError :message="form.errors.content" />
                            <InputError :message="form.errors.model" />
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
