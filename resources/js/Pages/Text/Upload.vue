<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({ content: '' });

const charCount = computed(() => form.content.length);
const charLeft  = computed(() => 5000 - charCount.value);
const isValid   = computed(() => charCount.value >= 100 && charCount.value <= 5000);

function submit() {
    form.post(route('text-analyses.store'));
}
</script>

<template>
    <Head title="Análise de Texto" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-zinc-100 text-base font-semibold">Análise de Texto</h1>
                <p class="text-zinc-500 text-sm mt-0.5">Detecte se um texto foi gerado por inteligência artificial</p>
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-2xl mx-auto space-y-6">

                <!-- Info pills -->
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-400 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        Análise síncrona
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-400 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        Modelo treinado em PT-BR
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-400 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>
                        100 – 5.000 caracteres
                    </span>
                </div>

                <!-- Card -->
                <div class="rounded-xl border border-zinc-800 bg-zinc-900 overflow-hidden">
                    <form @submit.prevent="submit" class="p-6 space-y-5">

                        <!-- Textarea -->
                        <div class="space-y-2">
                            <div class="relative">
                                <textarea
                                    v-model="form.content"
                                    rows="10"
                                    maxlength="5000"
                                    placeholder="Cole aqui o texto que deseja analisar..."
                                    class="w-full rounded-xl bg-zinc-950 border border-zinc-700 text-zinc-200 text-sm placeholder-zinc-600
                                           focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/60
                                           resize-none p-4 transition-colors"
                                    :class="form.errors.content ? 'border-red-500/60' : ''"
                                ></textarea>
                            </div>

                            <!-- Character counter -->
                            <div class="flex items-center justify-between px-1">
                                <span
                                    class="text-xs transition-colors"
                                    :class="charCount < 100
                                        ? 'text-amber-500'
                                        : charLeft < 200
                                            ? 'text-amber-400'
                                            : 'text-zinc-600'"
                                >
                                    <template v-if="charCount < 100">
                                        {{ 100 - charCount }} caracteres faltando para o mínimo
                                    </template>
                                    <template v-else>
                                        {{ charCount.toLocaleString('pt-BR') }} / 5.000 caracteres
                                    </template>
                                </span>
                                <span class="text-xs text-zinc-700">{{ charLeft.toLocaleString('pt-BR') }} restantes</span>
                            </div>
                        </div>

                        <!-- Errors -->
                        <InputError :message="form.errors.content" />

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-1">
                            <a :href="route('text-analyses.index')" class="text-sm text-zinc-500 hover:text-blue-400 transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Ver histórico
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing || !isValid"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-medium transition-all
                                       bg-blue-600 text-white hover:bg-blue-500 active:bg-blue-700
                                       disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                {{ form.processing ? 'Analisando...' : 'Analisar Texto' }}
                            </button>
                        </div>

                    </form>
                </div>

                <p class="text-center text-xs text-zinc-600">
                    A análise é realizada de forma síncrona — o resultado aparece imediatamente após o envio.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
