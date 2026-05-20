<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    reports: { type: Object, required: true }
});

const decideReport = (reportId, action) => {
    const confirmationMessage = action === 'approved'
        ? 'Delete the reported problem and approve this report?'
        : 'Keep the problem and dismiss this report?';

    if (confirm(confirmationMessage)) {
        router.patch(route('admin.problems.updateStatus', { report: reportId }), {
            action,
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
                            <h1 class="admin-title">Review Reports</h1>
                            <p class="admin-subtitle">Approve or dismiss problem reports and keep the platform safe.</p>

                </div>
            </header>
            <div class="glass-panel">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#1a2b3c] text-cyan-400 text-sm uppercase tracking-wider">
                            <th class="p-4">Id</th>
                            <th class="p-4">Problem</th>
                            <th class="p-4">Reporter</th>
                            <th class="p-4">Reason</th>
                            <th class="p-4">Severity</th>
                            <th class="p-4">Submitted</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="reports.data.length === 0">
                            <td colspan="7" class="p-8 text-center text-gray-500 italic">
                                No pending reports to review. You're all caught up!
                            </td>
                        </tr>
                        <tr v-for="report in reports.data" :key="report.reportId" class="border-b border-[#1a2b3c]/50 hover:bg-[#1a2b3c]/20 transition">
                            <td class="p-4 font-bold text-white">{{ report.reportId }}</td>
                            <td class="p-4">
                                <Link v-if="report.problem" :href="route('browse-problems.show', report.problem.problemId)" class="text-cyan-200 hover:text-white">
                                    {{ report.problem.title }}
                                </Link>
                                <span v-else class="text-gray-400">Problem removed</span>
                            </td>
                            <td class="p-4 text-gray-300">
                                <span v-if="report.reporter">{{ report.reporter.username }}</span>
                                <span v-else class="text-gray-500">Unknown</span>
                            </td>
                            <td class="p-4 text-gray-300">{{ report.reason }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs rounded border border-gray-600 text-gray-300">
                                    {{ report.severity }}
                                </span>
                            </td>
                            <td class="p-4 text-gray-400 text-sm">
                                {{ new Date(report.created_at).toLocaleDateString() }}
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <button type="button" @click="decideReport(report.reportId, 'approved')" class="action-btn accept-btn" title="Approve Report">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button type="button" @click="decideReport(report.reportId, 'rejected')" class="action-btn reject-btn" title="Keep Problem">
                                    <i class="bi bi-x-lg"></i> Keep
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
