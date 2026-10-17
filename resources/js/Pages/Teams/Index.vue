<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import RoleBadge from '@/Components/RoleBadge.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    members: {
        type: Array,
        default: () => [],
    },
    invitations: {
        type: Array,
        default: () => [],
    },
    abilities: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const team = computed(() => page.props.currentTeam);
const teams = computed(() => page.props.teams ?? []);

const createForm = useForm({ name: '' });
const renameForm = useForm({ name: team.value?.name ?? '' });
const inviteForm = useForm({ email: '', role: 'member' });

function createTeam() {
    createForm.post(route('teams.store'), {
        onSuccess: () => createForm.reset(),
    });
}

function renameTeam() {
    renameForm.patch(route('teams.update', team.value.id));
}

function invite() {
    inviteForm.post(route('invitations.store', team.value.id), {
        onSuccess: () => inviteForm.reset('email'),
    });
}

function switchTo(id) {
    router.post(route('teams.switch', id));
}

function removeMember(member) {
    router.delete(route('members.destroy', [team.value.id, member.id]));
}

function changeRole(member, role) {
    router.patch(route('members.update', [team.value.id, member.id]), { role });
}

function revoke(invitation) {
    router.delete(route('invitations.destroy', invitation.id));
}

function deleteTeam() {
    if (window.confirm(`Delete ${team.value.name}? Projects and memberships go with it.`)) {
        router.delete(route('teams.destroy', team.value.id));
    }
}
</script>

<template>
    <Head title="Team" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-stone-900">Team</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-3 sm:px-6 lg:px-8">
                <section class="space-y-6 lg:col-span-2">
                    <div v-if="teams.length === 0" class="rounded-lg border border-dashed border-stone-300 bg-white p-8">
                        <h3 class="text-lg font-semibold text-stone-900">You are not on a team</h3>
                        <p class="mt-2 text-sm text-stone-600">Create one to become its owner.</p>
                    </div>

                    <div v-else class="overflow-hidden rounded-lg border border-stone-200 bg-white">
                        <div class="border-b border-stone-200 px-6 py-4">
                            <h3 class="text-sm font-semibold text-stone-900">Your teams</h3>
                        </div>
                        <ul class="divide-y divide-stone-100">
                            <li v-for="item in teams" :key="item.id" class="flex items-center justify-between gap-3 px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-stone-900">{{ item.name }}</p>
                                    <RoleBadge class="mt-2" :role="item.role" />
                                </div>
                                <button
                                    v-if="!item.current"
                                    type="button"
                                    class="text-sm font-semibold text-teal-800"
                                    @click="switchTo(item.id)"
                                >
                                    Switch
                                </button>
                                <span v-else class="text-xs font-medium uppercase tracking-wide text-stone-400">Current</span>
                            </li>
                        </ul>
                    </div>

                    <div v-if="team" class="rounded-lg border border-stone-200 bg-white">
                        <div class="border-b border-stone-200 px-6 py-4">
                            <h3 class="text-sm font-semibold text-stone-900">Members</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-xs uppercase tracking-wide text-stone-500">
                                    <tr>
                                        <th class="px-6 py-3 font-medium">Person</th>
                                        <th class="px-6 py-3 font-medium">Role</th>
                                        <th class="px-6 py-3 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    <tr v-for="member in props.members" :key="member.id">
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-stone-900">{{ member.name }}</p>
                                            <p class="text-stone-500">{{ member.email }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <select
                                                v-if="member.can_change_role"
                                                class="rounded-md border-stone-300 text-sm"
                                                :value="member.role"
                                                @change="changeRole(member, $event.target.value)"
                                            >
                                                <option value="admin">Admin</option>
                                                <option value="member">Member</option>
                                            </select>
                                            <RoleBadge v-else :role="member.role" />
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button
                                                v-if="member.can_remove"
                                                type="button"
                                                class="text-sm font-semibold text-red-700"
                                                @click="removeMember(member)"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="team && props.invitations.length" class="rounded-lg border border-stone-200 bg-white">
                        <div class="border-b border-stone-200 px-6 py-4">
                            <h3 class="text-sm font-semibold text-stone-900">Pending invitations</h3>
                            <p class="mt-1 text-xs text-stone-500">Share the accept link. Mail is not sent in this demo.</p>
                        </div>
                        <ul class="divide-y divide-stone-100">
                            <li v-for="invitation in props.invitations" :key="invitation.id" class="px-6 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-stone-900">{{ invitation.email }}</p>
                                        <p class="text-xs text-stone-500">{{ invitation.role }}</p>
                                    </div>
                                    <button
                                        v-if="abilities.invite"
                                        type="button"
                                        class="text-sm font-semibold text-red-700"
                                        @click="revoke(invitation)"
                                    >
                                        Revoke
                                    </button>
                                </div>
                                <p class="mt-2 break-all text-xs text-stone-500">{{ invitation.accept_url }}</p>
                            </li>
                        </ul>
                    </div>
                    <p v-else-if="team" class="text-sm text-stone-500">No invitations are waiting.</p>
                </section>

                <aside class="space-y-6">
                    <form class="rounded-lg border border-stone-200 bg-white p-6" @submit.prevent="createTeam">
                        <h3 class="text-sm font-semibold text-stone-900">New team</h3>
                        <div class="mt-4">
                            <InputLabel for="team-name" value="Name" />
                            <TextInput id="team-name" v-model="createForm.name" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="createForm.errors.name" />
                        </div>
                        <PrimaryButton class="mt-4" :disabled="createForm.processing">Create</PrimaryButton>
                    </form>

                    <form v-if="team && abilities.update" class="rounded-lg border border-stone-200 bg-white p-6" @submit.prevent="renameTeam">
                        <h3 class="text-sm font-semibold text-stone-900">Rename {{ team.name }}</h3>
                        <div class="mt-4">
                            <InputLabel for="rename" value="Name" />
                            <TextInput id="rename" v-model="renameForm.name" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="renameForm.errors.name" />
                        </div>
                        <PrimaryButton class="mt-4" :disabled="renameForm.processing">Save</PrimaryButton>
                    </form>

                    <form v-if="team && abilities.invite" class="rounded-lg border border-stone-200 bg-white p-6" @submit.prevent="invite">
                        <h3 class="text-sm font-semibold text-stone-900">Invite</h3>
                        <p class="mt-1 text-xs text-stone-500">They need an account with this email, then the accept link.</p>
                        <div class="mt-4">
                            <InputLabel for="invite-email" value="Email" />
                            <TextInput id="invite-email" type="email" v-model="inviteForm.email" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="inviteForm.errors.email" />
                        </div>
                        <div class="mt-4">
                            <InputLabel for="invite-role" value="Role" />
                            <select id="invite-role" v-model="inviteForm.role" class="mt-1 block w-full rounded-md border-stone-300 text-sm">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                            <InputError class="mt-2" :message="inviteForm.errors.role" />
                        </div>
                        <PrimaryButton class="mt-4" :disabled="inviteForm.processing">Create invite</PrimaryButton>
                    </form>

                    <button
                        v-if="team && abilities.delete"
                        type="button"
                        class="text-sm font-semibold text-red-700"
                        @click="deleteTeam"
                    >
                        Delete this team
                    </button>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
