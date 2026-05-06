<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const models = [
    {
        id:    'detecting_ai',
        label: 'Detecting-AI',
        desc:  'Classificação direta em PT-BR — mais rápido',
        badge: 'Rápido',
        badgeClass: 'text-emerald-400',
    },
    {
        id:    'bert',
        label: 'BERT Português',
        desc:  'Análise de perplexidade por sentença — mais preciso',
        badge: 'Alta precisão',
        badgeClass: 'text-primary',
    },
];

const form         = useForm({ content: '', model: 'detecting_ai' });
const dropdownOpen = ref(false);

const selectedModel = computed(() => models.find(m => m.id === form.model));
const charCount     = computed(() => form.content.length);
const isValid       = computed(() => charCount.value >= 100 && charCount.value <= 5000);

function selectModel(id) {
    form.model    = id;
    dropdownOpen.value = false;
}

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
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
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

                        <!-- Click-outside overlay -->
                        <div v-if="dropdownOpen" class="fixed inset-0 z-0" @click="dropdownOpen = false" />

                        <!-- Toolbar -->
                        <div class="flex items-center justify-between gap-3 px-4 py-3 border-t border-border">

                            <!-- Model dropdown -->
                            <div class="relative">
                                <p class="text-[10px] text-muted-foreground mb-1 leading-none">Trocar modelo de análise</p>
                                <button
                                    type="button"
                                    @click="dropdownOpen = !dropdownOpen"
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-all
                                           bg-secondary hover:bg-accent
                                           border border-border
                                           text-foreground"
                                >
                                    <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                    </svg>
                                    {{ selectedModel.label }}
                                    <span class="text-[10px] font-semibold" :class="selectedModel.badgeClass">{{ selectedModel.badge }}</span>
                                    <svg
                                        class="w-3.5 h-3.5 text-muted-foreground transition-transform"
                                        :class="dropdownOpen ? 'rotate-180' : ''"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown menu -->
                                <Transition
                                    enter-active-class="transition ease-out duration-100"
                                    enter-from-class="opacity-0 scale-95 -translate-y-1"
                                    enter-to-class="opacity-100 scale-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="opacity-100 scale-100 translate-y-0"
                                    leave-to-class="opacity-0 scale-95 -translate-y-1"
                                >
                                    <div
                                        v-if="dropdownOpen"
                                        class="absolute bottom-full left-0 mb-2 w-64 rounded-xl border border-border
                                               bg-card shadow-lg shadow-black/10 dark:shadow-black/40 z-10 overflow-hidden"
                                    >
                                        <div class="px-3 pt-3 pb-1">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Modelo de análise</p>
                                        </div>
                                        <div class="p-1.5">
                                            <button
                                                v-for="m in models"
                                                :key="m.id"
                                                type="button"
                                                @click="selectModel(m.id)"
                                                class="w-full flex items-start gap-3 px-3 py-2.5 rounded-lg text-left transition-colors group"
                                                :class="form.model === m.id
                                                    ? 'bg-primary/10'
                                                    : 'hover:bg-accent'"
                                            >
                                                <!-- Check -->
                                                <span class="mt-0.5 w-4 h-4 shrink-0 flex items-center justify-center">
                                                    <svg v-if="form.model === m.id" class="w-3.5 h-3.5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                    </svg>
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-medium text-foreground ">{{ m.label }}</span>
                                                        <span class="text-[10px] font-semibold" :class="m.badgeClass">{{ m.badge }}</span>
                                                    </div>
                                                    <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">{{ m.desc }}</p>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

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
