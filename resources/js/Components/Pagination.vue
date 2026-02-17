<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: Array,
});

const filteredLinks = computed(() => {
    return props.links.map(link => {
        // Clean up text for "Next" and "Previous" if they contain HTML entities
        let label = link.label;
        if (label.includes('Next')) label = '»';
        if (label.includes('Previous')) label = '«';
        return { ...link, label };
    });
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap gap-2 justify-center mt-8">
            <template v-for="(link, key) in filteredLinks" :key="key">
                <div
                    v-if="link.url === null"
                    class="px-5 py-3 text-xs font-black uppercase tracking-widest text-slate-400 bg-slate-100/50 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/5 cursor-not-allowed"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="px-5 py-3 text-xs font-black uppercase tracking-widest transition-all rounded-2xl border border-slate-200 dark:border-white/5 premium-shadow active:scale-95"
                    :class="{ 'bg-amber-500 text-slate-900 border-amber-500 shadow-lg shadow-amber-500/20': link.active, 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5': !link.active }"
                    :href="link.url"
                    v-html="link.label"
                    preserve-scroll
                />
            </template>
        </div>
    </div>
</template>
