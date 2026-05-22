<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    payments: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? '');

function applyFilters() {
    router.get(route('admin.payments.index'), {
        search: search.value,
        status: status.value,
    }, { preserveState: true, replace: true });
}

let searchTimeout = null;
watch([search, status], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
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
    <Head title="Admin — Pagamentos" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-foreground">Pagamentos</h1>
                <Link :href="route('admin.dashboard')" class="text-sm text-primary hover:underline">← Admin</Link>
            </div>
        </template>

        <div class="px-6 lg:px-10 py-8 space-y-5">

            <!-- Filtros -->
            <div class="flex gap-3 flex-wrap">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Buscar usuário..."
                    class="bg-card border border-border rounded-xl px-4 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 w-64"
                />
                <select
                    v-model="status"
                    class="bg-card border border-border rounded-xl px-4 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
                    <option value="">Todos os status</option>
                    <option value="approved">Aprovado</option>
                    <option value="pending">Pendente</option>
                    <option value="rejected">Rejeitado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>

            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <div v-if="payments.data.length === 0" class="p-10 text-center text-sm text-muted-foreground">
                    Nenhum pagamento encontrado.
                </div>
                <table v-else class="w-full text-sm">
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
                            v-for="p in payments.data"
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

            <!-- Paginação -->
            <div v-if="payments.last_page > 1" class="flex justify-center gap-2">
                <Link
                    v-for="link in payments.links"
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
