<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    sessionId: {
        type: String,
        required: true,
    },
    plan: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const team = computed(() => page.props.currentTeam);
const form = useForm({});

function money(cents) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(cents / 100);
}

function confirm() {
    form.post(route('billing.fake-checkout.confirm', props.sessionId));
}
</script>

<template>
    <Head title="Fake checkout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">Fake checkout</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-lg px-4 sm:px-6">
                <form class="rounded-lg border border-stone-200 bg-white p-6" @submit.prevent="confirm">
                    <p class="text-xs font-medium uppercase tracking-wide text-amber-700">No card is collected</p>
                    <h3 class="mt-2 text-lg font-semibold text-stone-900">{{ plan.name }} for {{ team?.name }}</h3>
                    <p class="mt-2 text-sm text-stone-600">{{ plan.description }}</p>
                    <p class="mt-4 text-2xl font-semibold text-stone-900">{{ money(plan.price_cents) }}</p>
                    <p class="mt-2 text-xs text-stone-500">Session {{ sessionId }}</p>
                    <PrimaryButton class="mt-6" :disabled="form.processing">Confirm fake payment</PrimaryButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
