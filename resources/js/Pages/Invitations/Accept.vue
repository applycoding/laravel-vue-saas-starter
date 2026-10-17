<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: {
        type: Object,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({});

function accept() {
    form.post(route('invitations.accept', props.token));
}
</script>

<template>
    <Head title="Invitation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">Invitation</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-lg px-4 sm:px-6">
                <div class="rounded-lg border border-stone-200 bg-white p-6">
                    <h3 class="text-lg font-semibold text-stone-900">Join {{ invitation.team }}</h3>
                    <p class="mt-2 text-sm text-stone-600">
                        This invite is for {{ invitation.email }} as {{ invitation.role }}.
                    </p>
                    <p v-if="invitation.expired" class="mt-4 text-sm text-amber-800">
                        This invitation has expired or was already used.
                    </p>
                    <p v-else-if="!invitation.matches_user" class="mt-4 text-sm text-amber-800">
                        Sign in as {{ invitation.email }} to accept it.
                    </p>
                    <form v-else class="mt-6" @submit.prevent="accept">
                        <PrimaryButton :disabled="form.processing">Accept invitation</PrimaryButton>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
