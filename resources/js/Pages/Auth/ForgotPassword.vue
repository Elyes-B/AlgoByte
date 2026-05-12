<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import AuthBackgroundToggle from '@/Components/AuthBackgroundToggle.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthenticatedLayout>
    <div class="auth-page">

        <div class="auth-shell">
            <section class="auth-brand">
                <Link href="/" class="brand-mark">
                    <h1>AlgoByte</h1>
                    <span>code lab</span>
                </Link>

                <div class="brand-copy">
                    <p class="eyebrow">Recovery System</p>
                    <h2>Regain access to your focused coding room.</h2>
                    <p>
                        Forgot your password? No problem. Enter your email
                        address and we will send a reset link to your inbox.
                    </p>
                </div>

                <div class="code-preview">
                    <div class="window-controls">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <pre><code>function recoverAccount(email) {
  const request = sendResetLink(email)
  return request.sent ? "check_inbox" : "retry"
}</code></pre>
                </div>
            </section>

            <section class="auth-card">
                <div class="auth-card-header">
                    <p>Security</p>
                    <h2>Reset Password</h2>
                </div>

                <div v-if="status" class="status-message">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="auth-form">
                    <div class="field-group">
                        <label for="email">Email Address</label>
                        <input
                            id="email"
                            type="email"
                            class="auth-input"
                            v-model="form.email"
                            required
                            autofocus
                            placeholder="dev@algobyte.io"
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="actions">
                        <button
                            type="submit"
                            class="submit-btn"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Email Password Reset Link
                        </button>
                    </div>

                    <p class="footer-link">
                        Remembered? <Link :href="route('login')">Back to login</Link>
                    </p>
                </form>
            </section>
        </div>
    </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* ALL CSS FROM GUESTLAYOUT.VUE AND FORGOTPASSWORD.VUE
   MERGED TO MAINTAIN THE EXACT LOOK
*/
.auth-page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
    background-size: 32px 32px;
}

.toggle-wrapper {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 100;
}

.auth-shell {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    width: 100%;
    max-width: 1100px;
    min-height: 640px;
    overflow: hidden;
    border: 1px solid var(--ab-border);
    border-radius: 20px;
    background: rgba(7, 17, 27, 0.6);
    box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(20px);
}

.auth-brand {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 60px;
    background: radial-gradient(circle at 10% 10%, rgba(56, 217, 255, 0.08), transparent 40%);
}

.brand-mark {
    text-decoration: none;
}

.brand-mark h1 {
    margin: 0;
    color: var(--ab-text);
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -1px;
}

.brand-mark span {
    color: var(--ab-cyan);
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.brand-copy { margin: 40px 0; }

.eyebrow {
    margin: 0;
    color: var(--ab-cyan);
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.brand-copy h2 {
    margin: 12px 0 20px;
    color: var(--ab-text);
    font-size: 36px;
    font-weight: 800;
    line-height: 1.1;
}

.brand-copy p {
    color: var(--ab-muted);
    font-size: 16px;
    line-height: 1.6;
}

.code-preview {
    padding: 24px;
    border: 1px solid var(--ab-border);
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.4);
}

.window-controls { display: flex; gap: 8px; margin-bottom: 16px; }
.window-controls span { width: 10px; height: 10px; border-radius: 999px; background: #333; }

.auth-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px;
    border-left: 1px solid var(--ab-border);
    background: rgba(9, 20, 31, 0.4);
}

.auth-card-header h2 {
    margin: 6px 0 24px;
    color: var(--ab-text);
    font-size: 30px;
    font-weight: 800;
}

.auth-card-header p {
    color: var(--ab-cyan);
    font-weight: 800;
    text-transform: uppercase;
    font-size: 12px;
}

.auth-input {
    width: 100%;
    padding: 14px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.3);
    color: white;
    outline: none;
    transition: all 0.2s;
}

.auth-input:focus { border-color: var(--ab-cyan); box-shadow: 0 0 10px rgba(56, 217, 255, 0.2); }

.field-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--ab-muted);
    font-size: 13px;
    font-weight: 700;
}

.submit-btn {
    width: 100%;
    margin-top: 24px;
    padding: 16px;
    border: none;
    border-radius: 8px;
    background: var(--ab-cyan);
    color: #05080d;
    font-weight: 800;
    text-transform: uppercase;
    cursor: pointer;
    transition: transform 0.2s;
}

.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(56, 217, 255, 0.3); }

.footer-link {
    margin-top: 24px;
    text-align: center;
    font-size: 14px;
    color: var(--ab-muted);
}

.footer-link a { color: var(--ab-cyan); text-decoration: none; font-weight: 700; }

.status-message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    font-size: 14px;
    font-weight: 600;
}

@media (max-width: 900px) {
    .auth-shell { grid-template-columns: 1fr; }
    .auth-brand { display: none; }
    .auth-card { border-left: none; }
}
</style>
