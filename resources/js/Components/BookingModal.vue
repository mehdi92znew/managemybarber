<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
import { trans } from "../lang";
import Swal from "sweetalert2";

const route = window.route;

const props = defineProps({
    show: Boolean,
    barbers: {
        type: Array,
        default: () => [],
    },
    services: Array,
    initialDate: [String, Object],
    initialBarberId: [Number, String],
    initialAppointment: {
        type: Object,
        default: null,
    },
    isBarberView: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    "close",
    "appointment-created",
    "appointment-updated",
]);

const pageProps = usePage();
const currentUser = pageProps.props.auth?.user;

const form = useForm({
    barber_id: props.isBarberView
        ? currentUser?.id
        : props.initialBarberId || "",
    customer_id: null,
    new_customer_name: "",
    new_customer_phone: "",
    service_ids: [],
    start_time: "",
    notes: "",
    total_price: 0,
    total_duration: 0,
    status: "scheduled",
    payment_status: "unpaid",
});

const isManualPrice = ref(false);
const isManualDuration = ref(false);

const customerSearch = ref("");
const searchResults = ref([]);
const isSearching = ref(false);
const showNewCustomerFields = ref(false);

watch(
    () => props.initialAppointment,
    (appt) => {
        if (appt) {
            form.barber_id =
                appt.extendedProps?.barber_id || appt.barber_id || "";
            form.customer_id =
                appt.extendedProps?.customer_id || appt.customer_id || null;
            form.service_ids = (
                appt.extendedProps?.services ||
                appt.services ||
                []
            ).map((s) => s.id || s);
            const d = new Date(appt.start);
            const pad = (n) => n.toString().padStart(2, "0");
            form.start_time = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
            form.notes = appt.extendedProps?.notes || appt.notes || "";
            customerSearch.value =
                appt.extendedProps?.customer_name || appt.customer?.name || "";
            form.total_price =
                appt.extendedProps?.total_price || appt.total_price || 0;
            form.status =
                appt.extendedProps?.status || appt.status || "scheduled";
            form.payment_status =
                appt.extendedProps?.payment_status ||
                appt.payment_status ||
                "unpaid";
            if (appt.end && appt.start) {
                const end = new Date(appt.end);
                const start = new Date(appt.start);
                form.total_duration = Math.round((end - start) / 60000);
            } else {
                form.total_duration = 0;
            }
            isManualPrice.value = true;
            isManualDuration.value = true;
        } else {
            form.reset();
            customerSearch.value = "";
            isManualPrice.value = false;
            isManualDuration.value = false;
        }
    },
    { immediate: true },
);

watch(
    () => props.initialDate,
    (val) => {
        if (val && typeof val === "object" && val.start) {
            let iso = val.start;
            // Check if string contains 'T', if not append default time
            if (iso.indexOf("T") === -1) {
                form.start_time = iso + "T09:00";
            } else {
                form.start_time = iso.slice(0, 16);
            }
        } else if (val) {
            // Ensure format is compatible with datetime-local (YYYY-MM-DDTHH:mm)
            if (typeof val === "string" && val.length > 16) {
                form.start_time = val.slice(0, 16);
            } else {
                form.start_time = val;
            }
        }
    },
    { immediate: true },
);

watch(
    () => props.initialBarberId,
    (val) => {
        if (!props.isBarberView) form.barber_id = val || "";
    },
    { immediate: true },
);

// Sync form values when modal is shown to handle cases where props didn't change but form was reset
watch(
    () => props.show,
    (isVisible) => {
        if (isVisible) {
            if (props.initialBarberId && !props.isBarberView) {
                form.barber_id = props.initialBarberId;
            }
            if (props.initialDate) {
                if (
                    typeof props.initialDate === "object" &&
                    props.initialDate.start
                ) {
                    form.start_time = props.initialDate.start.slice(0, 16);
                } else {
                    form.start_time = props.initialDate.slice(0, 16);
                }
            }
        }
    },
);

const serviceSearch = ref("");
const isServiceDropdownOpen = ref(false);

const serviceSearchInput = ref(null);

const openServiceDropdown = () => {
    isServiceDropdownOpen.value = true;
    setTimeout(() => {
        serviceSearchInput.value?.focus();
    }, 100);
};

const filteredServices = computed(() => {
    if (!serviceSearch.value) return props.services;
    const search = serviceSearch.value.toLowerCase();
    return props.services.filter((s) => s.name.toLowerCase().includes(search));
});

const selectedServices = computed(() => {
    return props.services.filter((s) => form.service_ids.includes(s.id));
});

watch(
    () => form.service_ids,
    (newIds) => {
        if (!isManualDuration.value) {
            form.total_duration = props.services
                .filter((s) => newIds.includes(s.id))
                .reduce((sum, s) => sum + parseInt(s.duration_minutes), 0);
        }
        if (!isManualPrice.value) {
            form.total_price = props.services
                .filter((s) => newIds.includes(s.id))
                .reduce((sum, s) => sum + parseFloat(s.price), 0);
        }
    },
    { deep: true },
);

const toggleService = (serviceId) => {
    const index = form.service_ids.indexOf(serviceId);
    if (index === -1) {
        form.service_ids.push(serviceId);
    } else {
        form.service_ids.splice(index, 1);
    }
};

const serviceContainer = ref(null);
const handleClickOutside = (event) => {
    if (
        serviceContainer.value &&
        !serviceContainer.value.contains(event.target)
    ) {
        isServiceDropdownOpen.value = false;
    }
};

import { onMounted, onUnmounted } from "vue";
onMounted(() => {
    document.addEventListener("mousedown", handleClickOutside);
});
onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
});

const handleCustomerSearch = async () => {
    if (customerSearch.value.length < 2) {
        searchResults.value = [];
        return;
    }
    isSearching.value = true;
    try {
        const routeName = props.isBarberView
            ? "barber.customers.index"
            : "owner.customers.index";
        const response = await axios.get(route(routeName), {
            params: { search: customerSearch.value },
            headers: { Accept: "application/json" },
        });
        searchResults.value = response.data;
    } catch (e) {
        console.error(e);
        searchResults.value = [];
    }
    isSearching.value = false;
};

const clearCustomerSelection = () => {
    form.customer_id = null;
    customerSearch.value = "";
    searchResults.value = [];
};

const selectCustomer = (customer) => {
    form.customer_id = customer.id;
    customerSearch.value = customer.name;
    searchResults.value = [];
};

const buttonText = computed(() => {
    const locale = pageProps.props.locale || "en";
    if (form.processing) return trans("booking_progress", locale);
    return props.initialAppointment
        ? trans("save", locale)
        : trans("confirm_booking", locale);
});

const submit = () => {
    form.processing = true;
    if (props.initialAppointment) {
        const routeName = props.isBarberView
            ? "barber.appointments.update"
            : "owner.appointments.update";

        // Prepare data to send
        const dataToSend = { ...form.data() };
        dataToSend.total_duration = form.total_duration;
        dataToSend.total_price = form.total_price;

        axios
            .patch(route(routeName, props.initialAppointment.id), dataToSend)
            .then((response) => {
                emit("appointment-updated", response.data.appointment);
                close();
                const locale = pageProps.props.locale || "en";
                Swal.fire({
                    icon: "success",
                    title: trans("success", locale) || "Succès!",
                    text: response.data?.message || "Appointment updated.",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        container: "z-[99999]",
                    },
                });
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    // Check if it's a custom Exception thrown by PHP (no 'errors' array, just 'message')
                    if (
                        error.response.data.message &&
                        !error.response.data.errors
                    ) {
                        const locale = pageProps.props.locale || "en";
                        let errorMsg = error.response.data.message;

                        if (
                            errorMsg ===
                            "This time slot is already booked for the selected barber."
                        ) {
                            errorMsg =
                                trans(
                                    "error_time_slot_booked_barber",
                                    locale,
                                ) || errorMsg;
                        } else if (
                            errorMsg === "This time slot is already booked."
                        ) {
                            errorMsg =
                                trans("error_time_slot_booked", locale) ||
                                errorMsg;
                        }

                        Swal.fire({
                            target: document.getElementById(
                                "bookingModalDialog",
                            ),
                            icon: "error",
                            title: trans("error", locale) || "Erreur!",
                            text: errorMsg,
                            customClass: {
                                container: "z-[99999]",
                            },
                        });
                        return;
                    }

                    const errors = error.response.data.errors;
                    const flatErrors = {};
                    const locale = pageProps.props.locale || "en";

                    for (const key in errors) {
                        let msg = Array.isArray(errors[key])
                            ? errors[key][0]
                            : errors[key];

                        if (key === "barber_id" && msg.includes("required"))
                            msg = trans("error_barber_required", locale);
                        if (key === "service_ids" && msg.includes("required"))
                            msg = trans("error_service_required", locale);
                        if (key === "start_time" && msg.includes("required"))
                            msg = trans("error_start_time_required", locale);
                        if (
                            key === "new_customer_name" &&
                            msg.includes("required")
                        )
                            msg = trans("error_customer_name_required", locale);

                        flatErrors[key] = msg;
                    }

                    form.errors = flatErrors;
                    const firstErrorMsg = Object.values(flatErrors)[0];

                    Swal.fire({
                        target: document.getElementById("bookingModalDialog"),
                        icon: "error",
                        title: trans("error", locale) || "Erreur!",
                        text:
                            firstErrorMsg ||
                            trans("error_occurred", locale) ||
                            "Veuillez vérifier les champs du formulaire et réessayer.",
                        customClass: {
                            container: "z-[99999]",
                        },
                    });
                } else {
                    const locale = pageProps.props.locale || "en";
                    Swal.fire({
                        target: document.getElementById("bookingModalDialog"),
                        icon: "error",
                        title: trans("error", locale) || "Erreur!",
                        text:
                            error.response?.data?.message ||
                            trans("error_occurred", locale),
                        customClass: {
                            container: "z-[99999]",
                        },
                    });
                }
            })
            .finally(() => {
                form.processing = false;
            });
    } else {
        const endpoint = props.isBarberView
            ? route("barber.appointments.store")
            : route("owner.appointments.store");

        // Prepare data for creation as well
        const dataToSend = { ...form.data() };
        dataToSend.total_duration = form.total_duration;
        dataToSend.total_price = form.total_price;

        axios
            .post(endpoint, dataToSend)
            .then((response) => {
                emit("appointment-created", response.data.appointment);
                close();
                const locale = pageProps.props.locale || "en";
                Swal.fire({
                    icon: "success",
                    title: trans("success", locale) || "Succès!",
                    text: response.data?.message || "Appointment booked.",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        container: "z-[99999]",
                    },
                });
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    // Check if it's a custom Exception thrown by PHP (no 'errors' array, just 'message')
                    if (
                        error.response.data.message &&
                        !error.response.data.errors
                    ) {
                        const locale = pageProps.props.locale || "en";
                        let errorMsg = error.response.data.message;

                        if (
                            errorMsg ===
                            "This time slot is already booked for the selected barber."
                        ) {
                            errorMsg =
                                trans(
                                    "error_time_slot_booked_barber",
                                    locale,
                                ) || errorMsg;
                        } else if (
                            errorMsg === "This time slot is already booked."
                        ) {
                            errorMsg =
                                trans("error_time_slot_booked", locale) ||
                                errorMsg;
                        }

                        Swal.fire({
                            target: document.getElementById(
                                "bookingModalDialog",
                            ),
                            icon: "error",
                            title: trans("error", locale) || "Erreur!",
                            text: errorMsg,
                            customClass: {
                                container: "z-[99999]",
                            },
                        });
                        return;
                    }

                    const errors = error.response.data.errors;
                    const flatErrors = {};
                    const locale = pageProps.props.locale || "en";

                    for (const key in errors) {
                        let msg = Array.isArray(errors[key])
                            ? errors[key][0]
                            : errors[key];

                        if (key === "barber_id" && msg.includes("required"))
                            msg = trans("error_barber_required", locale);
                        if (key === "service_ids" && msg.includes("required"))
                            msg = trans("error_service_required", locale);
                        if (key === "start_time" && msg.includes("required"))
                            msg = trans("error_start_time_required", locale);
                        if (
                            key === "new_customer_name" &&
                            msg.includes("required")
                        )
                            msg = trans("error_customer_name_required", locale);

                        flatErrors[key] = msg;
                    }

                    form.errors = flatErrors;
                    const firstErrorMsg = Object.values(flatErrors)[0];

                    Swal.fire({
                        target: document.getElementById("bookingModalDialog"),
                        icon: "error",
                        title: trans("error", locale) || "Erreur!",
                        text:
                            firstErrorMsg ||
                            trans("error_occurred", locale) ||
                            "Veuillez vérifier les champs du formulaire et réessayer.",
                        customClass: {
                            container: "z-[99999]",
                        },
                    });
                } else {
                    const locale = pageProps.props.locale || "en";
                    Swal.fire({
                        target: document.getElementById("bookingModalDialog"),
                        icon: "error",
                        title: trans("error", locale) || "Erreur!",
                        text:
                            error.response?.data?.message ||
                            trans("error_occurred", locale),
                        customClass: {
                            container: "z-[99999]",
                        },
                    });
                }
            })
            .finally(() => {
                form.processing = false;
            });
    }
};

const formatCurrency = (value) => {
    let locale = "en-US";
    const currentLocale = pageProps.props.locale || "en";
    if (currentLocale === "fr" || currentLocale === "ar") locale = "fr-FR";
    return new Intl.NumberFormat(locale, {
        style: "currency",
        currency: "USD",
        currencyDisplay: "narrowSymbol",
    }).format(value || 0);
};

const close = () => {
    emit("close");
    form.reset();
    form.clearErrors();
    customerSearch.value = "";
    showNewCustomerFields.value = false;
};

const deleteAppointment = () => {
    const locale = pageProps.props.locale || "en";
    if (
        !confirm(
            trans("confirm_delete_appointment", locale) ||
                "Are you sure you want to delete this appointment?",
        )
    )
        return;

    const routeName = props.isBarberView
        ? "barber.appointments.destroy"
        : "owner.appointments.destroy";

    axios
        .delete(route(routeName, props.initialAppointment.id))
        .then(() => {
            emit("appointment-updated");
            close();
            Swal.fire({
                icon: "success",
                title: trans("success", locale) || "Success",
                text: "Appointment deleted.",
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    container: "z-[99999]",
                },
            });
        })
        .catch((error) => {
            Swal.fire({
                target: document.getElementById("bookingModalDialog"),
                icon: "error",
                title: trans("error", locale) || "Error",
                text:
                    error.response?.data?.message ||
                    "Failed to delete appointment.",
                customClass: {
                    container: "z-[99999]",
                },
            });
        });
};
</script>

<template>
    <Modal id="bookingModalDialog" :show="show" @close="close">
        <div class="p-4 sm:p-8 bg-white dark:bg-slate-900">
            <div class="flex items-center justify-between mb-6 sm:mb-8">
                <h2
                    class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-3"
                >
                    <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
                        <svg
                            class="h-5 w-5 sm:h-6 sm:w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                    </div>
                    {{ __("book_appointment") }}
                </h2>
                <button
                    @click="close"
                    class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="space-y-5 sm:space-y-6">
                <!-- Barber Selection -->
                <div
                    v-if="!isBarberView"
                    class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                    >
                        {{ __("barber") }}
                    </p>
                    <select
                        id="barber"
                        v-model="form.barber_id"
                        class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                    >
                        <option value="" disabled>
                            {{ __("select_barber") }}
                        </option>
                        <option
                            v-for="barber in barbers"
                            :key="barber.id"
                            :value="barber.id"
                        >
                            {{ barber.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.barber_id" class="mt-2" />
                </div>

                <!-- Start Time -->
                <div
                    class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                    >
                        {{ __("start_time") }}
                    </p>
                    <input
                        id="start_time"
                        v-model="form.start_time"
                        type="datetime-local"
                        class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm appearance-none"
                    />
                    <InputError
                        :message="form.errors.start_time"
                        class="mt-2"
                    />
                </div>

                <!-- Customer Selection -->
                <div
                    class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                >
                    <div class="flex items-center justify-between mb-2">
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400"
                        >
                            {{ __("customer") }}
                        </p>
                        <button
                            type="button"
                            @click="
                                showNewCustomerFields = !showNewCustomerFields
                            "
                            class="text-[10px] font-black uppercase tracking-widest text-amber-500 hover:text-amber-600 transition-colors"
                        >
                            {{
                                showNewCustomerFields
                                    ? __("select_existing")
                                    : __("create_new")
                            }}
                        </button>
                    </div>

                    <div v-if="!showNewCustomerFields" class="relative">
                        <div class="relative group">
                            <input
                                v-model="customerSearch"
                                @input="handleCustomerSearch"
                                type="text"
                                class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm placeholder:text-slate-300 dark:placeholder:text-slate-600"
                                :placeholder="__('search_placeholder_short')"
                                autocomplete="off"
                            />
                            <div
                                v-if="isSearching"
                                class="absolute right-0 top-0"
                            >
                                <svg
                                    class="animate-spin h-4 w-4 text-amber-500"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                            </div>
                        </div>

                        <ul
                            v-if="searchResults.length > 0"
                            class="absolute z-50 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-2xl mt-4 max-h-60 overflow-y-auto shadow-2xl premium-shadow"
                        >
                            <li
                                v-for="customer in searchResults"
                                :key="customer.id"
                                @click="selectCustomer(customer)"
                                class="px-5 py-4 hover:bg-slate-50 dark:hover:bg-white/5 cursor-pointer border-b border-slate-100 dark:border-white/5 last:border-0 transition-colors"
                            >
                                <div
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    {{ customer.name }}
                                </div>
                                <div
                                    class="text-[10px] font-medium text-slate-500 mt-0.5 tracking-wider"
                                >
                                    {{ customer.phone }}
                                </div>
                            </li>
                        </ul>

                        <div
                            v-if="form.customer_id"
                            class="mt-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex justify-between items-center text-xs text-emerald-700 dark:text-emerald-400"
                        >
                            <span class="font-bold flex items-center gap-2">
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    />
                                </svg>
                                {{ customerSearch }}
                            </span>
                            <button
                                type="button"
                                @click="clearCustomerSelection"
                                class="text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 transition-colors"
                            >
                                {{ __("change") }}
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2"
                    >
                        <div
                            class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5"
                        >
                            <p
                                class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1"
                            >
                                {{ __("customer_name") }}
                            </p>
                            <input
                                v-model="form.new_customer_name"
                                type="text"
                                class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-xs"
                                :placeholder="__('name_placeholder')"
                            />
                        </div>
                        <div
                            class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5"
                        >
                            <p
                                class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1"
                            >
                                {{ __("phone_number") }}
                            </p>
                            <input
                                v-model="form.new_customer_phone"
                                type="text"
                                class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-xs"
                                :placeholder="__('phone_placeholder')"
                            />
                        </div>
                    </div>
                </div>

                <!-- Service Selection (Select2 style) -->
                <div
                    ref="serviceContainer"
                    class="p-3 sm:p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 relative"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                    >
                        {{ __("services") }}
                    </p>

                    <div class="relative">
                        <!-- Selection Area / Dropdown Trigger -->
                        <div
                            @click="openServiceDropdown"
                            class="min-h-[46px] p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 flex flex-wrap gap-2 items-center cursor-pointer hover:border-amber-500/30 transition-all focus-within:ring-2 focus-within:ring-amber-500/20"
                        >
                            <div
                                v-if="
                                    selectedServices.length === 0 &&
                                    !isServiceDropdownOpen
                                "
                                class="text-xs text-slate-400 px-2 italic"
                            >
                                {{ __("select_services") }}
                            </div>

                            <div
                                v-for="service in selectedServices"
                                :key="service.id"
                                class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-amber-500/10 text-amber-600 border border-amber-500/20 animate-in fade-in zoom-in duration-200"
                            >
                                <span
                                    class="text-[10px] font-black uppercase tracking-wider"
                                    >{{ service.name }}</span
                                >
                                <button
                                    @click.stop="toggleService(service.id)"
                                    class="p-0.5 hover:bg-amber-500/20 rounded-md transition-colors"
                                >
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Inline Search Input (Select2 style) -->
                            <input
                                v-if="
                                    isServiceDropdownOpen ||
                                    selectedServices.length === 0
                                "
                                ref="serviceSearchInput"
                                v-model="serviceSearch"
                                @click.stop="isServiceDropdownOpen = true"
                                type="text"
                                class="flex-1 min-w-[120px] bg-transparent border-none text-xs font-bold text-slate-900 dark:text-white focus:ring-0 p-1"
                                :placeholder="
                                    selectedServices.length > 0
                                        ? ''
                                        : __('search_services')
                                "
                                autocomplete="off"
                            />

                            <div class="ml-auto px-1">
                                <svg
                                    class="w-4 h-4 text-slate-400 transition-transform duration-300"
                                    :class="{
                                        'rotate-180': isServiceDropdownOpen,
                                    }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown Menu (Optimized for Mobile) -->
                        <div
                            v-if="isServiceDropdownOpen"
                            class="absolute z-[100] left-0 right-0 mt-2 p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.5)] animate-in slide-in-from-top-2 duration-300"
                        >
                            <div
                                class="max-h-56 sm:max-h-48 overflow-y-auto no-scrollbar space-y-1"
                            >
                                <div
                                    v-for="service in filteredServices"
                                    :key="service.id"
                                    @click.stop="toggleService(service.id)"
                                    class="flex items-center justify-between p-3.5 sm:p-3 rounded-xl cursor-pointer transition-all active:scale-[0.98]"
                                    :class="
                                        form.service_ids.includes(service.id)
                                            ? 'bg-amber-500/10 border-amber-500/30'
                                            : 'hover:bg-slate-50 dark:hover:bg-white/5'
                                    "
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-5 h-5 sm:w-4 sm:h-4 rounded border flex items-center justify-center transition-all"
                                            :class="
                                                form.service_ids.includes(
                                                    service.id,
                                                )
                                                    ? 'bg-amber-500 border-amber-500'
                                                    : 'border-slate-300 dark:border-slate-700'
                                            "
                                        >
                                            <svg
                                                v-if="
                                                    form.service_ids.includes(
                                                        service.id,
                                                    )
                                                "
                                                class="w-3 h-3 text-slate-900"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="3"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs sm:text-xs font-bold text-slate-900 dark:text-white"
                                            >
                                                {{ service.name }}
                                            </p>
                                            <p
                                                class="text-[10px] font-medium text-slate-500 tracking-wider"
                                            >
                                                {{ service.duration_minutes }}m
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="text-xs font-black text-amber-600 dark:text-amber-500"
                                        >{{
                                            formatCurrency(service.price)
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="filteredServices.length === 0"
                                    class="p-8 text-center"
                                >
                                    <p
                                        class="text-xs text-slate-400 font-bold italic"
                                    >
                                        {{ __("no_results_found") }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <InputError
                        :message="form.errors.service_ids"
                        class="mt-2"
                    />
                </div>

                <!-- Notes -->
                <div
                    class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                >
                    <p
                        class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                    >
                        {{ __("notes") }}
                    </p>
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm placeholder:text-slate-300 dark:placeholder:text-slate-600 resize-none"
                        :placeholder="__('add_optional_note')"
                    ></textarea>
                </div>

                <!-- Status & Payment Status -->
                <div
                    v-if="initialAppointment"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                >
                    <div
                        class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                        >
                            {{ __("status") }}
                        </p>
                        <select
                            v-model="form.status"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                        >
                            <option value="scheduled">
                                {{ __("scheduled") }}
                            </option>
                            <option value="completed">
                                {{ __("completed") }}
                            </option>
                            <option value="cancelled">
                                {{ __("cancelled") }}
                            </option>
                        </select>
                    </div>
                    <div
                        class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2"
                        >
                            {{ __("payment_status") }}
                        </p>
                        <select
                            v-model="form.payment_status"
                            class="w-full border-none bg-transparent font-bold text-slate-900 dark:text-white focus:ring-0 p-0 text-sm"
                        >
                            <option value="unpaid">{{ __("unpaid") }}</option>
                            <option value="semi-paid">
                                {{ __("semi_paid") }}
                            </option>
                            <option value="paid">{{ __("paid") }}</option>
                        </select>
                    </div>
                </div>

                <!-- Summary -->
                <div
                    class="flex flex-col sm:flex-row gap-4 items-center justify-between p-5 rounded-3xl bg-slate-900 dark:bg-amber-500 shadow-xl"
                >
                    <div class="flex items-center gap-6">
                        <div class="text-center sm:text-left">
                            <p
                                class="text-[9px] font-black uppercase tracking-widest text-white/40 dark:text-slate-900/50 mb-0.5"
                            >
                                {{ __("duration_label") }}
                            </p>
                            <div
                                class="flex items-center gap-1 justify-center sm:justify-start"
                            >
                                <input
                                    v-model="form.total_duration"
                                    type="number"
                                    step="1"
                                    min="1"
                                    @input="isManualDuration = true"
                                    class="w-14 sm:w-16 bg-transparent border-none p-0 text-sm font-black text-white dark:text-slate-900 focus:ring-0 text-right sm:text-left"
                                />
                                <span
                                    class="text-sm font-black text-white dark:text-slate-900"
                                    >{{ __("mins_short") }}</span
                                >
                            </div>
                        </div>
                        <div
                            class="w-px h-8 bg-white/10 dark:bg-slate-900/10"
                        ></div>
                        <div class="text-center sm:text-left">
                            <p
                                class="text-[9px] font-black uppercase tracking-widest text-white/40 dark:text-slate-900/50 mb-0.5"
                            >
                                {{ __("total_label") }}
                            </p>
                            <input
                                v-model="form.total_price"
                                type="number"
                                step="0.01"
                                @input="isManualPrice = true"
                                class="w-24 bg-transparent border-none p-0 text-sm font-black text-white dark:text-slate-900 focus:ring-0"
                            />
                        </div>
                    </div>

                    <div
                        class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto mt-4 sm:mt-0"
                    >
                        <button
                            v-if="initialAppointment"
                            @click="deleteAppointment"
                            class="w-full sm:w-auto px-6 py-4 rounded-2xl bg-red-500/10 text-red-500 hover:bg-red-500/20 text-xs font-black uppercase tracking-widest transition-all"
                        >
                            {{ __("delete") }}
                        </button>
                        <button
                            @click="submit"
                            class="w-full sm:w-auto px-10 py-4 rounded-2xl bg-amber-500 dark:bg-slate-900 text-slate-900 dark:text-amber-500 text-xs font-black uppercase tracking-[0.2em] shadow-lg shadow-amber-500/20 active:scale-95 transition-all disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ buttonText }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style>
.swal2-container {
    z-index: 100000 !important;
}
</style>
