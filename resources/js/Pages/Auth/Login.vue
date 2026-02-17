<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="__('log_in')" />

        <div class="mb-10">
            <h2 class="text-4xl font-black text-slate-900 dark:text-white uppercase tracking-tighter mb-2">{{ __('log_in') }}</h2>
            <p class="text-slate-500 font-medium">{{ __('welcome_back_desc') || 'Enter your credentials to access your dashboard.' }}</p>
        </div>

        <div v-if="status" class="mb-6 p-4 rounded-2xl bg-emerald-500/10 text-emerald-600 text-sm font-bold border border-emerald-500/20">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
                <label for="email" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">{{ __('email') }}</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-amber-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path></svg>
                    </div>
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full pl-12 bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-white/10 rounded-2xl focus:ring-amber-500 focus:border-amber-500 transition-all duration-300"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@barberapp.com"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <div class="flex justify-between items-center px-1">
                    <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 uppercase">{{ __('password') }}</label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[10px] font-black uppercase tracking-widest text-amber-500 hover:text-amber-600 transition-colors"
                    >
                        {{ __('forgot_password') }}
                    </Link>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-amber-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full pl-12 bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-white/10 rounded-2xl focus:ring-amber-500 focus:border-amber-500 transition-all duration-300"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-amber-500 focus:ring-amber-500" />
                <span class="ms-3 text-sm font-bold text-slate-500 cursor-pointer" @click="form.remember = !form.remember">{{ __('remember_me') }}</span>
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-slate-900 border-none rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-black shadow-[0_20px_40px_-12px_rgba(0,0,0,0.2)] hover:shadow-none transition-all duration-300 transform active:scale-[0.98]"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    {{ __('log_in') }}
                </PrimaryButton>
            </div>

            <p class="text-center text-sm font-bold text-slate-500">
                {{ __('don_t_have_account') || "Don't have an account?" }} 
                <Link :href="route('register')" class="text-amber-500 hover:text-amber-600 ml-1">{{ __('register') }}</Link>
            </p>
        </form>
    </GuestLayout>
</template>
