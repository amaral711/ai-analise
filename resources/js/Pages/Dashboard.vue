<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({ text: '' });

const charCount = computed(() => form.text.length);
const isReady = computed(() => form.text.length >= 50);

function submit() {
    form.post(route('analyses.store'));
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Análise de Texto
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="mb-6 text-sm text-gray-600">
                            Cole ou digite um texto abaixo para verificar se ele foi gerado por inteligência artificial.
                        </p>

                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <textarea
                                    v-model="form.text"
                                    rows="12"
                                    placeholder="Cole o texto aqui... (mínimo 50 caracteres)"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                                ></textarea>

                                <div class="mt-1 flex items-center justify-between">
                                    <InputError :message="form.errors.text" />
                                    <span
                                        class="ml-auto text-xs"
                                        :class="charCount < 50 ? 'text-red-400' : 'text-gray-400'"
                                    >
                                        {{ charCount.toLocaleString() }} / 10.000
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <a
                                    :href="route('analyses.index')"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 underline"
                                >
                                    Ver histórico de análises
                                </a>

                                <PrimaryButton
                                    type="submit"
                                    :disabled="form.processing || !isReady"
                                    class="px-6"
                                >
                                    <span v-if="form.processing">Analisando...</span>
                                    <span v-else>Analisar Texto</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
