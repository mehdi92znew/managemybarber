<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 dark:bg-slate-950 p-4 md:p-8 relative overflow-hidden">
        <!-- Abstract Background Shapes -->
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-amber-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-500/10 rounded-full blur-[120px]"></div>

        <div class="w-full max-w-[1100px] grid lg:grid-cols-2 rounded-[2.5rem] overflow-hidden bg-white dark:bg-slate-900 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.1)] relative z-10 border border-slate-200 dark:border-white/5">
            <!-- Left Side: Branding/Visual -->
            <div class="hidden lg:flex flex-col justify-between p-12 bg-slate-900 relative overflow-hidden">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/40 to-transparent"></div>
                    <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover grayscale" />
                </div>
                
                <div class="relative z-10">
                    <Link href="/">
                        <ApplicationLogo class="w-16 h-16" />
                    </Link>
                </div>

                <div class="relative z-10">
                    <h1 class="text-5xl font-black text-white leading-tight mb-6 uppercase tracking-tighter">
                        Elevate Your <span class="text-amber-500">Style.</span><br/>
                        Master Your <span class="text-white/40">Business.</span>
                    </h1>
                    <p class="text-slate-400 text-lg font-medium max-w-md">
                        The ultimate management platform for modern barbershops. Join the revolution in grooming business.
                    </p>
                </div>

                <div class="relative z-10 flex items-center gap-6">
                    <div class="flex -space-x-3">
                        <img v-for="i in 3" :key="i" :src="`https://i.pravatar.cc/100?img=${i+10}`" class="w-10 h-10 rounded-full border-2 border-slate-900" />
                    </div>
                    <p class="text-slate-500 text-sm font-bold">Joined by <span class="text-white">500+</span> barbers</p>
                </div>
            </div>

            <!-- Right Side: Content -->
            <div class="flex flex-col p-8 md:p-16">
                <div class="lg:hidden mb-12 flex justify-center">
                    <Link href="/">
                        <ApplicationLogo class="w-16 h-16" />
                    </Link>
                </div>

                <div class="flex-1 flex flex-col justify-center">
                    <slot />
                </div>

                <!-- Language Switcher -->
                <div class="mt-12 flex items-center justify-center gap-4 py-4 border-t border-slate-100 dark:border-white/5">
                    <a :href="route('language.switch', 'en')" :class="['text-xs font-black tracking-widest transition-all', currentLocale === 'en' ? 'text-amber-500' : 'text-slate-400 hover:text-slate-600']">ENGLISH</a>
                    <span class="text-slate-200 dark:text-white/10 text-xs">/</span>
                    <a :href="route('language.switch', 'fr')" :class="['text-xs font-black tracking-widest transition-all', currentLocale === 'fr' ? 'text-amber-500' : 'text-slate-400 hover:text-slate-600']">FRANÇAIS</a>
                    <span class="text-slate-200 dark:text-white/10 text-xs">/</span>
                    <a :href="route('language.switch', 'ar')" :class="['text-xs font-black tracking-widest transition-all', currentLocale === 'ar' ? 'text-amber-500' : 'text-slate-400 hover:text-slate-600 font-arabic']">العربية</a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-arabic {
    font-family: 'Cairo', sans-serif;
}
</style>
