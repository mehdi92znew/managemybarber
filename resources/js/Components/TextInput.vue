<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-slate-200 bg-white/50 backdrop-blur-sm px-4 py-3 text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500 dark:border-white/5 dark:bg-black/20 dark:text-white transition-all duration-300"
        v-model="model"
        ref="input"
    />
</template>
