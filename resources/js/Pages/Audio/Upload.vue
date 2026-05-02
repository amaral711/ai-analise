<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({ audio: null });

const audioSrc     = ref(null);
const selectedFile = ref(null);
const fileError    = ref('');

const ALLOWED_MIME = [
    'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/ogg; codecs=opus',
    'audio/mp4', 'audio/aac', 'audio/x-m4a',
];
const ALLOWED_EXT = /\.(mp3|wav|ogg|m4a|aac)$/i;

function onFileChange(e) {
    const file = e.target.files[0];
    fileError.value    = '';
    audioSrc.value     = null;
    selectedFile.value = null;
    form.audio         = null;

    if (!file) return;

    const mimeOk = ALLOWED_MIME.some(m => file.type.startsWith(m.split(';')[0].trim()));
    if (!mimeOk && !ALLOWED_EXT.test(file.name)) {
        fileError.value = 'Formato inválido. Use MP3, WAV, OGG ou M4A.';
        return;
    }
    if (file.size > 25 * 1024 * 1024) {
        fileError.value = 'Arquivo maior que 25 MB.';
        return;
    }

    selectedFile.value = file;
    form.audio         = file;
    audioSrc.value     = URL.createObjectURL(file);
}

function submit() {
    form.post(route('audio-analyses.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Análise de Áudio" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-zinc-100 text-base font-semibold">Análise de Áudio</h1>
                    <p class="text-zinc-500 text-sm mt-0.5">Detecte se um áudio foi gerado por inteligência artificial</p>
                </div>
                <a :href="route('audio-analyses.index')" class="text-sm text-zinc-500 dark:text-zinc-500 hover:text-violet-600 dark:hover:text-violet-400 transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Histórico
                </a>
            </div>
        </template>

        <div class="p-6 lg:p-10">
            <div class="max-w-2xl mx-auto space-y-6">

                <!-- Info pills -->
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                        Análise síncrona
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400"></span>
                        Suporta MP3, WAV, OGG, M4A
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-zinc-500 dark:text-zinc-400 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-500 dark:bg-violet-400"></span>
                        Máx. 25 MB
                    </span>
                </div>

                <!-- Card -->
                <div class="rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden shadow-sm">
                    <form @submit.prevent="submit" class="p-6 space-y-5">

                        <!-- Drop zone / preview -->
                        <label
                            class="flex flex-col items-center justify-center w-full rounded-xl transition-all duration-200 overflow-hidden"
                            :class="audioSrc
                                ? 'border border-violet-400 dark:border-violet-500 bg-violet-50/50 dark:bg-violet-500/5 p-6'
                                : 'border-2 border-dashed border-slate-300 dark:border-zinc-700 hover:border-violet-400 dark:hover:border-violet-500/60 bg-slate-50 dark:bg-zinc-800/40 hover:bg-violet-50/40 dark:hover:bg-violet-500/5 h-52'"
                        >
                            <!-- Audio preview -->
                            <div v-if="audioSrc" class="w-full space-y-3">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-9 h-9 rounded-lg bg-violet-100 dark:bg-violet-500/20 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-violet-700 dark:text-violet-300 font-medium truncate">{{ selectedFile.name }}</p>
                                        <p class="text-xs text-zinc-500">Clique para trocar</p>
                                    </div>
                                </div>
                                <audio :src="audioSrc" controls class="w-full rounded-lg" style="accent-color: #7c3aed;" />
                            </div>

                            <!-- Empty state -->
                            <div v-else class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-zinc-700/60 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-slate-400 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400">Clique para selecionar ou arraste o áudio</p>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-600 mt-1">MP3, WAV, OGG, M4A — máx. 25 MB</p>
                                </div>
                            </div>

                            <input type="file" class="hidden" accept=".mp3,.wav,.ogg,.m4a,.aac,audio/*" @change="onFileChange" />
                        </label>

                        <!-- Errors -->
                        <p v-if="fileError" class="text-sm text-red-500 dark:text-red-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            {{ fileError }}
                        </p>
                        <InputError :message="form.errors.audio" />

                        <!-- Footer -->
                        <div class="flex justify-end pt-1">
                            <button
                                type="submit"
                                :disabled="form.processing || !selectedFile"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-medium transition-all
                                       bg-violet-600 text-white hover:bg-violet-500 active:bg-violet-700
                                       disabled:opacity-40 disabled:cursor-not-allowed"
                            >
                                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                {{ form.processing ? 'Analisando...' : 'Analisar Áudio' }}
                            </button>
                        </div>

                    </form>
                </div>

                <p class="text-center text-xs text-zinc-400 dark:text-zinc-600">
                    A análise é realizada de forma síncrona — o resultado aparece imediatamente após o envio.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
