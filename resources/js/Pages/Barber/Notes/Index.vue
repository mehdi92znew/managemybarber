<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { trans } from "@/lang";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Swal from "sweetalert2";

const props = defineProps({
    notes: Array,
    barbers: Array,
    filters: Object,
});

const page = usePage();
const getLocale = () => page.props.locale || "en";
const __ = (key) => trans(key, getLocale());

const filterForm = useForm({
    date: props.filters.date || "",
    type: props.filters.type || "",
});

const applyFilters = () => {
    filterForm.get(route("barber.notes.index"), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.date = "";
    filterForm.type = "";
    applyFilters();
};

const getBackgroundColor = (type) => {
    switch (type) {
        case "info":
            return "bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-900/30 text-blue-800 dark:text-blue-300";
        case "success":
            return "bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-900/30 text-emerald-800 dark:text-emerald-300";
        case "warning":
            return "bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-900/30 text-amber-800 dark:text-amber-300";
        case "danger":
            return "bg-rose-50 dark:bg-rose-900/10 border-rose-200 dark:border-rose-900/30 text-rose-800 dark:text-rose-300";
        default:
            return "bg-slate-50 dark:bg-slate-900/50 border-slate-200 dark:border-white/5 text-slate-800 dark:text-slate-300";
    }
};

const getIconColor = (type) => {
    switch (type) {
        case "info":
            return "text-blue-500 bg-blue-100 dark:bg-blue-500/20";
        case "success":
            return "text-emerald-500 bg-emerald-100 dark:bg-emerald-500/20";
        case "warning":
            return "text-amber-500 bg-amber-100 dark:bg-amber-500/20";
        case "danger":
            return "text-rose-500 bg-rose-100 dark:bg-rose-500/20";
        default:
            return "text-slate-500 bg-slate-100 dark:bg-slate-500/20";
    }
};
</script>

<template>
    <Head :title="__('notes')" />

    <AuthenticatedLayout>
        <template #header-title>{{ __("notes_management") }}</template>
        <template #header>{{ __("notes") }}</template>
        <template #header-subtitle>{{ __("manage_notes_desc") }}</template>


        <!-- Filters Section -->
        <div class="mb-8">
            <div
                class="flex flex-col sm:flex-row items-center gap-3 w-full"
            >

                <select
                    v-model="filterForm.type"
                    @change="applyFilters"
                    class="w-full sm:w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-amber-500 focus:border-amber-500 px-4 py-2.5 shadow-sm transition-all hover:border-slate-300 dark:hover:border-white/20"
                >
                    <option value="">{{ __("all_types") }}</option>
                    <option value="info">{{ __("info") }}</option>
                    <option value="success">{{ __("success") }}</option>
                    <option value="warning">{{ __("warning") }}</option>
                    <option value="danger">{{ __("danger") }}</option>
                </select>

                <input
                    type="date"
                    v-model="filterForm.date"
                    @change="applyFilters"
                    class="w-full sm:w-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-amber-500 focus:border-amber-500 px-4 py-2.5 shadow-sm transition-all hover:border-slate-300 dark:hover:border-white/20"
                />

                <button
                    v-if="filterForm.date || filterForm.type"
                    @click="clearFilters"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-900/30 text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/10 hover:bg-rose-100 dark:hover:bg-rose-900/20 text-xs font-black uppercase tracking-widest transition-all text-center"
                >
                    {{ __("clear_filters") }}
                </button>
            </div>
        </div>

        <div v-if="notes.length === 0" class="premium-shadow rounded-3xl p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 mx-auto max-w-lg mt-10">
            <div class="flex items-center justify-center w-20 h-20 mx-auto bg-amber-500/10 rounded-full mb-6 relative">
                <svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-white dark:bg-slate-900 rounded-full flex items-center justify-center">
                    <div class="w-6 h-6 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center">
                        <span class="text-xs font-black text-slate-400">0</span>
                    </div>
                </div>
            </div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ __("no_notes_found") }}</h3>
            <p class="text-sm text-slate-500 mt-2 font-medium max-w-[250px] mx-auto">{{ __("add_notes_desc") }}</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="note in notes"
                :key="note.id"
                class="rounded-3xl border p-6 transition-all shadow-sm hover:shadow-lg relative group"
                :class="getBackgroundColor(note.type)"
            >

                <div class="flex items-start gap-4 mb-4 pr-16">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="getIconColor(note.type)">
                         <span v-if="note.barber_id" class="text-sm font-black">{{ note.barber?.name?.charAt(0) }}</span>
                         <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                         </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-60">{{ __(note.type) }}</p>
                        <p class="text-sm font-bold mt-0.5">
                            {{ note.barber_id ? note.barber.name : __("shop_note") }}
                        </p>
                    </div>
                </div>
                
                <p class="text-sm font-medium opacity-80 mb-6 leading-relaxed whitespace-pre-line">{{ note.content }}</p>

                <div class="flex flex-col gap-2 border-t border-black/5 dark:border-white/10 pt-4 mt-auto">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-widest opacity-50">{{ __('created') }}</span>
                        <span class="text-xs font-bold opacity-80">
                            {{ new Date(note.created_at).toLocaleDateString(getLocale()) }}
                        </span>
                    </div>
                    <div v-if="note.date" class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-widest opacity-50">{{ __('for_date') }}</span>
                        <span class="text-[10px] font-bold opacity-80 px-2 py-0.5 rounded bg-black/5 dark:bg-white/10">
                            {{ new Date(note.date).toLocaleDateString(getLocale(), { timeZone: 'UTC' }) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>


    </AuthenticatedLayout>
</template>
