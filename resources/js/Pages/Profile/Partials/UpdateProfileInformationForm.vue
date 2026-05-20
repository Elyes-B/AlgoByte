<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    member: { type: Object, required: true },
});

const SUPABASE_STORAGE_URL = 'https://taycnalhgabfrapisbct.supabase.co/storage/v1/object/public/images';

// Helper to determine pathing between current upload state and backend records
const resolveSupabasePath = (fileName, folder) => {
    if (!fileName) return null;
    if (fileName.startsWith('data:')) return fileName; // local FileReader preview
    return `${SUPABASE_STORAGE_URL}/${folder}/${fileName}`;
};

const lastUploads = ref({
    profile_image: { name: '', size: 0 },
    background_image: { name: '', size: 0 }
});

const profilePreview = ref(resolveSupabasePath(props.member.profile_image, 'profiles'));
const backgroundPreview = ref(resolveSupabasePath(props.member.background_image, 'banners'));

// Watcher ensures that once Inertia finishes processing, the view updates without page refreshes
watch(() => props.member, (newMember) => {
    profilePreview.value = resolveSupabasePath(newMember.profile_image, 'profiles');
    backgroundPreview.value = resolveSupabasePath(newMember.background_image, 'banners');
}, { deep: true });

const form = useForm({
    _method: 'PATCH',
    username: props.member.username,
    email: props.member.email,
    profile_image: null,
    background_image: null,
});

const onFileChange = (e, type) => {
    const file = e.target.files[0];
    if (!file) return;

    form[type] = file;

    const reader = new FileReader();
    reader.onload = (f) => {
        if (type === 'profile_image') profilePreview.value = f.target.result;
        if (type === 'background_image') backgroundPreview.value = f.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            if (form.profile_image) {
                lastUploads.value.profile_image = { name: form.profile_image.name, size: form.profile_image.size };
            }
            if (form.background_image) {
                lastUploads.value.background_image = { name: form.background_image.name, size: form.background_image.size };
            }
            form.profile_image = null;
            form.background_image = null;
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-white">Profile Assets</h2>
            <p class="mt-1 text-sm text-gray-400">Update your public avatar and profile banner.</p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col items-center p-4 border border-gray-800 rounded-xl bg-black/20">
                    <InputLabel value="Profile Picture" class="mb-4" />
                    <div class="relative group cursor-pointer" @click="$refs.profileInput.click()">
                        <img :src="profilePreview || `https://ui-avatars.com/api/?name=${form.username}&background=38d9ff&color=05080d`"
                             class="w-32 h-32 rounded-2xl object-cover border-2 border-cyan-500/50 group-hover:opacity-75 transition" />
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            <span class="text-xs font-bold text-white uppercase">Change</span>
                        </div>
                    </div>
                    <input type="file" ref="profileInput" class="hidden" @change="onFileChange($event, 'profile_image')" accept="image/*" />
                    <InputError :message="form.errors.profile_image" class="mt-2" />
                </div>

                <div class="flex flex-col items-center p-4 border border-gray-800 rounded-xl bg-black/20">
                    <InputLabel value="Profile Banner" class="mb-4" />
                    <div class="w-full h-32 relative group cursor-pointer rounded-xl overflow-hidden border-2 border-gray-800" @click="$refs.bgInput.click()">
                        <img v-if="backgroundPreview" :src="backgroundPreview" class="w-full h-full object-cover group-hover:opacity-75 transition" />
                        <div v-else class="w-full h-full bg-gray-900"></div>
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            <span class="text-xs font-bold text-white uppercase">Upload Banner</span>
                        </div>
                    </div>
                    <input type="file" ref="bgInput" class="hidden" @change="onFileChange($event, 'background_image')" accept="image/*" />
                    <InputError :message="form.errors.background_image" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                <div>
                    <InputLabel for="username" value="Username" />
                    <TextInput id="username" v-model="form.username" class="mt-1 block w-full" />
                    <InputError :message="form.errors.username" />
                </div>
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput id="email" v-model="form.email" class="mt-1 block w-full" />
                    <InputError :message="form.errors.email" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save Profile</PrimaryButton>
                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-400">Changes saved successfully.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
