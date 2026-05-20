<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';





const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentReports: {
        type: Array,
        required: true,
    },
});

const severityClass = (severity) => {
    if (severity === 'high') {
        return 'severity-high';
    }
    if (severity === 'medium') {
        return 'severity-medium';
    }
    return 'severity-low';
};

const severityLabel = (severity) => {
    return severity.charAt(0).toUpperCase() + severity.slice(1);
};

const statusClass = (status) => {
    if (status === 'pending') {
        return 'status-pending';
    }
    if (status === 'reviewed') {
        return 'status-reviewed';
    }
    return 'status-rejected';
};

const statusLabel = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <section class="admin-dashboard">
            <div class="dashboard-header">
                <div>
                    <p class="eyebrow">System Management</p>
                    <h1>Admin Dashboard</h1>
                </div>
                <div class="dashboard-header-right">
                    <p class="subtitle">Monitor platform activity and manage reports</p>
                </div>
            </div>

            <section class="stats-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users-icon">👥</div>
                        <div class="stat-content">
                            <p class="stat-label">Total Users</p>
                            <p class="stat-value">{{ stats.totalUsers }}</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon problems-icon">📝</div>
                        <div class="stat-content">
                            <p class="stat-label">Total Problems</p>
                            <p class="stat-value">{{ stats.totalProblems }}</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon reports-icon">🚩</div>
                        <div class="stat-content">
                            <p class="stat-label">Pending Reports</p>
                            <p class="stat-value">{{ stats.pendingReports }}</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon total-icon">📊</div>
                        <div class="stat-content">
                            <p class="stat-label">Total Reports</p>
                            <p class="stat-value">{{ stats.totalReports }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="reports-section">
                <div class="section-header">
                    <div class="section-header-left">
                        <h2>Recent Reports</h2>
                        <span class="report-count">{{ recentReports.length }} reports</span>
                    </div>

                    <Link
                        :href="route('admin.problems.index')"
                        class="admin-link"
                    >
                        Problem Review
                        <span class="admin-link-arrow">→</span>
                    </Link>
                </div>


                <div v-if="recentReports.length > 0" class="reports-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Reporter</th>
                                <th>Problem</th>
                                <th>Reason</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="report in recentReports" :key="report.reportId">
                                <td class="reporter-cell">{{ report.reporter }}</td>
                                <td class="problem-cell">{{ report.problem }}</td>
                                <td class="reason-cell">{{ report.reason }}</td>
                                <td>
                                    <span :class="['severity-badge', severityClass(report.severity)]">
                                        {{ severityLabel(report.severity) }}
                                    </span>
                                </td>
                                <td>
                                    <span :class="['status-badge', statusClass(report.status)]">
                                        {{ statusLabel(report.status) }}
                                    </span>
                                </td>
                                <td class="time-cell">{{ report.createdAt }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="empty-state">
                    <p>No reports available</p>
                </div>
            </section>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.section-header-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.admin-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--ab-cyan);
    font-weight: 900;
    text-decoration: none;
    padding: 10px 14px;
    border: 1px solid rgba(56, 217, 255, 0.25);
    border-radius: 10px;
    background: rgba(56, 217, 255, 0.06);
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.admin-link:hover {
    transform: translateY(-1px);
    border-color: rgba(56, 217, 255, 0.45);
    background: rgba(56, 217, 255, 0.12);
}

.admin-link-arrow {
    color: var(--ab-teal);
}

.admin-dashboard {
    width: min(1400px, calc(100% - 32px));
    margin: 28px auto 40px;
    display: grid;
    gap: 28px;
}

 .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
}

.dashboard-header-right {
    display: flex;
    align-items: flex-start;
}


.dashboard-header h1 {
    margin: 0;
    color: var(--ab-text);
    font-size: clamp(28px, 4vw, 48px);
    font-weight: 850;
    line-height: 1;
}

.eyebrow {
    margin: 0 0 12px;
    color: var(--ab-teal);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.subtitle {
    margin: 0;
    color: var(--ab-muted);
    font-size: 15px;
    line-height: 1.6;
}

.stats-section {
    display: grid;
    gap: 16px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.stat-card {
    display: flex;
    gap: 16px;
    align-items: center;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(11, 22, 34, 0.96), rgba(5, 10, 16, 0.96));
    transition: transform 0.2s ease, border-color 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    border-color: rgba(56, 217, 255, 0.2);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 8px;
    font-size: 24px;
}

.users-icon {
    background: rgba(56, 217, 255, 0.1);
}

.problems-icon {
    background: rgba(255, 207, 138, 0.1);
}

.reports-icon {
    background: rgba(255, 93, 122, 0.1);
}

.total-icon {
    background: rgba(84, 214, 146, 0.1);
}

.stat-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stat-label {
    margin: 0;
    color: var(--ab-muted);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.stat-value {
    margin: 0;
    color: var(--ab-text);
    font-size: 28px;
    font-weight: 850;
}

.reports-section {
    display: grid;
    gap: 16px;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    background: rgba(7, 17, 27, 0.94);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.section-header h2 {
    margin: 0;
    color: var(--ab-text);
    font-size: 20px;
    font-weight: 800;
}

.report-count {
    display: inline-flex;
    padding: 4px 12px;
    border-radius: 999px;
    background: rgba(56, 217, 255, 0.1);
    color: var(--ab-cyan);
    font-size: 12px;
    font-weight: 700;
}

.reports-table {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

thead {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

th {
    padding: 12px;
    color: var(--ab-muted);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-align: left;
}

td {
    padding: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    color: var(--ab-text);
}

.reporter-cell,
.problem-cell {
    font-weight: 600;
}

.reason-cell {
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--ab-muted);
    font-size: 13px;
}

.time-cell {
    color: var(--ab-muted);
    font-size: 13px;
}

.severity-badge,
.status-badge {
    display: inline-flex;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.severity-high {
    background: rgba(255, 93, 122, 0.15);
    color: #ff8aa0;
    border: 1px solid rgba(255, 93, 122, 0.3);
}

.severity-medium {
    background: rgba(255, 207, 138, 0.15);
    color: #ffd38a;
    border: 1px solid rgba(255, 207, 138, 0.3);
}

.severity-low {
    background: rgba(84, 214, 146, 0.15);
    color: #68f2ab;
    border: 1px solid rgba(84, 214, 146, 0.3);
}

.status-pending {
    background: rgba(255, 207, 138, 0.15);
    color: #ffd38a;
    border: 1px solid rgba(255, 207, 138, 0.3);
}

.status-reviewed {
    background: rgba(56, 217, 255, 0.15);
    color: #38d9ff;
    border: 1px solid rgba(56, 217, 255, 0.3);
}

.status-rejected {
    background: rgba(255, 93, 122, 0.15);
    color: #ff8aa0;
    border: 1px solid rgba(255, 93, 122, 0.3);
}

.empty-state {
    padding: 40px;
    text-align: center;
    color: var(--ab-muted);
}

@media (max-width: 768px) {
    .admin-dashboard {
        width: min(100%, calc(100% - 16px));
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 12px;
    }

    th,
    td {
        padding: 8px;
    }

    .reason-cell {
        max-width: 150px;
    }
}
</style>
