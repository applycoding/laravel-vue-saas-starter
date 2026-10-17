<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PlanLimitMeter from '@/Components/PlanLimitMeter.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const team = computed(() => page.props.currentTeam);
const atLimit = computed(() => {
    const usage = team.value?.usage?.projects;
    if (!usage || usage.limit === null) {
        return false;
    }

    return usage.used >= usage.limit;
});

const form = useForm({
    name: '',
    description: '',
});

function createProject() {
    form.post(route('projects.store'), {
        onSuccess: () => form.reset(),
    });
}

function destroyProject(project) {
    router.delete(route('projects.destroy', project.id));
}
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">Projects</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-3 sm:px-6 lg:px-8">
                <section class="lg:col-span-2">
                    <div class="overflow-hidden rounded-lg border border-stone-200 bg-white">
                        <p v-if="projects.length === 0" class="px-6 py-10 text-sm text-stone-500">
                            This team has no projects yet.
                        </p>
                        <ul v-else class="divide-y divide-stone-100">
                            <li v-for="project in projects" :key="project.id" class="flex items-start justify-between gap-4 px-6 py-4">
                                <div>
                                    <p class="font-medium text-stone-900">{{ project.name }}</p>
                                    <p v-if="project.description" class="mt-1 text-sm text-stone-600">{{ project.description }}</p>
                                </div>
                                <button type="button" class="text-sm font-semibold text-red-700" @click="destroyProject(project)">
                                    Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div v-if="team" class="rounded-lg border border-stone-200 bg-white p-6">
                        <PlanLimitMeter
                            label="Projects"
                            :used="team.usage.projects.used"
                            :limit="team.usage.projects.limit"
                        />
                    </div>
                    <form class="rounded-lg border border-stone-200 bg-white p-6" @submit.prevent="createProject">
                        <h3 class="text-sm font-semibold text-stone-900">New project</h3>
                        <p v-if="atLimit" class="mt-2 text-sm text-amber-800">
                            This plan is full. Upgrade on the billing page to add another project.
                        </p>
                        <div class="mt-4">
                            <InputLabel for="project-name" value="Name" />
                            <TextInput id="project-name" v-model="form.name" class="mt-1 block w-full" required :disabled="atLimit" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="project-description" value="Description" />
                            <textarea
                                id="project-description"
                                v-model="form.description"
                                class="mt-1 block w-full rounded-md border-stone-300 text-sm shadow-sm focus:border-teal-700 focus:ring-teal-700"
                                rows="3"
                                :disabled="atLimit"
                            />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                        <PrimaryButton class="mt-4" :disabled="form.processing || atLimit">Create</PrimaryButton>
                    </form>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
