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
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-zinc-500 hover:text-violet-400 transition-colors"
                    >
                        Esqueceu a senha?
                    </Link>
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

        <p class="mt-6 text-center text-sm text-zinc-500">
            Não tem uma conta?
            <Link :href="route('register')" class="text-violet-400 hover:text-violet-300 font-medium transition-colors">
                Criar conta
            </Link>
        </p>
    </GuestLayout>
</template>
