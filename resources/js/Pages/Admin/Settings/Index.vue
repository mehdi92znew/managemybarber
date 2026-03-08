<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { __ } from '@/translate';

const props = defineProps({
    settings: Array
});

const form = useForm({
    settings: props.settings.map(s => ({ key: s.key, value: s.value, type: s.type }))
});

const submit = () => {
    form.patch(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Settings saved')
    });
};
</script>

<template>
    <Head :title="__('platform_settings')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('platform_settings') }}</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-black mb-1">SYSTEM ARCHITECTURE</span>
                <span class="italic font-black text-slate-900 dark:text-white">{{ __('platform_settings') }}</span>
            </div>
        </template>
        <template #header-subtitle>Global configuration and protocol definitions for the SaaS ecosystem</template>

        <div class="max-w-4xl mx-auto pb-12">
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 premium-shadow border border-slate-200 dark:border-white/5 shadow-2xl">
                <form @submit.prevent="submit" class="space-y-10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-for="(setting, index) in form.settings" :key="setting.key" 
                             class="space-y-4 p-8 bg-slate-50 dark:bg-black/20 rounded-[2rem] border border-slate-100 dark:border-white/5 hover:border-amber-500/30 transition-all duration-300 group">
                            
                            <div class="flex justify-between items-start">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] italic group-hover:text-amber-500 transition-colors">
                                    {{ setting.key.replace(/_/g, ' ') }}
                                </label>
                                <div class="h-1.5 w-1.5 rounded-full bg-amber-500/50"></div>
                            </div>
                            
                            <div v-if="setting.type === 'boolean'" class="flex items-center gap-4">
                                <button type="button" @click="form.settings[index].value = !form.settings[index].value" 
                                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-all duration-500 focus:outline-none border shadow-inner"
                                        :class="form.settings[index].value ? 'bg-amber-500 border-amber-600' : 'bg-slate-200 dark:bg-white/10 border-transparent'">
                                    <span :class="form.settings[index].value ? 'translate-x-6' : 'translate-x-1'" 
                                          class="inline-block h-5 w-5 transform rounded-full bg-white shadow-xl transition-all duration-500" />
                                </button>
                                <span class="text-[10px] font-black uppercase italic tracking-widest" :class="form.settings[index].value ? 'text-amber-500' : 'text-slate-400'">
                                    {{ form.settings[index].value ? 'ENABLED' : 'DISABLED' }}
                                </span>
                            </div>

                            <div v-else class="relative">
                                <input v-model="form.settings[index].value" :type="setting.type === 'integer' ? 'number' : 'text'" 
                                       class="w-full bg-white dark:bg-black/40 border-slate-200 dark:border-white/10 rounded-2xl focus:ring-amber-500 focus:border-amber-500 text-xs h-12 italic font-black uppercase tracking-widest pl-4 transition-all">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-400 italic opacity-40">
                                    {{ setting.type.toUpperCase() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="form.settings.length === 0" class="py-24 text-center">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-300 italic">No protocols defined in current genesis</p>
                    </div>

                    <div class="pt-10 border-t border-slate-100 dark:border-white/5 flex justify-end items-center gap-6">
                        <p class="text-[10px] font-black text-slate-400 uppercase italic tracking-widest opacity-40">Ensure all changes align with platform policy</p>
                        <button type="submit" :disabled="form.processing" 
                                class="px-10 py-4 bg-slate-900 dark:bg-amber-500 hover:bg-amber-500 dark:hover:bg-amber-400 text-white dark:text-slate-950 font-black rounded-2xl transition-all shadow-[0_15px_30px_-10px_rgba(245,158,11,0.3)] text-[10px] uppercase italic tracking-[0.2em] transform active:scale-95 disabled:opacity-50">
                            {{ form.processing ? 'SYNCHRONIZING...' : 'COMMIT PROTOCOL' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.premium-shadow {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
}
:dark .premium-shadow {
    box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.6);
}
</style>
