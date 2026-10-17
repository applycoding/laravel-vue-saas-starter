<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const navigation = [
    { label: 'Dashboard', routeName: 'dashboard', match: 'dashboard' },
    { label: 'Projects', routeName: 'projects.index', match: 'projects.*' },
    { label: 'Team', routeName: 'teams.index', match: 'teams.*' },
    { label: 'Billing', routeName: 'billing.index', match: 'billing.*' },
    { label: 'Activity', routeName: 'activity.index', match: 'activity.*' },
    { label: 'API', routeName: 'api-tokens.index', match: 'api-tokens.*' },
];

const teams = computed(() => page.props.teams ?? []);
const currentTeam = computed(() => page.props.currentTeam);
const flash = computed(() => page.props.flash ?? {});

function switchTeam(event) {
    router.post(route('teams.switch', event.target.value));
}
</script>

<template>
    <div>
        <div class="min-h-screen bg-stone-50">
            <nav class="border-b border-stone-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center gap-2">
                                <Link :href="route('dashboard')" class="flex items-center gap-2">
                                    <ApplicationLogo class="block h-8 w-auto fill-current text-teal-800" />
                                    <span class="text-sm font-semibold tracking-tight text-stone-900">Keel</span>
                                </Link>
                            </div>

                            <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex">
                                <NavLink
                                    v-for="item in navigation"
                                    :key="item.routeName"
                                    :href="route(item.routeName)"
                                    :active="route().current(item.match)"
                                >
                                    {{ item.label }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-3">
                            <label v-if="teams.length > 1" class="sr-only" for="team-switch">Current team</label>
                            <select
                                v-if="teams.length > 1"
                                id="team-switch"
                                class="rounded-md border-stone-300 text-sm text-stone-700 focus:border-teal-700 focus:ring-teal-700"
                                :value="currentTeam?.id"
                                @change="switchTeam"
                            >
                                <option v-for="team in teams" :key="team.id" :value="team.id">
                                    {{ team.name }}
                                </option>
                            </select>
                            <span v-else-if="currentTeam" class="text-sm text-stone-500">{{ currentTeam.name }}</span>

                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-stone-500 transition duration-150 ease-in-out hover:text-stone-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-stone-400 transition duration-150 ease-in-out hover:bg-stone-100 hover:text-stone-500 focus:bg-stone-100 focus:text-stone-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            v-for="item in navigation"
                            :key="item.routeName"
                            :href="route(item.routeName)"
                            :active="route().current(item.match)"
                        >
                            {{ item.label }}
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-stone-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-stone-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-stone-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                            <div v-if="currentTeam" class="mt-1 text-sm text-stone-500">
                                {{ currentTeam.name }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow-sm" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <div class="mx-auto max-w-7xl space-y-3 px-4 pt-6 sm:px-6 lg:px-8" v-if="flash.success || flash.error || flash.plainTextToken">
                    <p v-if="flash.success" class="rounded-md border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
                        {{ flash.success }}
                    </p>
                    <p v-if="flash.error" class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                        {{ flash.error }}
                    </p>
                    <div v-if="flash.plainTextToken" class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm text-stone-800">
                        <p class="font-medium">Copy this token now. It will not be shown again.</p>
                        <code class="mt-2 block overflow-x-auto rounded bg-stone-100 px-2 py-1 text-xs">{{ flash.plainTextToken }}</code>
                    </div>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
