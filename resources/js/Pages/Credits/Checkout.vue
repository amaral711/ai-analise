<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    payment: Object,
    package: Object,
});

const page = usePage();
const copied = ref(false);
const approved = ref(false);
const rejected = ref(false);

// Countdown timer
const expiresAt = new Date(props.payment.expires_at);
const remaining = ref(Math.max(0, Math.floor((expiresAt - Date.now()) / 1000)));
let timer = null;

const minutes = computed(() => String(Math.floor(remaining.value / 60)).padStart(2, '0'));
const seconds = computed(() => String(remaining.value % 60).padStart(2, '0'));
const expired = computed(() => remaining.value <= 0);

function formatPrice(price) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
}

function copy() {
    navigator.clipboard.writeText(props.payment.pix_qr_code);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 3000);
}

// WebSocket via Reverb — ouvir evento de pagamento aprovado
let channel = null;

onMounted(() => {
    timer = setInterval(() => {
        remaining.value = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
    }, 1000);

    if (window.Echo) {
        channel = window.Echo.private(`user.${page.props.auth.user.id}`)
            .listen('.payment.approved', (data) => {
                if (data.payment_id === props.payment.id) {
                    approved.value = true;
                    clearInterval(timer);
                    setTimeout(() => {
                        router.visit(route('credits.index'), {
                            replace: true,
                        });
                    }, 2500);
                }
            });
    }
});

onUnmounted(() => {
    clearInterval(timer);
    channel?.stopListening('.payment.approved');
});
</script>

<template>
    <Head title="Pagar via PIX" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-foreground">Pagamento PIX</h1>
        </template>

        <div class="px-6 lg:px-10 py-8 flex justify-center">
            <div class="w-full max-w-md space-y-6">

                <!-- Aprovado -->
                <div v-if="approved" class="text-center space-y-3 py-10">
                    <div class="w-16 h-16 rounded-full bg-green-500/10 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-foreground">Pagamento confirmado!</p>
                    <p class="text-sm text-muted-foreground">{{ payment.credits }} créditos adicionados. Redirecionando...</p>
                </div>

                <!-- Expirado -->
                <div v-else-if="expired" class="text-center space-y-3 py-10">
                    <div class="w-16 h-16 rounded-full bg-destructive/10 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-destructive" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-foreground">QR Code expirado</p>
                    <p class="text-sm text-muted-foreground">O tempo para pagamento esgotou. Gere um novo.</p>
                    <a :href="route('credits.index')" class="inline-block mt-2 text-sm text-primary hover:underline">← Voltar aos pacotes</a>
                </div>

                <!-- QR Code ativo -->
                <template v-else>
                    <!-- Resumo -->
                    <div class="bg-card border border-border rounded-2xl p-5 flex justify-between items-center">
                        <div>
                            <p class="text-sm text-muted-foreground">Pacote {{ package.name }}</p>
                            <p class="text-2xl font-bold text-foreground">{{ payment.credits }} créditos</p>
                        </div>
                        <p class="text-xl font-semibold text-primary">{{ formatPrice(payment.amount) }}</p>
                    </div>

                    <!-- Timer -->
                    <div class="text-center">
                        <p class="text-sm text-muted-foreground mb-1">QR Code expira em</p>
                        <p class="text-3xl font-mono font-bold" :class="remaining < 60 ? 'text-destructive' : 'text-foreground'">
                            {{ minutes }}:{{ seconds }}
                        </p>
                    </div>

                    <!-- QR Code Image -->
                    <div class="bg-white rounded-2xl p-4 flex items-center justify-center border border-border">
                        <img
                            v-if="payment.pix_qr_code_base64"
                            :src="'data:image/png;base64,' + payment.pix_qr_code_base64"
                            alt="QR Code PIX"
                            class="w-64 h-64"
                        />
                        <p v-else class="text-muted-foreground text-sm">QR Code indisponível</p>
                    </div>

                    <!-- Copia e cola -->
                    <div class="space-y-2">
                        <p class="text-xs text-muted-foreground font-medium uppercase tracking-wide">Pix Copia e Cola</p>
                        <div class="flex gap-2">
                            <div class="flex-1 bg-card border border-border rounded-xl px-3 py-2 text-xs text-muted-foreground font-mono truncate">
                                {{ payment.pix_qr_code }}
                            </div>
                            <button
                                @click="copy"
                                class="px-3 py-2 rounded-xl border border-border bg-card hover:bg-accent transition-colors text-sm"
                                :class="copied ? 'text-green-500 border-green-500/30' : 'text-foreground'"
                            >
                                {{ copied ? 'Copiado!' : 'Copiar' }}
                            </button>
                        </div>
                    </div>

                    <!-- Status aguardando -->
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <span class="inline-block w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        Aguardando confirmação do pagamento...
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
