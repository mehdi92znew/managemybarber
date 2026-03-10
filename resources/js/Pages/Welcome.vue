<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const isScrolled = ref(false);

onMounted(() => {
    window.addEventListener('scroll', () => {
        isScrolled.value = window.scrollY > 20;
    });
});

const features = [
    {
        title: 'Agenda & Gestion du Flux',
        description: 'Un outil pensé pour la rapidité du terrain. Ajoutez vos clients en un clic dès qu\'ils franchissent la porte, organisez la journée de vos coiffeurs et gardez un contrôle absolu sur votre salon.',
        icon: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>'
    },
    {
        title: 'Calcul des Commissions',
        description: 'Automatisez la paie de vos coiffeurs. Définissez des pourcentages personnalisés ou des montants fixes par prestation avec un suivi en temps réel.',
        icon: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    },
    {
        title: 'Caisse & Comptabilité',
        description: 'Suivi journalier du fond de caisse (La Caisse), gestion des paiements, des dépenses récurrentes (loyer, internet) et des encaissements.',
        icon: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>'
    },
    {
        title: 'Rapports & Statistiques',
        description: 'Tableaux de bord visuels pour suivre le chiffre d\'affaires brut, les parts des coiffeurs, le solde restant et la performance de votre salon.',
        icon: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" /></svg>'
    }
];

const testimonials = [
    {
        quote: "Avant le logiciel, on perdait de l'argent avec les oublis de caisse et les commissions mal calculées à la fin du mois. Aujourd'hui tout est tracé au centime près. C'est une révolution pour notre rentabilité.",
        author: "Karim A.",
        role: "Propriétaire, The Royal Barber",
        rating: 5
    },
    {
        quote: "Le chaos de la salle d'attente, c'est de l'histoire ancienne ! Avec l'agenda intelligent, chaque client a son tour respecté à la minute près. Personne n'est zappé et on n'a plus cet effet 'qui est le dernier ?'. L'organisation est parfaite.",
        author: "Mustafa B.",
        role: "Gérant, Boutique El Bahdja",
        rating: 5
    },
    {
        quote: "Fini le cahier et le stylo. L'application a totalement professionnalisé notre façon de travailler. L'équipe est beaucoup plus motivée grâce au suivi de leurs performances, et notre chiffre d'affaires s'en ressent !",
        author: "Jean-Pierre L.",
        role: "Propriétaire, Le Salon de Paris",
        rating: 5
    }
];
</script>

<template>
    <!-- SEO Optimization Meta Tags via Inertia Head -->
    <Head>
        <title>Logiciel de Gestion pour Barbershop & Salon | BarberPlatform</title>
        <meta name="description" content="Découvrez BarberPlatform, le logiciel N°1 pour barbershops et salons de coiffure. Gérez l'agenda, la caisse, et les commissions de vos coiffeurs." />
        <meta name="keywords" content="logiciel barbershop, gestion salon de coiffure, logiciel caisse coiffure, agenda salon de coiffure, application barbershop, calcul commission coiffeur, gestion de rendez-vous" />
        <meta name="robots" content="index, follow" />
        <meta property="og:title" content="Logiciel de Gestion pour Barbershop & Salon | BarberPlatform" />
        <meta property="og:description" content="Automatisez votre barbershop : agenda en clic, caisse journalière et calcul des parts des coiffeurs en temps réel." />
        <meta property="og:type" content="website" />
    </Head>

    <!-- Deep Slate & Obsidian Base -->
    <div class="bg-[#0a0f18] min-h-screen text-slate-300 font-sans selection:bg-amber-500/30 selection:text-amber-200">
        
        <!-- Header / Navbar -->
        <header 
            class="fixed top-0 w-full z-50 transition-all duration-300 border-b border-transparent"
            :class="{ 'bg-[#0f172a]/80 backdrop-blur-md border-white/5 shadow-2xl py-3': isScrolled, 'bg-transparent py-6': !isScrolled }"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <!-- Luxury Logo Mark -->
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/20 flex items-center justify-center text-slate-900">
                            <svg class="w-6 h-6 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" /></svg>
                        </div>
                        <span class="text-2xl font-black text-white tracking-tight font-display">Barber<span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-500">Platform</span></span>
                    </div>

                    <nav class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors">Fonctionnalités</a>
                        <a href="#testimonials" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors">Témoignages</a>
                        <a href="#contact" class="text-sm font-semibold text-slate-400 hover:text-white transition-colors">Contact</a>
                    </nav>

                    <div class="flex items-center gap-4" v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-sm font-bold text-white bg-white/10 hover:bg-white/20 px-6 py-2.5 rounded-full transition-all border border-white/10"
                        >
                            Accéder au Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="hidden sm:inline-block text-sm font-bold text-slate-300 hover:text-white transition-colors"
                            >
                                Connexion
                            </Link>
                            <a
                                href="https://wa.me/213559635615"
                                target="_blank"
                                class="inline-flex items-center px-6 py-2.5 rounded-full text-sm font-black text-white bg-[#25D366] hover:bg-[#1DA851] shadow-lg shadow-[#25D366]/20 hover:shadow-[#25D366]/40 hover:scale-[1.02] transition-all duration-300 gap-2"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.38 0 0 5.38 0 12.031c0 2.646.85 5.109 2.332 7.15l-1.571 5.733 5.86-1.543A11.964 11.964 0 0012.031 24c6.649 0 12.031-5.38 12.031-12.031S18.681 0 12.031 0zm0 21.964c-2.222 0-4.301-.5-6.096-1.378l-4.136 1.089 1.107-4.041A9.873 9.873 0 012.036 12.031C2.036 6.505 6.506 2.036 12.031 2.036c5.524 0 9.995 4.47 9.995 9.995s-4.47 9.995-9.995 9.995zm5.549-7.398c-.305-.152-1.789-.884-2.062-.986-.275-.102-.474-.152-.674.152-.199.305-.783.986-.957 1.189-.174.204-.349.23-.654.076-.305-.152-1.278-.472-2.433-1.503-.896-.801-1.503-1.795-1.674-2.103-.174-.305-.019-.472.133-.623.137-.137.305-.357.457-.535.152-.178.204-.305.305-.508.1-.204.05-.382-.025-.535-.074-.152-.674-1.625-.923-2.224-.242-.581-.489-.503-.674-.512-.174-.009-.374-.012-.574-.012s-.524.076-.8.382c-.275.305-1.049 1.025-1.049 2.499s1.074 2.894 1.224 3.097c.152.204 2.112 3.227 5.114 4.524.714.309 1.27.494 1.704.633.717.228 1.369.196 1.884.119.577-.087 1.789-.731 2.039-1.437.249-.706.249-1.312.174-1.437-.074-.125-.275-.204-.579-.357z"/></svg>
                                WhatsApp
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <!-- Hero Section -->
            <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
                <!-- Background Ambient Glows -->
                <div class="absolute top-0 right-0 -mr-64 -mt-32 w-[800px] h-[800px] bg-amber-500/10 rounded-full blur-[120px] pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -ml-64 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="lg:grid lg:grid-cols-12 lg:gap-16 items-center">
                        <div class="lg:col-span-6 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-widest mb-6">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                                Solution N°1 pour Barbershops
                            </div>
                            <!-- SEO H1 -->
                            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-[1.1] tracking-tight mb-6">
                                Le Logiciel de Gestion <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-500">Ultime</span> pour votre Barbershop
                            </h1>
                            <p class="text-lg sm:text-xl text-slate-400 mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0 font-medium">
                                Automatisez la prise de rendez-vous, calculez les parts de vos collaborateurs en un clic et suivez vos performances financières en temps réel avec un tableau de bord pensé pour vous.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                                <a
                                    href="https://wa.me/213559635615"
                                    target="_blank"
                                    class="w-full sm:w-auto px-8 py-4 rounded-2xl text-base font-black text-white bg-[#25D366] hover:bg-[#1DA851] shadow-[0_0_40px_-10px_rgba(37,211,102,0.4)] hover:shadow-[0_0_60px_-15px_rgba(37,211,102,0.5)] transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.38 0 0 5.38 0 12.031c0 2.646.85 5.109 2.332 7.15l-1.571 5.733 5.86-1.543A11.964 11.964 0 0012.031 24c6.649 0 12.031-5.38 12.031-12.031S18.681 0 12.031 0zm0 21.964c-2.222 0-4.301-.5-6.096-1.378l-4.136 1.089 1.107-4.041A9.873 9.873 0 012.036 12.031C2.036 6.505 6.506 2.036 12.031 2.036c5.524 0 9.995 4.47 9.995 9.995s-4.47 9.995-9.995 9.995zm5.549-7.398c-.305-.152-1.789-.884-2.062-.986-.275-.102-.474-.152-.674.152-.199.305-.783.986-.957 1.189-.174.204-.349.23-.654.076-.305-.152-1.278-.472-2.433-1.503-.896-.801-1.503-1.795-1.674-2.103-.174-.305-.019-.472.133-.623.137-.137.305-.357.457-.535.152-.178.204-.305.305-.508.1-.204.05-.382-.025-.535-.074-.152-.674-1.625-.923-2.224-.242-.581-.489-.503-.674-.512-.174-.009-.374-.012-.574-.012s-.524.076-.8.382c-.275.305-1.049 1.025-1.049 2.499s1.074 2.894 1.224 3.097c.152.204 2.112 3.227 5.114 4.524.714.309 1.27.494 1.704.633.717.228 1.369.196 1.884.119.577-.087 1.789-.731 2.039-1.437.249-.706.249-1.312.174-1.437-.074-.125-.275-.204-.579-.357z"/></svg>
                                    Nous contacter
                                </a>
                                <a 
                                    href="#features" 
                                    class="w-full sm:w-auto px-8 py-4 rounded-2xl text-base font-bold text-white bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 transition-all duration-300 text-center backdrop-blur-sm"
                                >
                                    Découvrir les fonctions
                                </a>
                            </div>
                            
                            <!-- Trust indicators -->
                            <div class="mt-12 flex items-center justify-center lg:justify-start gap-6">
                                <div class="flex -space-x-3">
                                    <img class="w-10 h-10 rounded-full border-2 border-[#0a0f18] object-cover" src="https://images.unsplash.com/photo-1599566150163-29194dcaad36?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Gérant de barbershop utilisateur BarberPlatform" />
        <img class="w-10 h-10 rounded-full border-2 border-[#0a0f18] object-cover" src="https://images.unsplash.com/photo-1527980965255-d3b416303d12?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Coiffeur dans un salon utilisant l'application" />
                                    <div class="w-10 h-10 rounded-full border-2 border-[#0a0f18] bg-[#1e293b] flex items-center justify-center text-xs font-bold text-white">+500</div>
                                </div>
                                <div class="text-sm font-medium text-slate-400">
                                    <span class="text-white font-bold">Approuvé par +500</span><br />
                                    barbershops en France et au Maghreb
                                </div>
                            </div>
                        </div>

                        <!-- 3D Mockup / Illustration Area -->
                        <div class="hidden lg:block lg:col-span-6 relative perspective-1000">
                            <!-- Floating Elements surrounding the main dashboard -->
                            <div class="absolute -top-10 -right-10 w-48 h-48 bg-amber-500 rounded-full blur-[100px] opacity-40"></div>
                            
                            <div class="relative transform rotate-y-[-10deg] rotate-x-[5deg] hover:rotate-y-[0deg] hover:rotate-x-[0deg] transition-all duration-700 ease-out shadow-[0_0_80px_-20px_rgba(245,158,11,0.2)] rounded-3xl overflow-hidden border border-white/10 bg-[#0f172a]/80 backdrop-blur-xl group">
                                <!-- Minimal Dashboard Mockup Design embedded directly -->
                                <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                                    <div class="flex space-x-2">
                                        <div class="w-3 h-3 rounded-full bg-red-500/50"></div>
                                        <div class="w-3 h-3 rounded-full bg-amber-500/50"></div>
                                        <div class="w-3 h-3 rounded-full bg-emerald-500/50"></div>
                                    </div>
                                    <div class="h-4 w-32 bg-white/5 rounded-full"></div>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-8">
                                        <div class="h-6 w-48 bg-white/10 rounded-lg"></div>
                                        <div class="h-8 w-8 bg-amber-500/20 rounded-full border border-amber-500/30 font-bold text-amber-500 flex items-center justify-center text-xs">M</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        <div class="p-4 rounded-xl bg-white/5 border border-white/5 group-hover:border-amber-500/30 transition-colors duration-500 relative overflow-hidden">
                                            <div class="absolute right-0 top-0 w-20 h-20 bg-amber-500/10 rounded-bl-full"></div>
                                            <div class="h-3 w-20 bg-slate-500/50 rounded mt-1 mb-3"></div>
                                            <div class="h-8 w-32 bg-white/20 rounded"></div>
                                        </div>
                                        <div class="p-4 rounded-xl bg-white/5 border border-white/5 relative overflow-hidden">
                                            <div class="absolute right-0 top-0 w-20 h-20 bg-emerald-500/10 rounded-bl-full"></div>
                                            <div class="h-3 w-24 bg-slate-500/50 rounded mt-1 mb-3"></div>
                                            <div class="h-8 w-24 bg-white/20 rounded"></div>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="h-4 w-full bg-white/5 rounded"></div>
                                        <div class="h-4 w-5/6 bg-white/5 rounded"></div>
                                        <div class="h-4 w-4/6 bg-white/5 rounded"></div>
                                    </div>
                                    <div class="mt-6 flex justify-between items-center p-3 rounded-xl bg-gradient-to-r from-amber-500/10 to-transparent border border-amber-500/20">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
                                            <div class="h-3 w-24 bg-amber-500/50 rounded"></div>
                                        </div>
                                        <div class="h-5 w-16 bg-amber-500 rounded p-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SEO Features Section -->
            <section id="features" class="py-24 bg-[#05080c] relative border-t border-white/5">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <h2 class="text-amber-500 font-bold tracking-widest uppercase text-sm mb-3">À propos du Logiciel</h2>
                        <!-- SEO H2 -->
                        <p class="mt-2 text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                            Tout ce dont vous avez besoin pour un Barbershop performant
                        </p>
                        <p class="mt-5 max-w-2xl mx-auto text-lg text-slate-400">
                            Centralisez vos opérations. De l'agenda journalier des coiffeurs jusqu'au calcul minutieux des parts et versements pour éviter toute erreur de caisse.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div 
                            v-for="(feature, index) in features" 
                            :key="index"
                            class="relative p-8 rounded-[2rem] bg-gradient-to-b from-[#111827] to-[#0f172a] border border-white/5 hover:border-amber-500/30 hover:scale-[1.02] transition-all duration-300 group shadow-lg"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-[2rem]"></div>
                            
                            <div class="relative z-10">
                                <div class="w-14 h-14 rounded-2xl bg-[#1e293b] border border-white/10 flex items-center justify-center text-amber-500 mb-6 group-hover:scale-110 group-hover:bg-amber-500/10 transition-all duration-300" v-html="feature.icon"></div>
                                <!-- SEO H3 -->
                                <h3 class="text-xl font-bold text-white mb-3">{{ feature.title }}</h3>
                                <p class="text-sm text-slate-400 leading-relaxed">{{ feature.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials & Trust -->
            <section id="testimonials" class="py-24 relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-black text-white">Ils gèrent leur salon avec BarberPlatform</h2>
                    </div>

                    <div class="grid md:grid-cols-3 gap-8">
                        <div v-for="(t, i) in testimonials" :key="i" class="p-8 rounded-[2rem] bg-white/5 border border-white/10 backdrop-blur-sm relative">
                            <!-- Quotation mark icon -->
                            <div class="text-6xl text-amber-500/20 absolute top-4 right-6 font-serif">"</div>
                            <div class="flex gap-1 mb-6 text-amber-500">
                                <svg v-for="star in t.rating" :key="star" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <p class="text-slate-300 italic mb-6 text-base leading-relaxed">"{{ t.quote }}"</p>
                            <div class="pt-6 border-t border-white/10">
                                <p class="text-white font-bold">{{ t.author }}</p>
                                <p class="text-xs text-amber-500/80">{{ t.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Free Trial Dedicated Section (Stitch Premium Design) -->
            <section class="py-24 bg-[#0a0f18] relative overflow-hidden border-t border-white/5">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                    <div class="grid lg:grid-cols-2 gap-16 items-center">
                        <!-- Left Side: Copy -->
                        <div>
                            <h2 class="text-4xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-white tracking-tight mb-6 leading-tight">
                                Testez l'application pendant 15 jours gratuits
                            </h2>
                            <p class="text-lg text-slate-400 mb-10 leading-relaxed font-medium">
                                Découvrez les fonctionnalités premium de notre logiciel avec un essai sans aucun risque. Aucune carte bancaire requise.
                            </p>
                            <div class="space-y-6 mb-12">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500/20 border border-amber-500/30">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="text-slate-200 font-medium text-lg">0 frais d'installation</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500/20 border border-amber-500/30">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="text-slate-200 font-medium text-lg">Nous configurons votre compte</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-500/20 border border-amber-500/30">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="text-slate-200 font-medium text-lg">Accompagnement WhatsApp 24/7</p>
                                </div>
                            </div>
                            <a 
                                href="https://wa.me/213559635615" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="inline-flex w-full sm:w-auto items-center justify-center gap-3 bg-amber-500 hover:bg-amber-400 text-slate-900 px-8 py-4 rounded-xl shadow-[0_4px_20px_rgba(245,158,11,0.3)] hover:shadow-[0_8px_30px_rgba(245,158,11,0.5)] transform hover:-translate-y-1 transition-all duration-300 font-bold text-lg"
                            >
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.38 0 0 5.38 0 12.031c0 2.646.85 5.109 2.332 7.15l-1.571 5.733 5.86-1.543A11.964 11.964 0 0012.031 24c6.649 0 12.031-5.38 12.031-12.031S18.681 0 12.031 0zm0 21.964c-2.222 0-4.301-.5-6.096-1.378l-4.136 1.089 1.107-4.041A9.873 9.873 0 012.036 12.031C2.036 6.505 6.506 2.036 12.031 2.036c5.524 0 9.995 4.47 9.995 9.995s-4.47 9.995-9.995 9.995zm5.549-7.398c-.305-.152-1.789-.884-2.062-.986-.275-.102-.474-.152-.674.152-.199.305-.783.986-.957 1.189-.174.204-.349.23-.654.076-.305-.152-1.278-.472-2.433-1.503-.896-.801-1.503-1.795-1.674-2.103-.174-.305-.019-.472.133-.623.137-.137.305-.357.457-.535.152-.178.204-.305.305-.508.1-.204.05-.382-.025-.535-.074-.152-.674-1.625-.923-2.224-.242-.581-.489-.503-.674-.512-.174-.009-.374-.012-.574-.012s-.524.076-.8.382c-.275.305-1.049 1.025-1.049 2.499s1.074 2.894 1.224 3.097c.152.204 2.112 3.227 5.114 4.524.714.309 1.27.494 1.704.633.717.228 1.369.196 1.884.119.577-.087 1.789-.731 2.039-1.437.249-.706.249-1.312.174-1.437-.074-.125-.275-.204-.579-.357z"/></svg>
                                Activer via WhatsApp
                            </a>
                        </div>
                        
                        <!-- Right Side: VIP Pass Card -->
                        <div class="relative w-full aspect-[1.6/1] max-w-lg mx-auto transform transition-transform duration-500 hover:scale-[1.02] hover:-rotate-y-2 hover:rotate-x-2 perspective-1000 group">
                            <!-- Background Blur Glow -->
                            <div class="absolute inset-0 bg-amber-500/20 blur-[80px] rounded-full group-hover:bg-amber-500/30 transition-colors duration-500"></div>
                            
                            <!-- Glass Card -->
                            <div class="relative h-full w-full rounded-2xl overflow-hidden flex flex-col p-8 border border-amber-500/40 shadow-[0_8px_32px_rgba(0,0,0,0.8)] backdrop-blur-xl bg-gradient-to-br from-[#1e293b]/80 to-[#0f172a]/90">
                                <!-- Holographic Texture Overlay -->
                                <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-amber-500/5 to-white/10 opacity-50 pointer-events-none mix-blend-overlay"></div>
                                
                                <div class="flex justify-between items-start mb-auto relative z-10">
                                    <div class="flex flex-col">
                                        <span class="text-xs uppercase tracking-[0.2em] text-amber-500 font-bold mb-1">Pass VIP Premium</span>
                                        <span class="text-3xl font-black tracking-tight text-white drop-shadow-md">ACCÈS SALON</span>
                                    </div>
                                    <div class="bg-amber-500 px-4 py-1.5 rounded-full text-xs font-black text-slate-900 tracking-tighter shadow-md">
                                        15 JOURS
                                    </div>
                                </div>
                                
                                <div class="mt-8 flex items-end justify-between relative z-10">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] uppercase tracking-widest text-slate-400">Autorisation Accordée</span>
                                        <span class="text-sm font-mono text-slate-200 bg-white/5 px-2 py-1 rounded border border-white/10 mt-1">MB2026</span>
                                    </div>
                                    <div class="w-12 h-12 bg-gradient-to-br from-white/20 to-transparent rounded-xl border border-white/20 flex items-center justify-center shadow-inner">
                                        <svg class="w-8 h-8 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                    </div>
                                </div>
                                
                                <!-- Bottom Golden Edge -->
                                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-amber-500 to-transparent opacity-80"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section class="py-20 relative bg-[#05080c]">
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-green-600 opacity-5"></div>
                <!-- Top border line -->
                <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-[#25D366]/30 to-transparent"></div>
                
            </section>
        </main>

        <!-- Footer / SEO Links -->
        <footer id="contact" class="bg-[#05080c] border-t border-white/5 pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-900">
                                <svg class="w-4 h-4 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" /></svg>
                            </div>
                            <span class="text-xl font-black text-white">Barber<span class="text-amber-500">Platform</span></span>
                        </div>
                        <p class="text-sm text-slate-500 mb-6">L'outil métier indispensable pour gérer vos rendez-vous, vos équipes et votre chiffre d'affaires.</p>
                    </div>
                    
                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Produit</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Fonctionnalités</a></li>
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Tarifs</a></li>
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Sécurité des données</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Ressources</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Guide d'utilisation</a></li>
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Astuces de gestion</a></li>
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Blog SEO & Marketing</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-xs">Légal</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Mentions légales</a></li>
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">Politique de confidentialité</a></li>
                            <li><a href="#" class="text-sm text-slate-500 hover:text-amber-500 transition-colors">CGV</a></li>
                        </ul>
                    </div>
                </div>

                <div class="pt-8 border-t border-white/5 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-slate-600">
                        &copy; 2026 BarberPlatform - Logiciel de point de vente et gestion. Tous droits réservés.
                    </p>
                </div>
            </div>
        </footer>
        <!-- Floating WhatsApp Button -->
        <a 
            href="https://wa.me/213559635615" 
            target="_blank" 
            rel="noopener noreferrer"
            class="fixed bottom-6 right-6 z-50 bg-[#25D366] text-white p-4 rounded-full shadow-[0_4px_20px_rgba(37,211,102,0.4)] hover:scale-110 hover:shadow-[0_6px_25px_rgba(37,211,102,0.6)] transition-all duration-300 group flex items-center justify-center cursor-pointer"
            aria-label="Contactez-nous sur WhatsApp"
        >
            <svg class="w-8 h-8 pointer-events-none" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.38 0 0 5.38 0 12.031c0 2.646.85 5.109 2.332 7.15l-1.571 5.733 5.86-1.543A11.964 11.964 0 0012.031 24c6.649 0 12.031-5.38 12.031-12.031S18.681 0 12.031 0zm0 21.964c-2.222 0-4.301-.5-6.096-1.378l-4.136 1.089 1.107-4.041A9.873 9.873 0 012.036 12.031C2.036 6.505 6.506 2.036 12.031 2.036c5.524 0 9.995 4.47 9.995 9.995s-4.47 9.995-9.995 9.995zm5.549-7.398c-.305-.152-1.789-.884-2.062-.986-.275-.102-.474-.152-.674.152-.199.305-.783.986-.957 1.189-.174.204-.349.23-.654.076-.305-.152-1.278-.472-2.433-1.503-.896-.801-1.503-1.795-1.674-2.103-.174-.305-.019-.472.133-.623.137-.137.305-.357.457-.535.152-.178.204-.305.305-.508.1-.204.05-.382-.025-.535-.074-.152-.674-1.625-.923-2.224-.242-.581-.489-.503-.674-.512-.174-.009-.374-.012-.574-.012s-.524.076-.8.382c-.275.305-1.049 1.025-1.049 2.499s1.074 2.894 1.224 3.097c.152.204 2.112 3.227 5.114 4.524.714.309 1.27.494 1.704.633.717.228 1.369.196 1.884.119.577-.087 1.789-.731 2.039-1.437.249-.706.249-1.312.174-1.437-.074-.125-.275-.204-.579-.357z"/></svg>
            <!-- Bulle tooltip au survol -->
            <span class="absolute right-full mr-4 bg-[#0a0f18] text-white text-sm font-bold py-2 px-4 rounded-xl border border-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap shadow-lg">
                Écrivez-nous sur WhatsApp
            </span>
        </a>
    </div>
</template>

<style>
/* CSS Reset extension & Utility classes specifically for this page */
.font-display { font-family: 'Inter', system-ui, sans-serif; }
.perspective-1000 { perspective: 1000px; }
.rotate-y-\[-10deg\] { transform: rotateY(-10deg); }
.rotate-x-\[5deg\] { transform: rotateX(5deg); }
.hover\:rotate-y-\[0deg\]:hover { transform: rotateY(0deg); }
.hover\:rotate-x-\[0deg\]:hover { transform: rotateX(0deg); }
</style>
