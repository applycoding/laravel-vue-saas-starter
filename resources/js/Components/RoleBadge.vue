<script setup>
const props = defineProps({
    role: {
        type: String,
        required: true,
    },
});

const labels = {
    owner: 'Owner',
    admin: 'Admin',
    member: 'Member',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
        :class="{
            'bg-teal-100 text-teal-900': props.role === 'owner',
            'bg-stone-200 text-stone-800': props.role === 'admin',
            'bg-white text-stone-600 ring-1 ring-stone-200': props.role === 'member',
        }"
        :data-role="props.role"
    >
        {{ labels[props.role] || props.role }}
    </span>
</template>
