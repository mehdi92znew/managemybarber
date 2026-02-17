<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-2.5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-white/5 dark:text-slate-200 dark:hover:bg-slate-700 transition-all duration-300 premium-shadow disabled:opacity-25"
    >
        <slot />
    </button>
</template>
