<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Keel" />

    <div class="min-h-screen bg-stone-950 text-stone-100">
        <div class="mx-auto flex min-h-screen max-w-3xl flex-col justify-center px-6 py-16">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-teal-300">Portfolio demo</p>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl">Keel</h1>
            <p class="mt-4 max-w-xl text-lg text-stone-300">
                A multi-tenant workspace starter. Teams, roles, invitations, plan limits, and a fake billing gateway live in one Laravel and Vue slice you can read end to end.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <Link
                    v-if="canLogin && $page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-stone-950"
                >
                    Open the workspace
                </Link>
                <template v-else>
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-stone-950"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-md border border-stone-600 px-4 py-2 text-sm font-semibold text-stone-100"
                    >
                        Create an account
                    </Link>
                </template>
            </div>

            <p class="mt-10 max-w-xl text-sm text-stone-400">
                Billing never charges a card. Invitations are links on the team page, not emails. Request volume and latency are not measured here.
            </p>
        </div>
    </div>
</template>
