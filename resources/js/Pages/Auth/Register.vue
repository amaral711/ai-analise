<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Criar conta" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Criar conta</h2>
            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">Comece a detectar conteúdo gerado por IA agora mesmo</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Nome</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Seu nome completo"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-600 text-sm
                           focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.name }"
                />
                <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.name }}</p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">E-mail</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="seu@email.com"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-600 text-sm
                           focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.email }"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Senha</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-600 text-sm
                           focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.password }"
                />
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Confirmar senha</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Repita a senha"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-600 text-sm
                           focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.password_confirmation }"
                />
                <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.password_confirmation }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-medium text-sm text-white
                       bg-violet-600 hover:bg-violet-500 active:bg-violet-700 transition-colors
                       disabled:opacity-50 disabled:cursor-not-allowed mt-2"
            >
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ form.processing ? 'Criando conta...' : 'Criar conta' }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-500 dark:text-zinc-500">
            Já tem uma conta?
            <Link :href="route('login')" class="text-violet-600 dark:text-violet-400 hover:text-violet-500 dark:hover:text-violet-300 font-medium transition-colors">
                Entrar
            </Link>
        </p>
    </GuestLayout>
</template>
