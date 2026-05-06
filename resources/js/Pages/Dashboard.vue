<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({ image: null });

const preview      = ref(null);
const selectedFile = ref(null);
const fileError    = ref('');

const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
const ALLOWED_EXT  = /\.(jpe?g|png|webp)$/i;

function onFileChange(e) {
    const file = e.target.files[0];
    fileError.value    = '';
    preview.value      = null;
    selectedFile.value = null;
    form.image         = null;

    if (!file) return;

    if (!ALLOWED_MIME.includes(file.type) && !ALLOWED_EXT.test(file.name)) {
        fileError.value = 'Formato inválido. Use JPG, PNG ou WebP.';
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        fileError.value = 'Imagem maior que 10 MB.';
        return;
    }

    selectedFile.value = file;
    form.image         = file;
    preview.value      = URL.createObjectURL(file);
}

function submit() {
    form.post(route('image-analyses.store'), { forceFormData: true });
}
</script>

<template>
    <Head title="Nova Análise" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-foreground text-base font-semibold">Análise de Imagem</h1>
                    <p class="text-muted-foreground text-sm mt-0.5">Detecte se uma imagem foi gerada por inteligência artificial</p>
                </div>
                <a :href="route('image-analyses.index')" class="text-sm text-muted-foreground hover:text-primary transition-colors flex items-center gap-1.5">
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
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary border border-border text-muted-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                        Análise síncrona
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary border border-border text-muted-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400"></span>
                        Suporta JPG, PNG, WebP
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary border border-border text-muted-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Máx. 10 MB
                    </span>
                </div>

                <!-- Card -->
                <div class="rounded-xl border border-border bg-card overflow-hidden shadow-sm">
                    <form @submit.prevent="submit" class="p-6 space-y-5">

                        <!-- Drop zone / preview -->
                        <label
                            class="flex flex-col items-center justify-center w-full rounded-xl transition-all duration-200 overflow-hidden"
                            :class="preview
                                ? 'border border-primary bg-primary/5 min-h-[280px]'
                                : 'border-2 border-dashed border-input hover:border-primary bg-secondary/40 hover:bg-primary/5 h-52'"
                        >
                            <!-- Preview -->
                            <div v-if="preview" class="relative w-full">
                                <img :src="preview" alt="Preview" class="w-full max-h-72 object-contain" />
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-zinc-900/90 to-transparent px-4 py-3">
                                    <p class="text-xs text-primary/80 font-medium truncate">{{ selectedFile.name }}</p>
                                    <p class="text-xs text-muted-foreground">Clique para trocar</p>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-secondary/60 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-muted-foreground">Clique para selecionar ou arraste a imagem</p>
                                    <p class="text-xs text-muted-foreground mt-1">JPG, PNG, WebP — máx. 10 MB</p>
                                </div>
                            </div>

                            <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.webp,image/*" @change="onFileChange" />
                        </label>

                        <!-- Errors -->
                        <p v-if="fileError" class="text-sm text-red-500 dark:text-red-400 flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            {{ fileError }}
                        </p>
                        <InputError :message="form.errors.image" />

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-1">
                            <a :href="route('image-analyses.index')" class="text-sm text-muted-foreground hover:text-primary transition-colors flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Ver histórico
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing || !selectedFile"
                                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-medium transition-all
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
                                {{ form.processing ? 'Analisando...' : 'Analisar Imagem' }}
                            </button>
                        </div>

                    </form>
                </div>

                <p class="text-center text-xs text-muted-foreground">
                    A análise é realizada de forma síncrona — o resultado aparece imediatamente após o envio.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
