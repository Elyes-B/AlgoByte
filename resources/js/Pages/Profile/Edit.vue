<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
    member: { type: Object },
});
</script>

<template>
    <Head title="Account Settings" />

    <AuthenticatedLayout>
        <div class="settings-container">
            <header class="settings-header">
                <div>
                    <h1 class="settings-title">Account Settings</h1>
                    <p class="settings-subtitle">Manage your profile, security, and account preferences.</p>
                </div>
                <Link :href="route('profile.show', member.username)" class="back-to-profile">
                    View Public Profile
                </Link>
            </header>

            <div class="settings-grid">
                <div class="settings-card">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        :member="member"
                    />
                </div>

                <div class="settings-card">
                    <UpdatePasswordForm />
                </div>

                <div class="settings-card danger-zone">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.settings-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 60px 20px;
}

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--ab-border);
}

.settings-title {
    font-size: 32px;
    font-weight: 900;
    color: white;
    margin: 0;
}

.settings-subtitle {
    color: var(--ab-muted);
    margin: 5px 0 0;
}

.back-to-profile {
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--ab-border);
    border-radius: 10px;
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.back-to-profile:hover {
    background: rgba(255, 255, 255, 0.1);
}

.settings-grid {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.settings-card {
    background: rgba(11, 22, 34, 0.6);
    border: 1px solid var(--ab-border);
    border-radius: 20px;
    padding: 30px;
}

.danger-zone {
    border-color: rgba(239, 68, 68, 0.2);
    background: rgba(239, 68, 68, 0.02);
}
</style>
