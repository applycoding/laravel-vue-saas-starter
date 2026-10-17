<script setup>
import PlanLimitMeter from '@/Components/PlanLimitMeter.vue';
import RoleBadge from '@/Components/RoleBadge.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const team = computed(() => page.props.currentTeam);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">Dashboard</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section v-if="!team" class="rounded-lg border border-dashed border-stone-300 bg-white p-8">
                    <h3 class="text-lg font-semibold text-stone-900">No team yet</h3>
                    <p class="mt-2 text-sm text-stone-600">
                        Create a team before you invite people or add projects.
                    </p>
                    <Link :href="route('teams.index')" class="mt-4 inline-flex text-sm font-semibold text-teal-800">
                        Create a team
                    </Link>
                </section>

                <template v-else>
                    <section class="grid gap-6 lg:grid-cols-3">
                        <div class="rounded-lg border border-stone-200 bg-white p-6 lg:col-span-2">
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-lg font-semibold text-stone-900">{{ team.name }}</h3>
                                <RoleBadge :role="team.role" />
                            </div>
                            <p class="mt-2 text-sm text-stone-600">
                                {{ team.plan.name }} plan
                                <span v-if="team.plan.price_cents === 0">· free</span>
                            </p>
                            <div class="mt-6 space-y-4">
                                <PlanLimitMeter
                                    label="Members"
                                    :used="team.usage.members.used"
                                    :limit="team.usage.members.limit"
                                />
                                <p v-if="team.usage.members.pending" class="text-xs text-stone-500">
                                    {{ team.usage.members.pending }} invitation(s) also reserve a seat.
                                </p>
                                <PlanLimitMeter
                                    label="Projects"
                                    :used="team.usage.projects.used"
                                    :limit="team.usage.projects.limit"
                                />
                            </div>
                        </div>
                        <div class="rounded-lg border border-stone-200 bg-white p-6">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-stone-500">Next</h3>
                            <ul class="mt-4 space-y-3 text-sm">
                                <li><Link class="font-medium text-teal-800" :href="route('teams.index')">Invite a teammate</Link></li>
                                <li><Link class="font-medium text-teal-800" :href="route('projects.index')">Add a project</Link></li>
                                <li><Link class="font-medium text-teal-800" :href="route('billing.index')">Review plans</Link></li>
                            </ul>
                        </div>
                    </section>

                    <section class="rounded-lg border border-stone-200 bg-white">
                        <div class="border-b border-stone-200 px-6 py-4">
                            <h3 class="text-sm font-semibold text-stone-900">Recent activity</h3>
                        </div>
                        <p v-if="recentActivity.length === 0" class="px-6 py-8 text-sm text-stone-500">
                            Nothing has been recorded for this team yet.
                        </p>
                        <ul v-else class="divide-y divide-stone-100">
                            <li v-for="entry in recentActivity" :key="entry.id" class="px-6 py-4">
                                <p class="text-sm text-stone-900">{{ entry.description }}</p>
                                <p class="mt-1 text-xs text-stone-500">
                                    {{ entry.user || 'System' }} · {{ entry.action }}
                                </p>
                            </li>
                        </ul>
                    </section>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
