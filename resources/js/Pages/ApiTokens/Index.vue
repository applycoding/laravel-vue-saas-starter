<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    tokens: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const team = computed(() => page.props.currentTeam);
const form = useForm({ name: '' });

function createToken() {
    form.post(route('api-tokens.store'), {
        onSuccess: () => form.reset(),
    });
}

function revoke(token) {
    router.delete(route('api-tokens.destroy', token.id));
}
</script>

<template>
    <Head title="API tokens" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">API tokens</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-3 sm:px-6 lg:px-8">
                <section class="lg:col-span-2">
                    <div class="rounded-lg border border-stone-200 bg-white p-6 text-sm text-stone-600">
                        <p>
                            Tokens are personal Sanctum tokens scoped to
                            <span class="font-medium text-stone-900">{{ team?.name || 'the current team' }}</span>.
                            Send <code class="rounded bg-stone-100 px-1">Authorization: Bearer</code> to
                            <code class="rounded bg-stone-100 px-1">/api/v1/projects</code> and
                            <code class="rounded bg-stone-100 px-1">/api/v1/team</code>.
                        </p>
                    </div>
                    <div class="mt-6 overflow-hidden rounded-lg border border-stone-200 bg-white">
                        <p v-if="tokens.length === 0" class="px-6 py-10 text-sm text-stone-500">No tokens yet.</p>
                        <ul v-else class="divide-y divide-stone-100">
                            <li v-for="token in tokens" :key="token.id" class="flex items-center justify-between gap-4 px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-stone-900">{{ token.name }}</p>
                                    <p class="text-xs text-stone-500">{{ (token.abilities || []).join(', ') }}</p>
                                </div>
                                <button type="button" class="text-sm font-semibold text-red-700" @click="revoke(token)">
                                    Revoke
                                </button>
                            </li>
                        </ul>
                    </div>
                </section>
                <form class="h-fit rounded-lg border border-stone-200 bg-white p-6" @submit.prevent="createToken">
                    <h3 class="text-sm font-semibold text-stone-900">New token</h3>
                    <div class="mt-4">
                        <InputLabel for="token-name" value="Name" />
                        <TextInput id="token-name" v-model="form.name" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <PrimaryButton class="mt-4" :disabled="form.processing">Create token</PrimaryButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
