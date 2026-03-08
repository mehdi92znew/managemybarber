<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { __ } from '@/translate';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object,
    filters: Object,
    auth: Object
});

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');

watch([search, role], debounce(([newSearch, newRole]) => {
    router.get(route('admin.users.index'), { search: newSearch, role: newRole }, {
        preserveState: true,
        replace: true
    });
}, 300));

const blockForm = useForm({});

const toggleBlock = (userId) => {
    blockForm.patch(route('admin.users.toggle', userId), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="__('manage_users')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('manage_users') }}</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-black mb-1">USER GOVERNANCE</span>
                <span class="italic font-black text-slate-900 dark:text-white">{{ __('manage_users') }}</span>
            </div>
        </template>
        <template #header-subtitle>Global user control and security oversight for the entire ecosystem</template>

        <div class="space-y-8 pb-12">
            <!-- Filter Bar Premium -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 premium-shadow border border-slate-200 dark:border-white/5 flex flex-col md:flex-row gap-6 items-center justify-between">
                <div class="relative w-full md:w-96">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 tracking-widest uppercase italic">SEARCH</span>
                    <input v-model="search" type="text" placeholder="..." class="w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-2xl pl-20 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs h-12 font-black italic uppercase tracking-widest">
                </div>
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="relative w-full md:w-60">
                         <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 tracking-widest uppercase italic pointer-events-none">ROLE</span>
                         <select v-model="role" class="w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-2xl pl-16 focus:ring-amber-500 focus:border-amber-500 text-[10px] h-12 font-black uppercase tracking-widest italic outline-none">
                            <option value="">ALL ROLES</option>
                            <option value="owner">OWNERS</option>
                            <option value="barber">BARBERS</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Users Table Premium -->
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-black/20 text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 italic border-b border-slate-100 dark:border-white/5">
                            <tr>
                                <th class="px-10 py-6">Identity</th>
                                <th class="px-10 py-6">Privileges</th>
                                <th class="px-10 py-6">Attached Venture</th>
                                <th class="px-10 py-6">Security State</th>
                                <th class="px-10 py-6 text-right">Protocol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-300 group">
                                <td class="px-10 py-8">
                                    <div class="font-black text-slate-900 dark:text-white group-hover:text-amber-500 transition-colors uppercase italic tracking-tighter text-lg">{{ user.name }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ user.email }}</div>
                                </td>
                                <td class="px-10 py-8">
                                    <span class="px-3 py-1 rounded-lg text-[9px] font-black tracking-[0.1em] uppercase italic border shrink-0" 
                                          :class="user.role === 'owner' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-slate-100 dark:bg-white/5 text-slate-500 border-slate-200 dark:border-white/10'">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-10 py-8">
                                    <Link v-if="user.shop" :href="route('admin.shops.show', user.shop.id)" class="text-xs font-black text-slate-800 dark:text-slate-200 hover:text-amber-500 uppercase italic underline decoration-slate-200 dark:decoration-slate-800 underline-offset-8 transition-colors">
                                        {{ user.shop.name }}
                                    </Link>
                                    <span v-else class="text-[10px] font-bold text-slate-400 uppercase italic tracking-widest opacity-40 italic">UNLINKED</span>
                                </td>
                                <td class="px-10 py-8">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full" :class="user.is_active ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]'"></div>
                                        <span class="text-[9px] font-black uppercase tracking-widest italic" :class="user.is_active ? 'text-emerald-500' : 'text-red-500'">
                                            {{ user.is_active ? 'AUTHORIZED' : 'RESTRICTED' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <button v-if="user.id !== auth.user.id" @click="toggleBlock(user.id)" 
                                            class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 italic border shrink-0"
                                            :class="user.is_active ? 'bg-red-500/10 border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500 hover:bg-emerald-500 hover:text-white'">
                                        {{ user.is_active ? __('block') : __('unblock') }}
                                    </button>
                                    <span v-else class="text-[9px] font-black text-slate-400 uppercase italic tracking-widest opacity-20 italic">GENESIS ADMIN</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Premium -->
                <div class="px-10 py-8 border-t border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02] flex items-center justify-between">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Node Records: {{ users.total }}</div>
                    <div class="flex gap-2">
                        <Link v-for="link in users.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="grow-0 px-4 py-2 rounded-xl text-[10px] font-black transition-all border border-slate-200 dark:border-white/10 italic" :class="link.active ? 'bg-amber-500 border-amber-500 text-slate-950 font-black' : 'bg-white dark:bg-slate-900 text-slate-500 hover:text-amber-500'" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.premium-shadow {
    box-shadow: 0 20px 50px -20px rgba(0, 0, 0, 0.05);
}
:dark .premium-shadow {
    box-shadow: 0 30px 60px -30px rgba(0, 0, 0, 0.4);
}
</style>
