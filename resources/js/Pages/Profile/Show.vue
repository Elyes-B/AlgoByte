<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    member: { type: Object, required: true },
    isOwner: { type: Boolean, default: false }
});

// Base Supabase storage URL for public images
const SUPABASE_STORAGE_URL = 'https://taycnalhgabfrapisbct.supabase.co/storage/v1/object/public/images';

const getBanner = (member) => {
    if (!member.background_image) return '';
    return `${SUPABASE_STORAGE_URL}/banners/${member.background_image}`;
};

const getAvatar = (member) => {
    return member.profile_image
        ? `${SUPABASE_STORAGE_URL}/profiles/${member.profile_image}`
        : `https://ui-avatars.com/api/?name=${member.username}&background=38d9ff&color=05080d`;
};
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="profile-container">
            <div class="profile-hero">
                <div class="banner-wrapper">
                    <img v-if="getBanner(member)"
                         :src="getBanner(member)"
                         alt="Background"
                         class="banner-img" />

                    <div v-else class="banner-fallback"></div>
                    <div class="banner-overlay"></div>
                </div>

                <div class="profile-info-bar">
                    <div class="profile-main-content">
                        <div class="avatar-container">
                            <img :src="getAvatar(member)" alt="Avatar" class="avatar-img" />
                        </div>

                        <div class="user-meta">
                            <h1 class="username">{{ member.username }}</h1>
                            <p v-if="isOwner" class="user-email">{{ member.email }}</p>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <Link v-if="isOwner" :href="route('profile.edit')" class="edit-btn">
                            <i class="bi bi-pencil-square"></i>
                            Edit Profile
                        </Link>

                        <Link :href="route('history.index', member.username)" class="edit-btn">
                            <i class="bi bi-clock-history"></i>
                            View History
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.profile-hero {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    background: rgba(11, 22, 34, 0.6);
    border: 1px solid var(--ab-border);
}

.banner-wrapper {
    height: 240px;
    width: 100%;
    position: relative;
}

.banner-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent, rgba(5, 8, 13, 0.8));
}

.banner-fallback {
    width: 100%;
    height: 100%;
    background: #05080d;
    position: relative;
    overflow: hidden;
}

.banner-fallback::before {
    content: '';
    position: absolute;
    top: -30%;
    left: -10%;
    width: 120%;
    height: 160%;
    background: radial-gradient(circle at 20% 35%, rgba(56, 217, 255, 0.2) 0%, transparent 60%);
    filter: blur(70px);
}

.profile-info-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding: 0 40px 30px;
    margin-top: -60px;
    position: relative;
    z-index: 10;
}

.profile-main-content {
    display: flex;
    align-items: flex-end;
    gap: 25px;
}

.avatar-container {
    position: relative;
    padding: 6px;
    background: #05080d;
    border-radius: 20px;
    border: 1px solid var(--ab-border);
}

.avatar-img {
    width: 120px;
    height: 120px;
    border-radius: 14px;
    object-fit: cover;
}

.username {
    font-size: 28px;
    font-weight: 900;
    color: white;
    margin: 0;
    letter-spacing: -0.5px;
}

.user-email {
    color: var(--ab-muted);
    font-size: 14px;
    font-weight: 600;
    margin: 2px 0 0;
}

.profile-actions {
    display: flex;
    gap: 12px; /* Space between the two buttons */
    align-items: center;
}

.edit-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: rgba(56, 217, 255, 0.1);
    border: 1px solid rgba(56, 217, 255, 0.3);
    border-radius: 10px;
    color: var(--ab-cyan);
    font-size: 12px; /* Slightly smaller font to fit two buttons better */
    font-weight: 800;
    text-transform: uppercase;
    transition: all 0.3s ease;
    text-decoration: none;
    white-space: nowrap; /* Prevents text from wrapping */
}

.edit-btn:hover {
    background: var(--ab-cyan);
    color: #05080d;
    box-shadow: 0 0 20px rgba(56, 217, 255, 0.4);
    transform: translateY(-2px);
}

.edit-btn i {
    font-size: 16px;
    line-height: 0;
}

@media (max-width: 768px) {
    .profile-info-bar {
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-top: -50px;
        padding-bottom: 20px;
    }

    .profile-actions {
        margin-top: 25px;
        width: 100%;
        justify-content: center;
        flex-wrap: wrap; /* Stack buttons on very small screens if needed */
    }
}
</style>
