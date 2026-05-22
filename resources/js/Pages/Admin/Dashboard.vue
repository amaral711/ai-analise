<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recentPayments: Array,
});

function formatPrice(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(date));
}

const statusColors = {
    approved:  'text-green-500 bg-green-500/10',
    pending:   'text-yellow-500 bg-yellow-500/10',
    rejected:  'text-red-500 bg-red-500/10',
    cancelled: 'text-muted-foreground bg-accent',
};
</script>

<template>
    <Head title="Admin — Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-foreground">Painel Administrativo</h1>
                <div class="flex gap-3 text-sm">
                    <Link :href="route('admin.users.index')" class="text-primary hover:underline">Usuários</Link>
                    <Link :href="route('admin.payments.index')" class="text-primary hover:underline">Pagamentos</Link>
                </div>
            </div>
        </template>

        <div class="px-6 lg:px-10 py-8 space-y-8">

            <!-- Cards de métricas -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-card border border-border rounded-2xl p-5 col-span-2 lg:col-span-1">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Usuários</p>
                    <p class="text-3xl font-bold text-foreground mt-1">{{ stats.total_users }}</p>
                </div>
                <div class="bg-card border border-border rounded-2xl p-5">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Receita Total</p>
                    <p class="text-2xl font-bold text-green-500 mt-1">{{ formatPrice(stats.total_revenue) }}</p>
                </div>
                <div class="bg-card border border-border rounded-2xl p-5">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Pagamentos Aprovados</p>
                    <p class="text-3xl font-bold text-foreground mt-1">{{ stats.total_approved_payments }}</p>
                </div>
                <div class="bg-card border border-border rounded-2xl p-5">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Aguardando</p>
                    <p class="text-3xl font-bold text-yellow-500 mt-1">{{ stats.total_pending_payments }}</p>
                </div>
                <div class="bg-card border border-border rounded-2xl p-5">
                    <p class="text-xs text-muted-foreground uppercase tracking-wide">Créditos em Circulação</p>
                    <p class="text-3xl font-bold text-primary mt-1">{{ stats.credits_in_circulation }}</p>
                </div>
            </div>

            <!-- Pagamentos recentes -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-foreground">Pagamentos recentes</h2>
                    <Link :href="route('admin.payments.index')" class="text-sm text-primary hover:underline">Ver todos →</Link>
                </div>

                <div class="bg-card border border-border rounded-2xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-5 py-3 font-medium text-muted-foreground">Usuário</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground">Pacote</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground text-right">Créditos</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground text-right">Valor</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground">Status</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground text-right">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="p in recentPayments"
                                :key="p.id"
                                class="border-b border-border last:border-0 hover:bg-accent/50 transition-colors"
                            >
                                <td class="px-5 py-3">
                                    <p class="text-foreground font-medium">{{ p.user.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ p.user.email }}</p>
                                </td>
                                <td class="px-5 py-3 text-muted-foreground">{{ p.package ?? '—' }}</td>
                                <td class="px-5 py-3 text-right font-mono">{{ p.credits }}</td>
                                <td class="px-5 py-3 text-right font-mono">{{ formatPrice(p.amount) }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" :class="statusColors[p.status]">
                                        {{ p.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-muted-foreground text-xs">{{ formatDate(p.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
