<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    subscription: {
        type: Object,
        default: null,
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

const checkout = useForm({ plan: '' });
const cancel = useForm({});

function money(cents) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(cents / 100);
}

function limitLabel(value, noun) {
    return value === null ? `Unlimited ${noun}` : `${value} ${noun}`;
}

function choose(plan) {
    checkout.plan = plan.slug;
    checkout.post(route('billing.checkout'));
}

function cancelPlan() {
    cancel.post(route('billing.cancel'));
}
</script>

<template>
    <Head title="Billing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">Billing</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="rounded-lg border border-stone-200 bg-white p-6">
                    <h3 class="text-sm font-semibold text-stone-900">Current subscription</h3>
                    <p v-if="subscription" class="mt-2 text-sm text-stone-600">
                        {{ subscription.plan_slug }} · {{ subscription.status }} · gateway {{ subscription.gateway }}
                    </p>
                    <p v-else class="mt-2 text-sm text-stone-500">No subscription row yet.</p>
                    <p class="mt-3 text-sm text-stone-500">
                        Checkout uses the fake gateway. Confirming it runs the same handler as a signed webhook. Nothing is charged.
                    </p>
                    <button
                        v-if="canManage && subscription && subscription.plan_slug !== 'starter'"
                        type="button"
                        class="mt-4 text-sm font-semibold text-red-700"
                        :disabled="cancel.processing"
                        @click="cancelPlan"
                    >
                        Cancel and return to Starter
                    </button>
                    <p v-if="!canManage" class="mt-4 text-sm text-stone-500">Only the owner can change the plan.</p>
                </section>

                <div class="grid gap-4 md:grid-cols-3">
                    <article v-for="plan in props.plans" :key="plan.slug" class="flex flex-col rounded-lg border border-stone-200 bg-white p-6">
                        <h3 class="text-lg font-semibold text-stone-900">{{ plan.name }}</h3>
                        <p class="mt-2 text-2xl font-semibold text-stone-900">{{ money(plan.price_cents) }}<span class="text-sm font-normal text-stone-500"> / month</span></p>
                        <p class="mt-3 text-sm text-stone-600">{{ plan.description }}</p>
                        <ul class="mt-4 space-y-1 text-sm text-stone-700">
                            <li>{{ limitLabel(plan.max_members, 'members') }}</li>
                            <li>{{ limitLabel(plan.max_projects, 'projects') }}</li>
                        </ul>
                        <PrimaryButton
                            v-if="canManage && plan.slug !== subscription?.plan_slug && plan.price_cents > 0"
                            class="mt-6"
                            :disabled="checkout.processing"
                            @click="choose(plan)"
                        >
                            Fake checkout
                        </PrimaryButton>
                        <p v-else-if="plan.slug === subscription?.plan_slug" class="mt-6 text-sm font-medium text-teal-800">Current plan</p>
                    </article>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
