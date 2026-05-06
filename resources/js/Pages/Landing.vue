<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="DeepScan — Detector de conteúdo gerado por IA" />

    <div class="min-h-screen bg-background relative overflow-hidden">

        <!-- Background orbs -->
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] rounded-full bg-primary/5 blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] rounded-full bg-primary/3 blur-3xl pointer-events-none"></div>

        <!-- Navbar -->
        <nav class="relative z-10 flex items-center justify-between px-6 sm:px-10 py-4 border-b border-border/60 bg-background/60 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <img src="/logo.jpeg" alt="DeepScan" class="w-9 h-9 rounded-xl object-cover" />
                <span class="text-foreground font-bold text-lg tracking-tight">DeepScan</span>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('login')"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent transition-colors"
                >
                    Entrar
                </Link>
                <Link
                    :href="route('register')"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary/90 transition-colors"
                >
                    Criar conta
                </Link>
            </div>
        </nav>

        <!-- Hero -->
        <section class="relative z-10 flex flex-col items-center text-center px-6 pt-20 pb-16 sm:pt-28 sm:pb-24">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary/5 border border-primary/20 text-primary text-xs font-medium mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                Análise em tempo real com IA
            </span>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-foreground leading-tight max-w-3xl">
                Detecte conteúdo
                <span class="text-primary"> gerado por IA</span>
                <br/>com precisão.
            </h1>

            <p class="mt-6 text-lg text-muted-foreground max-w-xl leading-relaxed">
                Analise textos, imagens e áudios em segundos. Saiba com segurança se o conteúdo foi criado por um humano ou por inteligência artificial.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                <Link
                    :href="route('register')"
                    class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl text-base font-semibold text-primary-foreground bg-primary hover:bg-primary/90 active:bg-primary/80 transition-colors shadow-lg shadow-primary/20"
                >
                    Começar gratuitamente
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </Link>
                <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl text-base font-semibold text-foreground bg-card border border-border hover:bg-accent transition-colors"
                >
                    Já tenho conta
                </Link>
            </div>
        </section>

        <!-- Features -->
        <section class="relative z-10 px-6 sm:px-10 pb-20">
            <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Texto -->
                <div class="group p-6 rounded-2xl bg-card border border-border hover:border-primary/30 transition-colors">
                    <div class="w-11 h-11 rounded-xl bg-primary/5 flex items-center justify-center mb-4 group-hover:bg-primary/10 transition-colors">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <h3 class="text-foreground font-semibold mb-2">Análise de Texto</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        Cole qualquer texto e receba um score de probabilidade com explicações detalhadas sobre os padrões identificados.
                    </p>
                </div>

                <!-- Imagem -->
                <div class="group p-6 rounded-2xl bg-card border border-border hover:border-primary/30 transition-colors">
                    <div class="w-11 h-11 rounded-xl bg-primary/5 flex items-center justify-center mb-4 group-hover:bg-primary/10 transition-colors">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-foreground font-semibold mb-2">Análise de Imagem</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        Faça upload de JPG, PNG ou WebP e identifique se a imagem foi gerada por ferramentas como DALL-E, Midjourney ou Stable Diffusion.
                    </p>
                </div>

                <!-- Áudio -->
                <div class="group p-6 rounded-2xl bg-card border border-border hover:border-primary/30 transition-colors">
                    <div class="w-11 h-11 rounded-xl bg-primary/5 flex items-center justify-center mb-4 group-hover:bg-primary/10 transition-colors">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
                        </svg>
                    </div>
                    <h3 class="text-foreground font-semibold mb-2">Análise de Áudio</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">
                        Detecte vozes sintéticas e áudios gerados por IA. Suporta MP3, WAV e outros formatos comuns.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative z-10 px-6 sm:px-10 pb-24">
            <div class="max-w-2xl mx-auto text-center p-10 rounded-2xl bg-primary relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-primary to-primary/80"></div>
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold text-primary-foreground mb-3">Pronto para começar?</h2>
                    <p class="text-primary-foreground/70 text-sm mb-6">Crie sua conta gratuitamente e comece a analisar agora mesmo.</p>
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-primary bg-white hover:bg-white/90 transition-colors"
                    >
                        Criar conta gratuita
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 text-center pb-8 text-muted-foreground text-xs">
            © 2026 DeepScan. Todos os direitos reservados.
        </footer>
    </div>
</template>
