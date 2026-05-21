<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex bg-background relative overflow-hidden">

        <!-- Background orbs -->
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>

        <!-- Left panel -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-2/5 flex-col justify-between p-12 bg-card/60 border-r border-border relative overflow-hidden backdrop-blur-sm">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>

            <!-- Logo -->
            <Link href="/" class="flex items-center gap-3 relative z-10">
                <img src="/logo.jpeg" alt="DeepScan" class="w-9 h-9 rounded-xl object-cover" />
                <span class="text-foreground font-semibold text-lg tracking-tight">DeepScan</span>
            </Link>

            <!-- Tagline -->
            <div class="relative z-10">
                <h1 class="text-3xl font-bold text-foreground leading-snug">
                    Detecte conteúdo<br/>
                    <span class="text-primary">gerado por IA</span><br/>
                    com precisão.
                </h1>
                <p class="text-muted-foreground mt-4 text-sm leading-relaxed max-w-sm">
                    Analise textos, imagens e áudios em segundos. Obtenha um score de probabilidade com explicações detalhadas sobre os fatores identificados.
                </p>

                <!-- Feature pills -->
                <div class="flex flex-wrap gap-2 mt-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-secondary border border-border text-secondary-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                        Análise em tempo real
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-secondary border border-border text-secondary-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Texto & Imagem
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-secondary border border-border text-secondary-foreground text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        Áudio & Voz
                    </span>
                </div>
            </div>

            <p class="text-muted-foreground text-xs relative z-10">© 2026 DeepScan. Todos os direitos reservados.</p>
        </div>

        <!-- Right panel (form) -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12">
            <!-- Mobile logo -->
            <Link href="/" class="flex flex-col items-center gap-2 mb-8 lg:hidden">
                <img src="/logo.jpeg" alt="DeepScan" class="w-20 h-20 rounded-2xl object-cover" />
                <span class="text-foreground font-semibold text-lg">DeepScan</span>
            </Link>

            <div class="w-full max-w-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
