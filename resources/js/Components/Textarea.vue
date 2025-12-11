<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: String,
});

const emit = defineEmits(['update:modelValue']);

const value = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    },
});
</script>

<template>
    <textarea
        v-model="value"
        class="border-gray-300 focus:border-taller-blue-dark focus:ring-taller-blue-dark rounded-md shadow-sm"
        :class="{
            'border-red-300 focus:border-red-500 focus:ring-red-500': $attrs.error,
        }"
    ></textarea>
</template>
