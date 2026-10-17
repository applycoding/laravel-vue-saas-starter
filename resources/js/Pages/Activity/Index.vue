<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    activity: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Activity" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">Activity</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg border border-stone-200 bg-white">
                    <p v-if="activity.data.length === 0" class="px-6 py-10 text-sm text-stone-500">
                        No activity for this team yet.
                    </p>
                    <ul v-else class="divide-y divide-stone-100">
                        <li v-for="entry in activity.data" :key="entry.id" class="px-6 py-4">
                            <p class="text-sm text-stone-900">{{ entry.description }}</p>
                            <p class="mt-1 text-xs text-stone-500">
                                {{ entry.user || 'System' }} · {{ entry.action }}
                            </p>
                        </li>
                    </ul>
                </div>
                <div class="mt-4 flex justify-between text-sm">
                    <Link v-if="activity.prev_page_url" :href="activity.prev_page_url" class="font-semibold text-teal-800">Previous</Link>
                    <span v-else class="text-stone-400">Previous</span>
                    <Link v-if="activity.next_page_url" :href="activity.next_page_url" class="font-semibold text-teal-800">Next</Link>
                    <span v-else class="text-stone-400">Next</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
