<script setup>
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    targetMember: Object,
    problems: Object, // This is now a paginated Laravel object (has .data and .links)
    currentTab: String,
    isOwner: Boolean
});

// Navigation Logic using Inertia Router
const switchTab = (tabName) => {
    router.get(route('history.index', props.targetMember.username),
        { tab: tabName },
        { preserveState: true, preserveScroll: true }
    );
};

const changePage = (url) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};

// CSS Class Helpers
const tabClass = (isActive) => [
  'pb-4 text-xs font-black uppercase tracking-[0.15em] transition-all duration-300 border-b-2 bg-transparent cursor-pointer',
  isActive
    ? 'text-[#38d9ff] border-[#38d9ff] drop-shadow-[0_0_10px_rgba(56,217,255,0.5)]'
    : 'text-gray-500 border-transparent hover:text-gray-300'
];

const difficultyClass = (difficulty) => {
  const base = 'px-3 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border ';
  if (difficulty === 'Easy') return base + 'bg-emerald-500/5 text-emerald-400 border-emerald-500/20';
  if (difficulty === 'Medium') return base + 'bg-amber-500/5 text-amber-400 border-amber-500/20';
  return base + 'bg-rose-500/5 text-rose-400 border-rose-500/20';
};
</script>

<template>
    <Head :title="`${targetMember.username}'s History`" />

    <AuthenticatedLayout>
        <div class="py-10 px-6">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-white title">History of {{ targetMember.username }}</h1>

                <div class="flex space-x-10 border-b border-[#1a2b3c] mb-10" id="selectOptions">
                    <button @click="switchTab('solved')" :class="tabClass(currentTab === 'solved')">
                        Solved
                    </button>
                    <button @click="switchTab('created')" :class="tabClass(currentTab === 'created')">
                        Authored
                    </button>
                    <button @click="switchTab('attempted')" :class="tabClass(currentTab === 'attempted')">
                        Attempts
                    </button>
                </div>

                <div class="grid gap-4">
                    <div v-if="problems.data.length === 0" class="glass-card p-20 text-center border border-[#1a2b3c] rounded-2xl">
                        <span class="text-gray-600 font-black uppercase tracking-widest">No Records Found</span>
                    </div>

                    <div v-else
                        v-for="problem in problems.data"
                        :key="problem.problemId"
                        class="glass-card group flex items-center justify-between p-6 rounded-xl border border-[#1a2b3c] hover:border-[#38d9ff]/30 transition-all duration-500"
                    >
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-3">
                                <span :class="difficultyClass(problem.difficulty)">{{ problem.difficulty }}</span>
                                <h3 class="text-lg font-extrabold text-white group-hover:text-[#38d9ff] transition-colors">
                                    {{ problem.title }}
                                </h3>
                            </div>
                            <div class="flex items-center gap-4 text-[10px] font-bold text-gray-500 uppercase tracking-widest">
                                <span class="text-gray-400">ID: #{{ problem.problemId }}</span>
                                <span class="w-1 h-1 bg-gray-700 rounded-full"></span>
                                <span>Status: {{ problem.status }}</span>
                                <span class="w-1 h-1 bg-gray-700 rounded-full"></span>
                                <span>Activity: {{ new Date(problem.created_at).toLocaleDateString() }}</span>
                            </div>
                        </div>

                        <a :href="`/problems/${problem.problemId}`" class="solve-btn">
                            Open Details
                        </a>
                    </div>
                </div>

                <div v-if="problems.links.length > 3" class="mt-10 flex justify-center gap-2">
                    <button
                        v-for="(link, index) in problems.links"
                        :key="index"
                        @click="changePage(link.url)"
                        v-html="link.label"
                        class="px-4 py-2 text-xs font-black uppercase rounded-lg border transition-all duration-200"
                        :class="[
                            link.active
                                ? 'bg-[#38d9ff] text-[#05080d] border-[#38d9ff]'
                                : 'bg-transparent text-gray-500 border-[#1a2b3c] hover:border-[#38d9ff] hover:text-[#38d9ff]',
                            !link.url ? 'opacity-20 cursor-not-allowed' : 'cursor-pointer'
                        ]"
                        :disabled="!link.url"
                    />
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass-card {
    background: rgba(11, 22, 34, 0.6);
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}

.solve-btn {
    padding: 10px 24px;
    background: rgba(56, 217, 255, 0.05);
    border: 1px solid rgba(56, 217, 255, 0.2);
    border-radius: 8px;
    color: #38d9ff;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.title {
    font-size: 32px;
    font-weight: 900;
    color: white;
    margin: 0 0 20px;
}

.solve-btn:hover {
    background: #38d9ff;
    color: #05080d;
    box-shadow: 0 0 20px rgba(56, 217, 255, 0.4);
    transform: translateY(-2px);
}
</style>
