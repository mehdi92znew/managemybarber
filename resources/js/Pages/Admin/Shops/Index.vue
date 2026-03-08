<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { __ } from '@/translate';
import debounce from 'lodash/debounce';

const props = defineProps({
    shops: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, status], debounce(([newSearch, newStatus]) => {
    router.get(route('admin.shops.index'), { search: newSearch, status: newStatus }, {
        preserveState: true,
        replace: true
    });
}, 300));

const getStatusClass = (val) => {
    switch (val) {
        case 'pending': return 'bg-amber-500/10 border-amber-500/50 text-amber-500';
        case 'trial': return 'bg-emerald-500/10 border-emerald-500/50 text-emerald-500';
        case 'active': return 'bg-blue-500/10 border-blue-500/50 text-blue-500';
        case 'suspended': return 'bg-red-500/10 border-red-500/50 text-red-500';
        default: return 'bg-slate-500/10 border-slate-500/50 text-slate-500';
    }
};
</script>

<template>
    <Head :title="__('manage_shops')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __('manage_shops') }}</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-black mb-1">PARTNER NETWORK</span>
                <span class="italic font-black text-slate-900 dark:text-white">{{ __('manage_shops') }}</span>
            </div>
        </template>
        <template #header-subtitle>Full oversight of platform-wide barbershop operations</template>

        <div class="space-y-8 pb-12">
            <!-- Filter Bar Premium -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 premium-shadow border border-slate-200 dark:border-white/5 flex flex-col md:flex-row gap-6 items-center justify-between">
                <div class="relative w-full md:w-96">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 tracking-widest uppercase italic">SEARCH</span>
                    <input v-model="search" type="text" placeholder="..." class="w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-2xl pl-20 focus:ring-amber-500 focus:border-amber-500 transition-all text-xs h-12 font-black italic uppercase tracking-widest">
                </div>
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="relative w-full md:w-60">
                         <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 tracking-widest uppercase italic pointer-events-none">PHASE</span>
                         <select v-model="status" class="w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-2xl pl-16 focus:ring-amber-500 focus:border-amber-500 text-[10px] h-12 font-black uppercase tracking-widest italic outline-none">
                            <option value="">ALL STATUS</option>
                            <option value="pending">PENDING</option>
                            <option value="trial">TRIALING</option>
                            <option value="active">ACTIVE</option>
                            <option value="suspended">SUSPENDED</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Shops Table Premium -->
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-black/20 text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 italic border-b border-slate-100 dark:border-white/5">
                            <tr>
                                <th class="px-10 py-6">Vanguard Detail</th>
                                <th class="px-10 py-6">Governance</th>
                                <th class="px-10 py-6">State</th>
                                <th class="px-10 py-6">Genesis</th>
                                <th class="px-10 py-6 text-right">Protocol</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <tr v-for="shop in shops.data" :key="shop.id" class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-300 group">
                                <td class="px-10 py-8">
                                    <div class="font-black text-slate-900 dark:text-white group-hover:text-amber-500 transition-colors uppercase italic tracking-tighter text-lg">{{ shop.name }}</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ shop.slug }}</div>
                                </td>
                                <td class="px-10 py-8">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center text-[10px] font-black text-slate-500 border border-slate-200 dark:border-white/10 shrink-0 uppercase italic font-bold">
                                            {{ shop.owner?.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase italic">{{ shop.owner?.name || 'N/A' }}</div>
                                            <div class="text-[9px] text-slate-500 font-bold italic tracking-tighter">{{ shop.owner?.email || '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8">
                                    <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border italic" :class="getStatusClass(shop.subscription_status)">
                                        {{ shop.subscription_status }}
                                    </span>
                                </td>
                                <td class="px-10 py-8 text-[11px] font-black text-slate-400 italic tabular-nums">
                                    {{ new Date(shop.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <Link :href="route('admin.shops.show', shop.id)" class="px-5 py-2.5 bg-slate-900 dark:bg-white/5 hover:bg-amber-500 dark:hover:bg-amber-500 text-white hover:text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-300 italic">
                                        ANALYZE PROFILE
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="shops.data.length === 0">
                                <td colspan="5" class="py-24 text-center">
                                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500 italic">No assets found in current selection</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Premium -->
                <div class="px-10 py-8 border-t border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02] flex items-center justify-between">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Inventory Point: {{ shops.from }} - {{ shops.to }} of {{ shops.total }}</div>
                    <div class="flex gap-2">
                        <Link v-for="link in shops.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="grow-0 px-4 py-2 rounded-xl text-[10px] font-black transition-all border border-slate-200 dark:border-white/10 italic" :class="link.active ? 'bg-amber-500 border-amber-500 text-slate-950 font-black' : 'bg-white dark:bg-slate-900 text-slate-500 hover:text-amber-500'" />
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
