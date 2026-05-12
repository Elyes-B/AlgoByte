<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    problems: { type: Object, required: true }
});

const setStatus = (problemId, newStatus) => {
    if (confirm(`Are you sure you want to mark this problem as ${newStatus}?`)) {
        router.patch(route('admin.problems.updateStatus', problemId), {
            status: newStatus
        }, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Problem Review" />

    <AuthenticatedLayout>
        <div class="admin-container">
            <header class="admin-header">
                <div>
                    <h1 class="admin-title">Review Submissions</h1>
                    <p class="admin-subtitle">Manage and curate community-created problems.</p>
                </div>
            </header>

            <div class="glass-panel">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#1a2b3c] text-cyan-400 text-sm uppercase tracking-wider">
                            <th class="p-4">Id</th>
                            <th class="p-4">Title</th>
                            <th class="p-4">Author</th>
                            <th class="p-4">Difficulty</th>
                            <th class="p-4">Submitted</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="problems.data.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-500 italic">
                                No pending problems to review. You're all caught up!
                            </td>
                        </tr>
                        <tr v-for="problem in problems.data" :key="problem.problemId" class="border-b border-[#1a2b3c]/50 hover:bg-[#1a2b3c]/20 transition">
                            <td class="p-4 font-bold text-white">{{ problem.problemId }}</td>
                            <Link :href="`/problems/${problem.problemId}`">
                                {{ problem.title }}
                            </Link>
                            <td class="p-4 text-gray-300" v-if="problem.creator?.profile_image">
                                <Link :href="`/users/${problem.creator.username}`" class="flex items-center">
                                <img :src="problem.creator.profile_image" :alt="problem.creator.username" class="account-profile-image" style="display: inline-block;"/>
                                <span style="margin-left: 10px;">{{ problem.creator.username}}</span>
                                </Link>
                            </td>
                            <td class="p-4 text-gray-300" v-else>'Unknown'</td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded border border-gray-600 text-gray-300">
                                    {{ problem.difficulty }}
                               </span>
                            </td>
                            <td class="p-4 text-gray-400 text-sm">
                                {{ new Date(problem.created_at).toLocaleDateString() }}
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <button @click="setStatus(problem.problemId, 'Accepted')" class="action-btn accept-btn" title="Accept">
                                    <i class="bi bi-check-lg"></i> Accept
                                </button>
                                <button @click="setStatus(problem.problemId, 'Rejected')" class="action-btn reject-btn" title="Reject">
                                    <i class="bi bi-x-lg"></i> Reject
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.admin-header {
    margin-bottom: 30px;
}

.admin-title {
    font-size: 32px;
    font-weight: 900;
    color: white;
    margin: 0;
}

.admin-subtitle {
    color: var(--ab-muted);
    margin: 5px 0 0;
}

.glass-panel {
    background: rgba(11, 22, 34, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid var(--ab-border);
    border-radius: 16px;
    overflow: hidden;
}

.action-btn {
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.accept-btn {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.accept-btn:hover {
    background: #10b981;
    color: #000;
}

.reject-btn {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.reject-btn:hover {
    background: #ef4444;
    color: #fff;
}

.account-profile-image {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    object-fit: cover;
}
</style>
