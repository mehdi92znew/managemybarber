<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { __ } from '@/translate';
import debounce from 'lodash/debounce';

const props = defineProps({
    logs: Object,
    filters: Object,
    shops: Array,
    actions: Array
});

const search = ref(props.filters.search || '');
const shop_id = ref(props.filters.shop_id || '');
const action = ref(props.filters.action || '');

watch([search, shop_id, action], debounce(() => {
    router.get(route('admin.logs.index'), { 
        search: search.value, 
        shop_id: shop_id.value,
        action: action.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 400));

const getActionColor = (a) => {
    if (a.includes('blocked') || a.includes('error') || a.includes('delete')) return 'text-rose-500';
    if (a.includes('approval') || a.includes('active') || a.includes('create')) return 'text-emerald-500';
    return 'text-amber-500';
};
</script>

<template>
    <Head title="System Audit Logs" />

    <AuthenticatedLayout>
        <template #header-title>Registry Logs</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-black mb-1">GENESIS AUDIT</span>
                <span class="italic font-black text-slate-900 dark:text-white uppercase">System Protocol History</span>
            </div>
        </template>
        <template #header-subtitle>Global oversight of every interaction within the platform ecosystem</template>

        <div class="space-y-10 pb-20">
            <!-- Luxury Control Bar -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 premium-shadow border border-slate-200 dark:border-white/5 flex flex-wrap items-center gap-6 shadow-2xl">
                <div class="flex-1 min-w-[300px] relative group">
                    <input v-model="search" type="text" placeholder="Search audit trail..." 
                           class="w-full bg-slate-50 dark:bg-black/40 border-transparent rounded-2xl h-14 pl-12 pr-6 text-xs font-black uppercase tracking-widest italic focus:ring-amber-500 focus:bg-white dark:focus:bg-black transition-all outline-none">
                    <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-amber-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <select v-model="shop_id" class="bg-slate-50 dark:bg-black/40 border-transparent rounded-2xl h-14 px-6 text-[10px] font-black uppercase tracking-widest italic focus:ring-amber-500 outline-none min-w-[200px]">
                    <option value="">All Ventures</option>
                    <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name.toUpperCase() }}</option>
                </select>

                <select v-model="action" class="bg-slate-50 dark:bg-black/40 border-transparent rounded-2xl h-14 px-6 text-[10px] font-black uppercase tracking-widest italic focus:ring-amber-500 outline-none min-w-[200px]">
                    <option value="">All protocols</option>
                    <option v-for="a in actions" :key="a" :value="a">{{ a.toUpperCase().replace(/_/g, ' ') }}</option>
                </select>
            </div>

            <!-- Audit Feed -->
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow overflow-hidden shadow-2xl">
                 <table class="w-full text-left">
                     <thead class="bg-slate-50 dark:bg-black/20 text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 italic border-b border-slate-100 dark:border-white/5">
                         <tr>
                             <th class="px-10 py-6">Timestamp</th>
                             <th class="px-10 py-6">Venture Cluster</th>
                             <th class="px-10 py-6">Protocol Interaction</th>
                             <th class="px-10 py-6">Executed By</th>
                         </tr>
                     </thead>
                     <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                         <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-300 group">
                             <td class="px-10 py-8">
                                 <div class="text-[11px] font-black text-slate-400 group-hover:text-amber-500 transition-colors uppercase italic tracking-tighter tabular-nums">
                                     {{ new Date(log.created_at).toLocaleString() }}
                                 </div>
                                 <div class="text-[9px] font-bold text-slate-300 uppercase mt-1">NODE: {{ log.ip_address }}</div>
                             </td>
                             <td class="px-10 py-8">
                                 <div v-if="log.shop" class="flex flex-col">
                                     <span class="text-xs font-black text-slate-900 dark:text-white uppercase italic tracking-tighter">{{ log.shop.name }}</span>
                                     <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">ID: #{{ log.shop.id }}</span>
                                 </div>
                                 <span v-else class="text-[9px] font-black text-slate-300 italic">SYSTEM LEVEL</span>
                             </td>
                             <td class="px-10 py-8 max-w-md">
                                 <div class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border italic inline-block mb-3"
                                      :class="getActionColor(log.action).replace('text-', 'bg-').replace('500', '500/10') + ' ' + getActionColor(log.action) + ' ' + 'border-slate-100 dark:border-white/5'">
                                     {{ log.action.replace(/_/g, ' ') }}
                                 </div>
                                 <p class="text-xs font-black text-slate-700 dark:text-slate-300 leading-relaxed italic uppercase tracking-tighter">{{ log.description }}</p>
                             </td>
                             <td class="px-10 py-8">
                                 <div v-if="log.user" class="flex items-center gap-3">
                                     <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center text-[10px] font-black text-slate-500 border border-slate-200 dark:border-white/10 shrink-0 uppercase italic">
                                         {{ log.user.name.charAt(0) }}
                                     </div>
                                     <div class="flex flex-col">
                                         <span class="text-xs font-black text-slate-900 dark:text-white uppercase italic tracking-tighter">{{ log.user.name }}</span>
                                         <span class="text-[10px] font-black text-amber-500 uppercase italic opacity-60 tracking-widest">{{ log.user.role }}</span>
                                     </div>
                                 </div>
                                 <span v-else class="text-[10px] font-black text-slate-400 italic">AUTOMATED ENGINE</span>
                             </td>
                         </tr>
                         <tr v-if="logs.data.length === 0">
                             <td colspan="4" class="py-32 text-center">
                                 <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-300 italic opacity-40">No entries detected in current horizon</p>
                             </td>
                         </tr>
                     </tbody>
                 </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center pt-10">
                <div class="flex items-center gap-3 bg-white dark:bg-slate-900 p-3 rounded-[2rem] premium-shadow border border-slate-200 dark:border-white/5 shadow-xl">
                    <Component 
                        v-for="link in logs.links" 
                        :key="link.label"
                        :is="link.url ? 'Link' : 'span'"
                        :href="link.url"
                        v-html="link.label"
                        class="px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest italic transition-all duration-300"
                        :class="[
                            link.active ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/20' : 'text-slate-400 hover:text-amber-500',
                            !link.url ? 'opacity-20 cursor-default' : 'hover:scale-105'
                        ]"
                    />
                </div>
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
