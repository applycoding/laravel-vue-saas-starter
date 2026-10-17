<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    used: {
        type: Number,
        required: true,
    },
    limit: {
        type: Number,
        default: null,
    },
});

const unlimited = computed(() => props.limit === null);
const reached = computed(() => !unlimited.value && props.used >= props.limit);
const width = computed(() => {
    if (unlimited.value || !props.limit) {
        return 0;
    }

    return Math.min(100, Math.round((props.used / props.limit) * 100));
});
</script>

<template>
    <div class="plan-limit" :data-state="reached ? 'reached' : 'ok'">
        <div class="flex items-center justify-between text-sm text-stone-700">
            <span class="font-medium">{{ label }}</span>
            <span v-if="unlimited">{{ used }} / Unlimited</span>
            <span v-else>{{ used }} / {{ limit }}</span>
        </div>
        <div v-if="!unlimited" class="mt-2 h-2 rounded-full bg-stone-200">
            <div
                class="h-2 rounded-full"
                :class="reached ? 'bg-amber-600' : 'bg-teal-700'"
                :style="{ width: `${width}%` }"
            />
        </div>
    </div>
</template>
