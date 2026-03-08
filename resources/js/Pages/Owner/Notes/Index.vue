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

const isAddingNote = ref(false);
const editingNote = ref(null);

const form = useForm({
    content: "",
    type: "info",
    barber_id: "",
    date: "",
});

const filterForm = useForm({
    barber_id: props.filters.barber_id || "",
    date: props.filters.date || "",
    type: props.filters.type || "",
});

const applyFilters = () => {
    filterForm.get(route("owner.notes.index"), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.barber_id = "";
    filterForm.date = "";
    filterForm.type = "";
    applyFilters();
};

const openAddModal = () => {
    editingNote.value = null;
    form.reset();
    isAddingNote.value = true;
};

const openEditModal = (note) => {
    editingNote.value = note;
    form.content = note.content;
    form.type = note.type;
    form.barber_id = note.barber_id || "";
    form.date = note.date || "";
    isAddingNote.value = true;
};

const closeAddModal = () => {
    isAddingNote.value = false;
    form.reset();
    form.clearErrors();
};

const saveNote = () => {
    if (editingNote.value) {
        form.patch(route("owner.notes.update", editingNote.value.id), {
            onSuccess: () => {
                closeAddModal();
                Swal.fire({
                    icon: "success",
                    title: __("success"),
                    text: __("note_updated_successfully"),
                    timer: 1500,
                    showConfirmButton: false,
                });
            },
        });
    } else {
        form.post(route("owner.notes.store"), {
            onSuccess: () => {
                closeAddModal();
                Swal.fire({
                    icon: "success",
                    title: __("success"),
                    text: __("note_created_successfully"),
                    timer: 1500,
                    showConfirmButton: false,
                });
            },
        });
    }
};

const deleteNote = (note) => {
    Swal.fire({
        title: __("confirm_delete_note"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#64748b",
        confirmButtonText: __("delete"),
        cancelButtonText: __("cancel"),
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(route("owner.notes.destroy", note.id), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: "success",
                        title: __("success"),
                        timer: 1500,
                        showConfirmButton: false,
                    });
                },
            });
        }
    });
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
        <template #header-actions>
            <PrimaryButton
                @click="openAddModal"
                class="!rounded-xl !px-4 sm:!px-6 !py-2.5 sm:!py-3 shadow-lg shadow-rose-500/20 flex items-center gap-2 transition-transform active:scale-95 text-white"
            >
                <svg
                    class="w-4 h-4 sm:w-5 sm:h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                <span class="hidden sm:inline font-bold tracking-widest">{{
                    __("add_note")
                }}</span>
            </PrimaryButton>
        </template>

        <!-- Filters Section -->
        <div class="mb-8">
            <div
                class="flex flex-col sm:flex-row items-center gap-3 w-full"
            >
                <select
                    v-model="filterForm.barber_id"
                    @change="applyFilters"
                    class="w-full sm:w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-rose-500 focus:border-rose-500 px-4 py-2.5 shadow-sm transition-all hover:border-slate-300 dark:hover:border-white/20"
                >
                    <option value="">{{ __("all_stylists") }}</option>
                    <option
                        v-for="barber in barbers"
                        :key="barber.id"
                        :value="barber.id"
                    >
                        {{ barber.name }}
                    </option>
                </select>

                <select
                    v-model="filterForm.type"
                    @change="applyFilters"
                    class="w-full sm:w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-rose-500 focus:border-rose-500 px-4 py-2.5 shadow-sm transition-all hover:border-slate-300 dark:hover:border-white/20"
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
                    class="w-full sm:w-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 focus:ring-rose-500 focus:border-rose-500 px-4 py-2.5 shadow-sm transition-all hover:border-slate-300 dark:hover:border-white/20"
                />

                <button
                    v-if="filterForm.barber_id || filterForm.date || filterForm.type"
                    @click="clearFilters"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-900/30 text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/10 hover:bg-rose-100 dark:hover:bg-rose-900/20 text-xs font-black uppercase tracking-widest transition-all text-center"
                >
                    {{ __("clear_filters") }}
                </button>
            </div>
        </div>

        <div v-if="notes.length === 0" class="premium-shadow rounded-3xl p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 mx-auto max-w-lg mt-10">
            <div class="flex items-center justify-center w-20 h-20 mx-auto bg-rose-500/10 rounded-full mb-6 relative">
                <svg class="w-10 h-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <PrimaryButton @click="openAddModal" class="mt-8 shadow-lg shadow-rose-500/20 hover:-translate-y-0.5 transition-all w-full justify-center !py-3 bg-rose-500 text-white">
                {{ __("add_your_first_note") }}
            </PrimaryButton>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="note in notes"
                :key="note.id"
                class="rounded-3xl border p-6 transition-all shadow-sm hover:shadow-lg relative group"
                :class="getBackgroundColor(note.type)"
            >
                <!-- Actions Dropdown -->
                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    <div class="flex space-x-1">
                        <button
                            @click="openEditModal(note)"
                            class="p-1.5 rounded-lg bg-white/50 dark:bg-black/20 hover:bg-white dark:hover:bg-slate-800 text-slate-500 hover:text-indigo-500 transition-all border border-transparent hover:border-slate-200 dark:hover:border-white/10 shadow-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button
                            @click="deleteNote(note)"
                            class="p-1.5 rounded-lg bg-white/50 dark:bg-black/20 hover:bg-white dark:hover:bg-slate-800 text-slate-500 hover:text-rose-500 transition-all border border-transparent hover:border-slate-200 dark:hover:border-white/10 shadow-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>

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

        <Modal :show="isAddingNote" @close="closeAddModal">
            <div class="p-6 sm:p-8 bg-white dark:bg-slate-900 rounded-[2rem]">
                <div class="flex items-start justify-between mb-8">
                    <div class="flex flex-col">
                        <h2 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                            {{ editingNote ? __("edit_note") : __("add_note") }}
                        </h2>
                        <div class="h-1 text-white bg-rose-500 rounded-full mt-2 self-start w-12"></div>
                    </div>
                    <button @click="closeAddModal" class="p-2 bg-slate-100 dark:bg-white/5 rounded-xl hover:bg-slate-200 dark:hover:bg-white/10 transition-colors">
                        <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="saveNote" class="space-y-6">
                    <div>
                        <InputLabel :value="__('type')" />
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
                            <button
                                type="button"
                                @click="form.type = 'info'"
                                :class="form.type === 'info' ? 'bg-blue-500 text-white ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="px-4 py-3 rounded-xl border border-transparent font-bold text-xs uppercase tracking-widest transition-all"
                            >
                                {{ __("info") }}
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'success'"
                                :class="form.type === 'success' ? 'bg-emerald-500 text-white ring-2 ring-emerald-500 ring-offset-2 dark:ring-offset-slate-900' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="px-4 py-3 rounded-xl border border-transparent font-bold text-xs uppercase tracking-widest transition-all"
                            >
                                {{ __("success") }}
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'warning'"
                                :class="form.type === 'warning' ? 'bg-orange-500 text-white ring-2 ring-orange-500 ring-offset-2 dark:ring-offset-slate-900' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="px-4 py-3 rounded-xl border border-transparent font-bold text-xs uppercase tracking-widest transition-all"
                            >
                                {{ __("warning") }}
                            </button>
                            <button
                                type="button"
                                @click="form.type = 'danger'"
                                :class="form.type === 'danger' ? 'bg-rose-500 text-white ring-2 ring-rose-500 ring-offset-2 dark:ring-offset-slate-900' : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="px-4 py-3 rounded-xl border border-transparent font-bold text-xs uppercase tracking-widest transition-all"
                            >
                                {{ __("danger") }}
                            </button>
                        </div>
                        <InputError :message="form.errors.type" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel :value="__('assigned_stylist')" />
                            <select
                                v-model="form.barber_id"
                                class="mt-2 block w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white focus:ring-rose-500 focus:border-rose-500 transition-all font-medium py-3"
                            >
                                <option value="">{{ __("shop_note") }}</option>
                                <option
                                    v-for="barber in barbers"
                                    :key="barber.id"
                                    :value="barber.id"
                                >
                                    {{ barber.name }}
                                </option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-widest font-bold">{{ __("leave_empty_for_shop") }}</p>
                            <InputError :message="form.errors.barber_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel :value="__('for_date')" />
                            <input
                                type="date"
                                v-model="form.date"
                                class="mt-2 block w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-xl text-slate-900 dark:text-white focus:ring-rose-500 focus:border-rose-500 transition-all font-medium py-3"
                            />
                            <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-widest font-bold">{{ __("optional") }}</p>
                            <InputError :message="form.errors.date" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <InputLabel :value="__('note_content')" />
                        <textarea
                            v-model="form.content"
                            rows="4"
                            class="mt-2 block w-full bg-slate-50 dark:bg-black/20 border-slate-200 dark:border-white/10 rounded-xl shadow-sm text-slate-900 dark:text-white focus:ring-rose-500 focus:border-rose-500 sm:text-sm resize-none transition-all p-4"
                            :placeholder="__('enter_note_content')"
                        ></textarea>
                        <InputError :message="form.errors.content" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-8 gap-3">
                        <SecondaryButton @click="closeAddModal" class="!px-6 !py-3 !rounded-xl">
                            {{ __("cancel") }}
                        </SecondaryButton>
                        <PrimaryButton
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            class="!px-8 !py-3 !rounded-xl shadow-lg shadow-rose-500/20 text-white bg-rose-500"
                        >
                            {{ editingNote ? __("save") : __("add_note") }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
