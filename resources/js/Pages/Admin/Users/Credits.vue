<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    targetUser: Object,
    transactions: Array,
});

const form = useForm({
    credits: '',
    reason: '',
});

const page = usePage();
const success = page.props.flash?.success ?? null;

function submit() {
    form.post(route('admin.users.credits.store', props.targetUser.id), {
        onSuccess: () => form.reset(),
    });
}

const typeLabels = {
    freemium:          { label: 'Bônus inicial',        color: 'text-blue-500' },
    purchase:          { label: 'Compra',                color: 'text-green-500' },
    analysis_consumed: { label: 'Análise',               color: 'text-orange-500' },
    manual_grant:      { label: 'Concessão manual',      color: 'text-purple-500' },
    manual_deduct:     { label: 'Remoção manual',        color: 'text-red-500' },
};

function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    }).format(new Date(date));
}
</script>

<template>
    <Head :title="`Admin — Créditos de ${targetUser.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-foreground">{{ targetUser.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ targetUser.email }}</p>
                </div>
                <Link :href="route('admin.users.index')" class="text-sm text-primary hover:underline">← Usuários</Link>
            </div>
        </template>

        <div class="px-6 lg:px-10 py-8 space-y-8">

            <!-- Saldo -->
            <div class="bg-primary/5 border border-primary/20 rounded-2xl p-5">
                <p class="text-sm text-muted-foreground">Saldo atual</p>
                <p class="text-4xl font-bold text-primary mt-1">{{ targetUser.credits }}</p>
                <p class="text-sm text-muted-foreground">créditos</p>
            </div>

            <!-- Formulário -->
            <div class="bg-card border border-border rounded-2xl p-6 space-y-4 max-w-md">
                <h2 class="font-semibold text-foreground">Conceder / Remover créditos</h2>

                <div v-if="success" class="text-sm text-green-500 bg-green-500/10 rounded-lg px-3 py-2">
                    {{ success }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="text-xs text-muted-foreground font-medium block mb-1">
                            Quantidade (positivo = adicionar, negativo = remover)
                        </label>
                        <input
                            v-model.number="form.credits"
                            type="number"
                            placeholder="Ex: 10 ou -5"
                            class="w-full bg-background border border-border rounded-xl px-4 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                        <p v-if="form.errors.credits" class="text-xs text-destructive mt-1">{{ form.errors.credits }}</p>
                    </div>

                    <div>
                        <label class="text-xs text-muted-foreground font-medium block mb-1">Justificativa</label>
                        <input
                            v-model="form.reason"
                            type="text"
                            placeholder="Ex: Compensação por falha na análise"
                            class="w-full bg-background border border-border rounded-xl px-4 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30"
                        />
                        <p v-if="form.errors.reason" class="text-xs text-destructive mt-1">{{ form.errors.reason }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-primary text-primary-foreground py-2.5 rounded-xl text-sm font-medium hover:bg-primary/90 disabled:opacity-60 transition-colors"
                    >
                        {{ form.processing ? 'Salvando...' : 'Salvar' }}
                    </button>
                </form>
            </div>

            <!-- Histórico -->
            <div>
                <h2 class="text-base font-semibold text-foreground mb-4">Últimas transações</h2>
                <div class="bg-card border border-border rounded-2xl overflow-hidden">
                    <div v-if="transactions.length === 0" class="p-8 text-center text-sm text-muted-foreground">
                        Nenhuma transação registrada.
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-5 py-3 font-medium text-muted-foreground">Tipo</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground">Descrição</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground text-right">Δ Créditos</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground text-right">Saldo</th>
                                <th class="px-5 py-3 font-medium text-muted-foreground text-right">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="tx in transactions"
                                :key="tx.id"
                                class="border-b border-border last:border-0"
                            >
                                <td class="px-5 py-3">
                                    <span class="text-xs font-medium" :class="typeLabels[tx.type]?.color">
                                        {{ typeLabels[tx.type]?.label ?? tx.type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-muted-foreground">{{ tx.description }}</td>
                                <td class="px-5 py-3 text-right font-mono font-semibold"
                                    :class="tx.credits_delta > 0 ? 'text-green-500' : 'text-red-500'">
                                    {{ tx.credits_delta > 0 ? '+' : '' }}{{ tx.credits_delta }}
                                </td>
                                <td class="px-5 py-3 text-right font-mono text-muted-foreground">{{ tx.balance_after }}</td>
                                <td class="px-5 py-3 text-right text-muted-foreground text-xs">{{ formatDate(tx.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
