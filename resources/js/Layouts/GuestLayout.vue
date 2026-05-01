<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex bg-zinc-950">

        <!-- Left panel -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-2/5 flex-col justify-between p-12 bg-zinc-900 border-r border-zinc-800 relative overflow-hidden">
            <!-- Subtle gradient orb -->
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-violet-600/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-violet-800/10 blur-3xl pointer-events-none"></div>

            <!-- Logo -->
            <Link href="/" class="flex items-center gap-3 relative z-10">
                <div class="w-9 h-9 rounded-xl bg-violet-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                </div>
                <span class="text-white font-semibold text-lg tracking-tight">DeepScan</span>
            </Link>

            <!-- Tagline -->
            <div class="relative z-10">
                <h1 class="text-3xl font-bold text-white leading-snug">
                    Detecte conteúdo<br/>
                    <span class="text-violet-400">gerado por IA</span><br/>
                    com precisão.
                </h1>
                <p class="text-zinc-400 mt-4 text-sm leading-relaxed max-w-sm">
                    Analise textos e documentos em segundos. Obtenha um score de probabilidade com explicações detalhadas sobre os fatores identificados.
                </p>

                <!-- Feature pills -->
                <div class="flex flex-wrap gap-2 mt-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-300 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Análise em tempo real
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-300 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-400"></span>
                        Score detalhado
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-300 text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        PDF, DOCX, TXT
                    </span>
                </div>
            </div>

            <p class="text-zinc-600 text-xs relative z-10">© 2025 DeepScan. Todos os direitos reservados.</p>
        </div>

        <!-- Right panel (form) -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12">
            <!-- Mobile logo -->
            <Link href="/" class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-8 h-8 rounded-lg bg-violet-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                    </svg>
                </div>
                <span class="text-white font-semibold">DeepScan</span>
            </Link>

            <div class="w-full max-w-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
