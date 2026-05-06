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
            <h2 class="text-2xl font-bold text-foreground">Criar conta</h2>
            <p class="text-muted-foreground text-sm mt-1">Comece a detectar conteúdo gerado por IA agora mesmo</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-foreground mb-1.5">Nome</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Seu nome completo"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-card border border-input text-foreground placeholder:text-muted-foreground text-sm
                           focus:border-ring focus:ring-1 focus:ring-ring focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.name }"
                />
                <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.name }}</p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-foreground mb-1.5">E-mail</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="seu@email.com"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-card border border-input text-foreground placeholder:text-muted-foreground text-sm
                           focus:border-ring focus:ring-1 focus:ring-ring focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.email }"
                />
                <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-foreground mb-1.5">Senha</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-card border border-input text-foreground placeholder:text-muted-foreground text-sm
                           focus:border-ring focus:ring-1 focus:ring-ring focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.password }"
                />
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-foreground mb-1.5">Confirmar senha</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Repita a senha"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-card border border-input text-foreground placeholder:text-muted-foreground text-sm
                           focus:border-ring focus:ring-1 focus:ring-ring focus:outline-none transition-colors"
                    :class="{ 'border-red-400 dark:border-red-500': form.errors.password_confirmation }"
                />
                <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.password_confirmation }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-medium text-sm text-primary-foreground
                       bg-primary hover:bg-primary/90 active:bg-primary/80 transition-colors
                       disabled:opacity-50 disabled:cursor-not-allowed mt-2"
            >
                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ form.processing ? 'Criando conta...' : 'Criar conta' }}
            </button>
        </form>

        <div class="mt-6 flex items-center gap-3">
            <span class="flex-1 h-px bg-border"></span>
            <span class="text-xs text-muted-foreground">ou continue com</span>
            <span class="flex-1 h-px bg-border"></span>
        </div>

        <a
            :href="route('auth.google')"
            class="mt-4 w-full flex items-center justify-center gap-3 px-4 py-2.5 rounded-lg border border-input
                   bg-card hover:bg-accent text-foreground text-sm font-medium transition-colors"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Registrar com Google
        </a>

        <p class="mt-6 text-center text-sm text-muted-foreground">
            Já tem uma conta?
            <Link :href="route('login')" class="text-primary hover:text-primary font-medium transition-colors">
                Entrar
            </Link>
        </p>
    </GuestLayout>
</template>
