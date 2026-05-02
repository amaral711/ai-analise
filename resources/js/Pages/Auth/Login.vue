<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Entrar" />

        <div class="mb-8">
            <h2 class="text-2xl font-bold text-white">Bem-vindo de volta</h2>
            <p class="text-zinc-400 text-sm mt-1">Entre com sua conta para continuar</p>
        </div>

        <div v-if="status" class="mb-4 text-sm text-emerald-400 bg-emerald-400/10 border border-emerald-400/20 rounded-lg px-4 py-3">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-zinc-300 mb-1.5">E-mail</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="seu@email.com"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100 placeholder-zinc-600 text-sm
                           focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none transition-colors"
                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.email }"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-400">{{ form.errors.email }}</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-medium text-zinc-300">Senha</label>
                </div>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100 placeholder-zinc-600 text-sm
                           focus:border-violet-500 focus:ring-1 focus:ring-violet-500 focus:outline-none transition-colors"
                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.password }"
                />
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-400">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="remember"
                    v-model="form.remember"
                    type="checkbox"
                    class="w-4 h-4 rounded border-zinc-600 bg-zinc-800 text-violet-600 focus:ring-violet-500 focus:ring-offset-zinc-900"
                />
                <label for="remember" class="text-sm text-zinc-400">Lembrar de mim</label>
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
                {{ form.processing ? 'Entrando...' : 'Entrar' }}
            </button>
        </form>

        <div class="mt-6 flex items-center gap-3">
            <span class="flex-1 h-px bg-zinc-700"></span>
            <span class="text-xs text-zinc-500">ou continue com</span>
            <span class="flex-1 h-px bg-zinc-700"></span>
        </div>

        <a
            :href="route('auth.google')"
            class="mt-4 w-full flex items-center justify-center gap-3 px-4 py-2.5 rounded-lg border border-zinc-700
                   bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-sm font-medium transition-colors"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Entrar com Google
        </a>

        <p class="mt-6 text-center text-sm text-zinc-500">
            Não tem uma conta?
            <Link :href="route('register')" class="text-violet-400 hover:text-violet-300 font-medium transition-colors">
                Criar conta
            </Link>
        </p>
    </GuestLayout>
</template>
