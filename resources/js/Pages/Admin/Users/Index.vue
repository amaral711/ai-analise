<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');

let searchTimeout = null;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), { search: val }, { preserveState: true, replace: true });
    }, 400);
});
</script>

<template>
    <Head title="Admin — Usuários" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-foreground">Usuários</h1>
                <Link :href="route('admin.dashboard')" class="text-sm text-primary hover:underline">← Admin</Link>
            </div>
        </template>

        <div class="px-6 lg:px-10 py-8 space-y-5">
            <input
                v-model="search"
                type="text"
                placeholder="Buscar por nome ou email..."
                class="w-full max-w-sm bg-card border border-border rounded-xl px-4 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30"
            />

            <div class="bg-card border border-border rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left">
                            <th class="px-5 py-3 font-medium text-muted-foreground">Usuário</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground text-right">Créditos</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground text-right">Análises</th>
                            <th class="px-5 py-3 font-medium text-muted-foreground text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="border-b border-border last:border-0 hover:bg-accent/50 transition-colors"
                        >
                            <td class="px-5 py-3">
                                <p class="text-foreground font-medium">{{ user.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="font-mono font-semibold" :class="user.credits > 0 ? 'text-primary' : 'text-muted-foreground'">
                                    {{ user.credits }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right text-muted-foreground font-mono">
                                {{ (user.analyses_count ?? 0) + (user.text_analyses_count ?? 0) + (user.audio_analyses_count ?? 0) }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <Link
                                    :href="route('admin.users.credits', user.id)"
                                    class="text-xs text-primary hover:underline"
                                >
                                    Gerenciar créditos
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div v-if="users.last_page > 1" class="flex justify-center gap-2">
                <Link
                    v-for="link in users.links"
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
