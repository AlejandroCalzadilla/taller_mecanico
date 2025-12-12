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
        class="rounded-md shadow-sm transition duration-200"
        :style="{ 
            borderColor: 'var(--color-border)',
            '--focus-border': 'var(--color-primary)',
            '--focus-ring': 'var(--color-primary)',
            color: 'var(--color-text)',
        }"
        v-model="model"
        ref="input"
    />
</template>

<style scoped>
input:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(var(--color-primary), 0.1);
}
</style>