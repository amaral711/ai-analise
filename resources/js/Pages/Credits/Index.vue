<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    packages: Array,
});

const page = usePage();
const credits = page.props.credits;

function formatPrice(price) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
}

function pricePerCredit(pkg) {
    return formatPrice(pkg.price / pkg.credits);
}
</script>

<template>
    <Head title="Créditos" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-foreground">Créditos</h1>
        </template>

        <div class="px-6 lg:px-10 py-8 space-y-8">

            <!-- Saldo atual -->
            <div class="bg-primary/5 border border-primary/20 rounded-2xl p-6 flex items-center justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">Seu saldo atual</p>
                    <p class="text-4xl font-bold text-primary mt-1">{{ credits }}</p>
                    <p class="text-sm text-muted-foreground mt-1">créditos disponíveis</p>
                </div>
                <Link
                    :href="route('credits.history')"
                    class="text-sm text-primary hover:underline"
                >
                    Ver histórico →
                </Link>
            </div>

            <!-- Pacotes -->
            <div>
                <h2 class="text-lg font-semibold text-foreground mb-4">Comprar créditos</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                        v-for="(pkg, i) in packages"
                        :key="pkg.id"
                        class="relative border rounded-2xl p-6 flex flex-col gap-4 transition-all"
                        :class="i === 1
                            ? 'border-primary bg-primary/5 ring-1 ring-primary/30'
                            : 'border-border bg-card hover:border-primary/40'"
                    >
                        <span
                            v-if="i === 1"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-primary-foreground text-xs font-semibold px-3 py-1 rounded-full"
                        >
                            Mais popular
                        </span>

                        <div>
                            <p class="font-semibold text-foreground text-lg">{{ pkg.name }}</p>
                            <p class="text-sm text-muted-foreground mt-1">{{ pkg.description }}</p>
                        </div>

                        <div>
                            <p class="text-3xl font-bold text-foreground">{{ pkg.credits }}</p>
                            <p class="text-sm text-muted-foreground">créditos</p>
                        </div>

                        <div class="mt-auto space-y-3">
                            <p class="text-xs text-muted-foreground">{{ pricePerCredit(pkg) }} por crédito</p>
                            <Link
                                :href="route('credits.checkout', pkg.id)"
                                method="post"
                                as="button"
                                class="w-full text-center py-2.5 px-4 rounded-xl font-medium text-sm transition-colors"
                                :class="i === 1
                                    ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                    : 'bg-accent text-foreground hover:bg-primary hover:text-primary-foreground'"
                            >
                                {{ formatPrice(pkg.price) }} — Comprar via PIX
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info PIX -->
            <div class="bg-card border border-border rounded-xl p-4 flex gap-3 text-sm text-muted-foreground">
                <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p>Pagamento exclusivo via PIX. Após o pagamento confirmado, os créditos são adicionados automaticamente à sua conta em instantes.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
