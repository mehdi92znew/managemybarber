<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { __ } from '@/translate';
const props = defineProps({
    shop: Object,
    stats: Object,
    logs: Array,
});

const activeTab = ref('overview');

const subForm = useForm({
    status: props.shop.subscription_status,
    ends_at: props.shop.subscription_ends_at ? props.shop.subscription_ends_at.split('T')[0] : '',
});

const approveForm = useForm({});
const toggleUserForm = useForm({});

const submitSubscription = () => {
    subForm.patch(route('admin.shops.subscription.update', props.shop.id), {
        preserveScroll: true,
        onSuccess: () => alert('Subscription updated successfully')
    });
};

const approveShop = () => {
    if (confirm('Are you sure you want to approve this shop?')) {
        approveForm.patch(route('admin.shops.approve', props.shop.id));
    }
};

const suspendShop = () => {
     if (confirm('Suspend this shop? Access will be removed.')) {
        approveForm.patch(route('admin.shops.suspend', props.shop.id));
    }
};

const activateShop = () => {
    if (confirm('Activate this shop?')) {
        approveForm.patch(route('admin.shops.activate', props.shop.id));
    }
};

const toggleUser = (userId) => {
    toggleUserForm.patch(route('admin.users.toggle', userId), {
        preserveScroll: true
    });
};

const impersonate = () => {
    if (confirm(`Login as ${props.shop.owner?.name}?`)) {
        useForm({}).post(route('admin.shops.impersonate', props.shop.id));
    }
};

const getStatusClass = (status) => {
    switch(status) {
        case 'pending': return 'bg-amber-500/10 text-amber-500 border-amber-500/50';
        case 'active': return 'bg-blue-500/10 text-blue-500 border-blue-500/50';
        case 'trial': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/50';
        case 'suspended': return 'bg-red-500/10 text-red-500 border-red-500/50';
        default: return 'bg-slate-500/10 text-slate-500 border-slate-500/50';
    }
};
</script>

<template>
    <Head :title="shop.name + ' - Profil'" />

    <AuthenticatedLayout>
        <template #header-title>{{ shop.name }}</template>
        <template #header>
            <div class="flex flex-col">
                <span class="text-xs uppercase tracking-widest text-amber-500 font-black mb-1">VENTURE PROFILE</span>
                <span class="italic font-black text-slate-900 dark:text-white uppercase">{{ shop.name }}</span>
            </div>
        </template>
        <template #header-subtitle>Comprehensive oversight of venture operations and governance</template>
        <template #header-actions>
            <button @click="impersonate" class="px-6 py-3 bg-slate-900 dark:bg-amber-500 hover:bg-amber-500 dark:hover:bg-amber-400 text-white dark:text-slate-950 font-black rounded-2xl transition-all shadow-[0_15px_30px_-10px_rgba(245,158,11,0.3)] text-[10px] flex items-center gap-3 uppercase italic tracking-widest group">
                <span class="text-lg group-hover:scale-110 transition-transform duration-300">👤</span>
                {{ __('impersonate') }}
            </button>
        </template>

        <div class="space-y-10 pb-20">
            <!-- Venture Metadata Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div v-for="stat in [
                    { label: 'Platform State', value: shop.subscription_status, class: getStatusClass(shop.subscription_status) },
                    { label: 'Total Appointments', value: stats.total_appointments, color: 'text-amber-500' },
                    { label: 'Global Revenue', value: new Intl.NumberFormat('fr-FR', {style: 'currency', currency:'EUR'}).format(stats.total_revenue), color: 'text-emerald-500' },
                    { label: 'Active Assets', value: stats.total_customers, color: 'text-blue-500' }
                ]" :key="stat.label" class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 premium-shadow border border-slate-200 dark:border-white/5 shadow-2xl overflow-hidden group">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-3 group-hover:text-amber-500 transition-colors">{{ stat.label }}</p>
                    <div class="flex items-end justify-between">
                         <div class="text-3xl font-black italic tracking-tighter uppercase tabular-nums" :class="stat.color || stat.class">
                            {{ stat.value }}
                         </div>
                    </div>
                </div>
            </div>

            <!-- Content Split -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <!-- Navigation Column -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-4 premium-shadow border border-slate-200 dark:border-white/5 space-y-2">
                        <button v-for="tab in ['overview', 'users', 'subscription', 'logs']" :key="tab" @click="activeTab = tab" 
                                class="w-full text-left px-8 py-4 rounded-[1.5rem] transition-all duration-300 font-black uppercase tracking-widest text-[9px] italic border flex items-center justify-between group"
                                :class="activeTab === tab ? 'bg-amber-500 border-amber-500 text-slate-950 shadow-[0_10px_20px_-5px_rgba(245,158,11,0.3)]' : 'bg-transparent border-transparent text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-amber-500'">
                            {{ tab === 'logs' ? __('history_logs') : tab === 'users' ? __('shop_users') : tab === 'subscription' ? __('subscription_management') : __('overview') }}
                            <span v-if="activeTab === tab" class="text-lg">→</span>
                        </button>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 premium-shadow border border-slate-200 dark:border-white/5 space-y-4">
                         <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6 italic opacity-60">Governance Actions</h4>
                         <button v-if="shop.subscription_status === 'pending'" @click="approveShop" class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-2xl transition-all uppercase text-[10px] tracking-widest italic shadow-lg shadow-emerald-500/20">APPROVE VENTURE</button>
                         <button v-if="shop.subscription_status !== 'suspended'" @click="suspendShop" class="w-full py-4 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white border border-red-500/20 font-black rounded-2xl transition-all uppercase text-[10px] tracking-widest italic">SUSPEND ACCESS</button>
                         <button v-if="shop.subscription_status === 'suspended'" @click="activateShop" class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-black rounded-2xl transition-all uppercase text-[10px] tracking-widest italic">RESTORE ACCESS</button>
                    </div>
                </div>

                <!-- Main Display Display -->
                <div class="lg:col-span-9">
                    <!-- Overview Tab Display -->
                    <div v-if="activeTab === 'overview'" class="space-y-10">
                         <div class="bg-white dark:bg-slate-900 rounded-[3rem] p-10 premium-shadow border border-slate-200 dark:border-white/5 shadow-2xl relative overflow-hidden group">
                             <div class="absolute top-0 right-0 p-12 opacity-[0.03] scale-[4] group-hover:scale-[4.5] transition-transform duration-1000">
                                 <ApplicationLogo class="w-24 h-24" />
                             </div>
                             <h3 class="font-black text-lg mb-10 italic tracking-widest uppercase text-slate-900 dark:text-white flex items-center gap-4">
                                 <span class="block h-1 w-10 bg-amber-500"></span>
                                 Asset Intelligence
                             </h3>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-16">
                                 <div class="space-y-2">
                                     <p class="text-[9px] text-slate-400 uppercase font-black italic tracking-widest">Genesis Origin</p>
                                     <p class="text-lg text-slate-900 dark:text-white font-black italic tracking-tighter">{{ shop.owner?.name }}</p>
                                     <p class="text-xs text-slate-500 font-black italic uppercase tracking-widest">{{ shop.owner?.email }}</p>
                                 </div>
                                  <div class="space-y-2">
                                     <p class="text-[9px] text-slate-400 uppercase font-black italic tracking-widest">Platform Entry</p>
                                     <p class="text-lg text-slate-900 dark:text-white font-black italic tracking-tighter tabular-nums">{{ new Date(shop.created_at).toLocaleString() }}</p>
                                 </div>
                                 <div class="space-y-2">
                                     <p class="text-[9px] text-slate-400 uppercase font-black italic tracking-widest">Assigned Sector</p>
                                     <p class="text-xs text-slate-900 dark:text-slate-300 font-black italic uppercase tracking-widest leading-relaxed">{{ shop.address || 'UNDEFINED SECTOR' }}</p>
                                 </div>
                                 <div class="space-y-2">
                                     <p class="text-[9px] text-slate-400 uppercase font-black italic tracking-widest">Service Clusters</p>
                                     <p class="text-lg text-amber-500 font-black italic tracking-tighter">{{ shop.services?.length || 0 }} CATALOG NODES</p>
                                 </div>
                             </div>
                         </div>
                    </div>

                    <!-- Users Tab Display -->
                    <div v-if="activeTab === 'users'" class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow overflow-hidden shadow-2xl">
                         <table class="w-full text-left">
                             <thead class="bg-slate-50 dark:bg-black/20 text-[9px] font-black uppercase tracking-[0.3em] text-slate-500 italic border-b border-slate-100 dark:border-white/5">
                                 <tr>
                                     <th class="px-10 py-6">Identity</th>
                                     <th class="px-10 py-6">Protocol Role</th>
                                     <th class="px-10 py-6">Security State</th>
                                     <th class="px-10 py-6 text-right">Governance</th>
                                 </tr>
                             </thead>
                             <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                                 <tr v-for="user in shop.users" :key="user.id" class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-300 group">
                                     <td class="px-10 py-8">
                                         <div class="font-black text-slate-900 dark:text-white group-hover:text-amber-500 transition-colors uppercase italic tracking-tighter text-lg">{{ user.name }}</div>
                                         <div class="text-[9px] font-black text-slate-400 italic font-mono">{{ user.email }}</div>
                                     </td>
                                     <td class="px-10 py-8">
                                         <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border italic shrink-0" 
                                               :class="user.role === 'owner' ? 'bg-amber-500 text-slate-950 border-amber-500 shadow-lg shadow-amber-500/20' : 'bg-slate-100 dark:bg-white/5 text-slate-400 border-slate-200 dark:border-white/10'">
                                             {{ user.role }}
                                         </span>
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
                                         <button @click="toggleUser(user.id)" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 italic border shrink-0"
                                                 :class="user.is_active ? 'bg-red-500/10 border-red-500/20 text-red-500 hover:bg-red-500 hover:text-white' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500 hover:bg-emerald-500 hover:text-white'">
                                             {{ user.is_active ? __('block') : __('unblock') }}
                                         </button>
                                     </td>
                                 </tr>
                             </tbody>
                         </table>
                    </div>

                    <!-- Subscription Tab Display -->
                    <div v-if="activeTab === 'subscription'" class="bg-white dark:bg-slate-900 rounded-[3rem] p-12 premium-shadow border border-slate-200 dark:border-white/5 shadow-2xl max-w-2xl mx-auto space-y-10 group">
                        <div class="space-y-2">
                             <h3 class="font-black text-xl italic tracking-widest uppercase text-slate-900 dark:text-white">{{ __('subscription_management') }}</h3>
                             <p class="text-[10px] text-slate-400 font-black uppercase italic tracking-widest opacity-60 italic">Define venture access tiers and validity periods</p>
                        </div>
                        <form @submit.prevent="submitSubscription" class="space-y-8">
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] italic">Venture Protocol Tier</label>
                                <select v-model="subForm.status" class="w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-2xl h-14 px-6 text-[10px] font-black uppercase tracking-widest italic focus:ring-amber-500 focus:border-amber-500 outline-none">
                                    <option value="pending">PENDING ADMIN AUDIT</option>
                                    <option value="trial">TRIAL PROTOCOL</option>
                                    <option value="active">PREMIUM NODE (FULLY ACTIVE)</option>
                                    <option value="suspended">MANUAL GOVERNANCE SUSPENSION</option>
                                </select>
                            </div>
                            <div class="space-y-4">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] italic">Validity Horizon</label>
                                <input v-model="subForm.ends_at" type="date" class="w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-2xl h-14 px-6 text-[10px] font-black uppercase tracking-widest italic focus:ring-amber-500 focus:border-amber-500 outline-none">
                            </div>

                            <div class="pt-6">
                                <button type="submit" :disabled="subForm.processing" class="w-full py-5 bg-slate-900 dark:bg-amber-500 hover:bg-amber-400 text-white dark:text-slate-950 font-black rounded-2xl uppercase tracking-widest italic disabled:opacity-50 transition-all shadow-[0_15px_30px_-10px_rgba(245,158,11,0.3)] transform active:scale-[0.98]">
                                    {{ subForm.processing ? 'SYNCHRONIZING CORE...' : 'COMMIT PROTOCOL CHANGE' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Logs Tab Display -->
                    <div v-if="activeTab === 'logs'" class="bg-white dark:bg-slate-900 rounded-[3rem] border border-slate-200 dark:border-white/5 premium-shadow overflow-hidden shadow-2xl flex flex-col h-[700px]">
                        <div class="p-10 border-b border-slate-100 dark:border-white/5 flex justify-between items-center bg-slate-50 dark:bg-black/5">
                             <h3 class="font-black italic uppercase tracking-[0.2em] text-[10px] text-slate-900 dark:text-white">Genesis Audit Trail</h3>
                             <span class="text-[9px] font-black text-slate-400 uppercase italic opacity-40">LATEST 50 RECTIFICATIONS</span>
                        </div>
                        <div class="flex-1 overflow-y-auto p-12 space-y-10 custom-scrollbar">
                            <div v-for="log in logs" :key="log.id" class="relative pl-12 before:content-[''] before:absolute before:left-3 before:top-2 before:bottom-0 before:w-px before:bg-slate-100 dark:before:bg-white/5 group">
                                 <div class="absolute left-0 top-1.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-white/10 flex items-center justify-center text-[10px] z-10 group-hover:border-amber-500 group-hover:scale-125 transition-all duration-500 shadow-sm">
                                     <div class="w-1.5 h-1.5 rounded-full bg-slate-400 group-hover:bg-amber-500"></div>
                                 </div>
                                 <div class="space-y-3">
                                     <p class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase italic tracking-tighter leading-tight">{{ log.description }}</p>
                                     <div class="flex flex-wrap items-center gap-4 font-black text-[9px] uppercase tracking-widest italic">
                                         <span class="text-slate-400 tabular-nums">{{ new Date(log.created_at).toLocaleString() }}</span>
                                         <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/5 text-amber-500">USER: {{ log.user?.name || 'SYSTEM' }}</span>
                                         <span class="text-slate-400 opacity-40">NODE: {{ log.ip_address }}</span>
                                     </div>
                                 </div>
                            </div>
                            <div v-if="logs.length === 0" class="py-32 text-center">
                                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-300 italic opacity-40">No audit events found in current horizon</p>
                            </div>
                        </div>
                    </div>
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
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
:dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e293b;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
:dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #334155;
}
</style>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #1e293b;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #334155;
}
</style>
