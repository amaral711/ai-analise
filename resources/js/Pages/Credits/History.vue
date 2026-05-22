<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    transactions: Object,
});

const typeLabels = {
    freemium:          { label: 'Bônus inicial',        color: 'text-blue-500',  bg: 'bg-blue-500/10' },
    purchase:          { label: 'Compra',                color: 'text-green-500', bg: 'bg-green-500/10' },
    analysis_consumed: { label: 'Análise realizada',     color: 'text-orange-500', bg: 'bg-orange-500/10' },
    manual_grant:      { label: 'Concessão manual',      color: 'text-purple-500', bg: 'bg-purple-500/10' },
    manual_deduct:     { label: 'Remoção manual',        color: 'text-red-500',   bg: 'bg-red-500/10' },
};

function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(date));
}
</script>

<template>
    <Head title="Histórico de Créditos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-foreground">Histórico de Créditos</h1>
                <Link :href="route('credits.index')" class="text-sm text-primary hover:underline">
                    Comprar créditos →
                </Link>
            </div>
        </template>

        <div class="px-6 lg:px-10 py-8">
            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <div v-if="transactions.data.length === 0" class="p-10 text-center text-muted-foreground text-sm">
                    Nenhuma transação encontrada.
                </div>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left">
                            <th class="px-5 py-3 font-medium text-muted-foreground">Tipo</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground">Descrição</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground text-right">Créditos</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground text-right">Saldo</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground text-right">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="tx in transactions.data"
                            :key="tx.id"
                            class="border-b border-border last:border-0 hover:bg-accent/50 transition-colors"
                        >
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                    :class="[typeLabels[tx.type]?.bg, typeLabels[tx.type]?.color]"
                                >
                                    {{ typeLabels[tx.type]?.label ?? tx.type }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-foreground">{{ tx.description }}</td>
                            <td class="px-5 py-3 text-right font-mono font-semibold"
                                :class="tx.credits_delta > 0 ? 'text-green-500' : 'text-red-500'"
                            >
                                {{ tx.credits_delta > 0 ? '+' : '' }}{{ tx.credits_delta }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-muted-foreground">{{ tx.balance_after }}</td>
                            <td class="px-5 py-3 text-right text-muted-foreground whitespace-nowrap">{{ formatDate(tx.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div v-if="transactions.last_page > 1" class="mt-4 flex justify-center gap-2">
                <Link
                    v-for="link in transactions.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    v-html="link.label"
                    class="px-3 py-1 rounded-lg text-sm border border-border transition-colors"
                    :class="link.active
                        ? 'bg-primary text-primary-foreground border-primary'
                        : link.url ? 'hover:bg-accent text-foreground' : 'text-muted-foreground cursor-default'"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
