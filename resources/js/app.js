import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { translations } from "./lang";
import { usePage } from "@inertiajs/vue3";

import { __ } from "./translate";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

const translatePlugin = {
    install(app) {
        app.config.globalProperties.__ = __;
        app.provide("__", __);
    },
};

const appElement = document.getElementById("app");

if (appElement) {
    createInertiaApp({
        title: (title) => `${title} - ${appName}`,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob("./Pages/**/*.vue"),
            ),
        setup({ el, App, props, plugin }) {
            const app = createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .use(translatePlugin);

            return app.mount(el);
        },
        progress: {
            color: "#4B5563",
        },
    });
}

// Hybrid Vue Components for Blade Views
import BarberManager from "./Components/BarberManager.vue";

const barberManagerEl = document.getElementById("barber-manager");
if (barberManagerEl) {
    const app = createApp(BarberManager, {
        initialBarbers: JSON.parse(barberManagerEl.dataset.barbers),
    });
    app.use(ZiggyVue);
    app.use(translatePlugin);
    app.mount("#barber-manager");
}

// Service Manager
import ServiceManager from "./Components/ServiceManager.vue";
const serviceManagerEl = document.getElementById("service-manager");
if (serviceManagerEl) {
    const app = createApp(ServiceManager, {
        initialServices: JSON.parse(serviceManagerEl.dataset.services),
    });
    app.use(ZiggyVue);
    app.use(translatePlugin);
    app.mount("#service-manager");
}

// Customer Manager
import CustomerManager from "./Components/CustomerManager.vue";
const customerManagerEl = document.getElementById("customer-manager");
if (customerManagerEl) {
    const app = createApp(CustomerManager, {
        initialCustomers: JSON.parse(customerManagerEl.dataset.customers),
    });
    app.use(ZiggyVue);
    app.use(translatePlugin);
    app.mount("#customer-manager");
}

// Calendar App
import CalendarView from "./Components/CalendarView.vue";
const calendarAppEl = document.getElementById("calendar-app");
if (calendarAppEl) {
    const app = createApp(CalendarView, {
        initialBarbers: JSON.parse(calendarAppEl.dataset.barbers),
        initialServices: JSON.parse(calendarAppEl.dataset.services),
    });
    app.use(ZiggyVue);
    app.use(translatePlugin);
    app.mount("#calendar-app");
}

const barberCalendarAppEl = document.getElementById("barber-calendar-app");
if (barberCalendarAppEl) {
    const app = createApp(CalendarView, {
        initialBarbers: JSON.parse(barberCalendarAppEl.dataset.barbers),
        initialServices: JSON.parse(barberCalendarAppEl.dataset.services),
    });
    app.use(ZiggyVue);
    app.use(translatePlugin);
    app.mount("#barber-calendar-app");
}
