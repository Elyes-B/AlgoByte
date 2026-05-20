<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch} from 'vue';
import { Head,router,usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { VueMonacoEditor } from '@guolao/vue-monaco-editor';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// View states: 'list' (Dashboard view), 'create' (Form empty), 'edit' (Form pre-filled)
const activeView = ref('list');
const editingProblemId = ref(null);

const props = defineProps({
    problems: {
        type: Array,
        default: () => []
    },
    dashboardCounts: {
        type: Object,
        default: () => ({
            total_users: 0,
            total_admins: 0,
            problems_on_site: 0,
            created_problems: 0,
            submissions: 0,
        })
    }
});

const fakeProblem = {
    id: 'fake-problem',
    title: 'Sample Editable Problem',
    description: 'This is a temporary problem used only for testing the edit and delete buttons.',
    language: 'javascript',
    solution: 'function solution(input) {\n  return input;\n}',
    explanation: 'Return the input unchanged.',
    visibility: 'public',
    difficulty: 'easy',
    test_cases: [
        { id: 1, label: 'Example 01', arguments: ['1'], expected_output: '1' },
        { id: 2, label: 'Example 02', arguments: ['2'], expected_output: '2' },
    ],
    fake: true,
};
const localFakeProblems = ref([fakeProblem]);

const displayProblems = computed(() => (props.problems.length ? props.problems : localFakeProblems.value));

const page = usePage();

const languageOptions = [
    {
        value: 'typescript',
        label: 'TypeScript',
        fileName: 'solution.ts',
        template: `function solution(input) {
  // write your implementation here
  return input
}`,
    },
    {
        value: 'javascript',
        label: 'JavaScript',
        fileName: 'solution.js',
        template: `function solution(input) {
  // write your implementation here
  return input;
}`,
    },
    {
        value: 'python',
        label: 'Python',
        fileName: 'solution.py',
        template: `def solution(input):
    # write your implementation here
    return input`,
    },
    {
        value: 'java',
        label: 'Java',
        fileName: 'Solution.java',
        template: `public String solution(String input) {
  // write your implementation here
  return input;
}`,
    },
    {
        value: 'c',
        label: 'C',
        fileName: 'solution.c',
        template: `char* solution(char* input) {
  // write your implementation here
  return input;
}`,
    },
];

const getLanguageConfig = (language) =>
    languageOptions.find((option) => option.value === language) ?? languageOptions[0];

const createTestCase = (id, label) => ({
    id,
    label,
    arguments: [],
    result: '',
    message: '',
    status: 'idle',
    isValidating: false,
});

const form = reactive({
    title: '',
    description: '',
    language: 'typescript',
    solution: getLanguageConfig('typescript').template,
    explanation: '',
    visibility: 'public',
    difficulty: 'medium',
    testCases: [
        createTestCase(1, 'Example 01'),
        createTestCase(2, 'Example 02'),
    ],
});

const editorFontSize = ref(Number(localStorage.getItem('editorFontSize')) || 14);
const appMode = ref(localStorage.getItem('authBackgroundMode') || document.documentElement.dataset.authBg || 'dark');
const isCreating = ref(false);
const createSuccess = ref('');
const createError = ref('');
let modeObserver;

/* =========================================================================
   🆕 CRUD NAVIGATION & ACTIONS MANAGEMENT
   ========================================================================= */

const openCreateMode = () => {
    editingProblemId.value = null;
    form.title = '';
    form.description = '';
    form.language = 'typescript';
    form.solution = getLanguageConfig('typescript').template;
    form.explanation = '';
    form.visibility = 'public';
    form.difficulty = 'medium';
    form.testCases = [
        createTestCase(1, 'Example 01'),
        createTestCase(2, 'Example 02'),
    ];
    createSuccess.value = '';
    createError.value = '';
    activeView.value = 'create';
};

const openEditMode = (problem) => {
    editingProblemId.value = problem.id;
    form.title = problem.title;
    form.description = problem.description;
    form.language = problem.language;
    form.solution = problem.solution;
    form.explanation = problem.explanation;
    form.visibility = problem.visibility || 'public';
    form.difficulty = problem.difficulty;

    // Check if the input details have saved arrays or parse backend payload structures
    if (problem.test_cases && problem.test_cases.length >= 2) {
        form.testCases = problem.test_cases.map((tc, idx) =>
            createTestCase(tc.id || idx + 1, tc.label || `Example 0${idx + 1}`, tc.arguments || [], tc.expected_output || '')
        );
    } else {
        form.testCases = [
            createTestCase(1, 'Example 01'),
            createTestCase(2, 'Example 02'),
        ];
    }
    createSuccess.value = '';
    createError.value = '';
    activeView.value = 'edit';
};

const cancelForm = () => {
    activeView.value = 'list';
    editingProblemId.value = null;
};

const deleteProblem = async (problemId) => {
    if (!confirm('Are you sure you want to delete this challenge permanently?')) return;

    if (problemId === fakeProblem.id) {
        localFakeProblems.value = [];
        return;
    }

    try {
        // Change route endpoint structure depending on your back-end configuration rules
        router.delete(route('problem-creation.delete', { id: problemId }), {
            headers: {
                Accept: 'application/json',
            },
        });
        // Force Inertia to fetch raw updated properties without a hard application loop reload
        router.reload({ only: ['problems'] });
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to remove entry details.');
    }
};

const difficultyTone = computed(() => {
    if (form.difficulty === 'easy') {
        return 'difficulty-easy';
    }

    if (form.difficulty === 'hard') {
        return 'difficulty-hard';
    }

    return 'difficulty-medium';
});

const selectedLanguage = computed(() => getLanguageConfig(form.language));
const solutionFileName = computed(() => selectedLanguage.value.fileName);
const monacoTheme = computed(() => (appMode.value === 'light' ? 'eclipse' : 'vs-dark'));

const eclipseTheme = {
    base: 'vs',
    inherit: true,
    rules: [
        { token: 'keyword', foreground: '7F0055', fontStyle: 'bold' },
        { token: 'comment', foreground: '3F7F5F' },
        { token: 'string', foreground: '2A00FF' },
        { token: 'number', foreground: '000000' },
    ],
    colors: {
        'editor.background': '#f2f6f8',
        'editor.foreground': '#17303c',
        'editor.lineHighlightBackground': '#e4edf1',
        'editor.selectionBackground': '#c8e4ee',
    },
};

const currentMember = computed(() => page.props.auth?.member ?? {});
const isAdmin = computed(() => [true, 1, '1', 'true'].includes(currentMember.value.is_admin));
const adminStatusMessage = computed(() => {
    if (currentMember.value.is_admin === undefined || currentMember.value.is_admin === null) {
        return 'Admin status not available yet.';
    }
    return isAdmin.value ? 'You are in admin mode.' : 'You are in user mode.';
});
const dashboardCounts = computed(() => props.dashboardCounts || {
    total_users: 0,
    total_admins: 0,
    problems_on_site: 0,
    created_problems: 0,
    submissions: 0,
});

const handleBeforeMount = (monaco) => {
    monaco.editor.defineTheme('eclipse', eclipseTheme);
};

const syncAppMode = () => {
    appMode.value = document.documentElement.dataset.authBg || localStorage.getItem('authBackgroundMode') || 'dark';
};

onMounted(() => {
    syncAppMode();
    modeObserver = new MutationObserver(syncAppMode);
    modeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-auth-bg'],
    });
});

onBeforeUnmount(() => {
    modeObserver?.disconnect();
});

const solutionEditorOptions = computed(() => ({
    fontSize: editorFontSize.value,
    automaticLayout: true,
    minimap: { enabled: true },
    scrollBeyondLastLine: false,
    wordWrap: 'on',
    padding: {
        top: 14,
        bottom: 14,
    },
}));

const splitParameters = (parameterList) =>
    parameterList
        .split(',')
        .map((parameter) => parameter.trim())
        .filter(Boolean);

const normalizeScriptParameter = (parameter) =>
    parameter
        .replace(/^\.{3}/, '')
        .replace(/\s*=.*$/, '')
        .replace(/\s*:\s*.+$/, '')
        .trim();

const normalizeTypedParameter = (parameter) => {
    const cleaned = parameter.replace(/\s*=.*$/, '').trim();
    const segments = cleaned.split(/\s+/);
    const lastSegment = segments.at(-1) ?? cleaned;

    return lastSegment.replace(/^[*&]+/, '').replace(/\[\]$/, '').trim();
};

const extractParameters = (source, expression, normalizer) => {
    const match = source.match(expression);

    if (!match) {
        return { detected: false, parameters: [], returnType: null };
    }

    return {
        detected: true,
        parameters: splitParameters(match[1]).map(normalizer).filter(Boolean),
        returnType: match[2]?.trim() ?? null,
    };
};

const parseSolutionSignature = (source, language) => {
    switch (language) {
        case 'python':
            return extractParameters(source, /def\s+solution\s*\(([^)]*)\)\s*:/s, normalizeScriptParameter);
        case 'java': {
            const match = source.match(
                /(?:public|private|protected|static|final|\s)*([A-Za-z_][\w<>\[\]]*)\s+solution\s*\(([^)]*)\)/s,
            );

            if (!match) {
                return { detected: false, parameters: [], returnType: null };
            }

            return {
                detected: true,
                parameters: splitParameters(match[2]).map(normalizeTypedParameter).filter(Boolean),
                returnType: match[1].trim(),
            };
        }
        case 'c': {
            const match = source.match(/([A-Za-z_][\w\s*]*?)\s+solution\s*\(([^)]*)\)/s);

            if (!match) {
                return { detected: false, parameters: [], returnType: null };
            }

            return {
                detected: true,
                parameters: splitParameters(match[2]).map(normalizeTypedParameter).filter(Boolean),
                returnType: match[1].trim(),
            };
        }
        case 'javascript':
        case 'typescript': {
            const functionMatch = source.match(/function\s+solution\s*\(([^)]*)\)/s);

            if (functionMatch) {
                return {
                    detected: true,
                    parameters: splitParameters(functionMatch[1]).map(normalizeScriptParameter).filter(Boolean),
                    returnType: null,
                };
            }

            const arrowMatch = source.match(/(?:const|let|var)\s+solution\s*=\s*\(([^)]*)\)\s*=>/s);

            if (arrowMatch) {
                return {
                    detected: true,
                    parameters: splitParameters(arrowMatch[1]).map(normalizeScriptParameter).filter(Boolean),
                    returnType: null,
                };
            }

            return { detected: false, parameters: [], returnType: null };
        }
        default:
            return { detected: false, parameters: [], returnType: null };
    }
};

const solutionSignature = computed(() => parseSolutionSignature(form.solution, form.language));
const parameterNames = computed(() => solutionSignature.value.parameters);
const areArgumentsFilled = computed(() =>
    form.testCases.every((testCase) => testCase.arguments.every((argument) => argument.trim() !== '')),
);
const isFormComplete = computed(() =>
    form.title.trim() !== '' &&
    form.description.trim() !== '' &&
    form.solution.trim() !== '' &&
    form.explanation.trim() !== '' &&
    form.visibility.trim() !== '' &&
    form.difficulty.trim() !== '' &&
    form.language.trim() !== '' &&
    solutionSignature.value.detected &&
    areArgumentsFilled.value,
);
const areTestCasesValid = computed(() =>
    form.testCases.length === 2 &&
    form.testCases.every((testCase) => testCase.status === 'success'),
);
const canCreateProblem = computed(() =>
    isFormComplete.value &&
    areTestCasesValid.value &&
    !isCreating.value &&
    !form.testCases.some((testCase) => testCase.isValidating),
);

const resetValidationState = () => {
    form.testCases.forEach((testCase) => {
        testCase.result = '';
        testCase.message = '';
        testCase.status = 'idle';
        testCase.isValidating = false;
    });
};

watch(
    parameterNames,
    (nextParameters) => {
        form.testCases.forEach((testCase) => {
            const previousArguments = [...testCase.arguments];
            testCase.arguments = nextParameters.map((_, index) => previousArguments[index] ?? '');
        });
    },
    { immediate: true },
);

watch(
    () => form.language,
    (nextLanguage) => {
        form.solution = getLanguageConfig(nextLanguage).template;
        createSuccess.value = '';
        createError.value = '';
        resetValidationState();
    },
);

watch(
    () => form.solution,
    (_, previousValue) => {
        if (previousValue === undefined) {
            return;
        }

        createSuccess.value = '';
        createError.value = '';
        resetValidationState();
    },
);

watch(
    () => form.testCases.map((testCase) => testCase.arguments.join('\u0001')).join('\u0002'),
    (_, previousValue) => {
        if (previousValue === undefined) {
            return;
        }

        createSuccess.value = '';
        createError.value = '';
        resetValidationState();
    },
);

watch(
    () => [form.title, form.description, form.explanation, form.visibility, form.difficulty],
    (_, previousValue) => {
        if (previousValue === undefined) {
            return;
        }

        createSuccess.value = '';
        createError.value = '';
    },
);

const normalizeExecutionOutput = (value) => {
    if (typeof value !== 'string') {
        return '';
    }

    return value.replace(/\r\n/g, '\n').replace(/\n$/, '');
};

const getTestCaseArgumentsSource = (testCase) => testCase.arguments.map((argument) => argument.trim()).join(', ');

const formatArgumentPreview = (testCase) => {
    if (!solutionSignature.value.detected) {
        return 'Signature not detected yet.';
    }

    if (parameterNames.value.length === 0) {
        return 'solution()';
    }

    return parameterNames.value
        .map((parameterName, index) => `${parameterName}: ${testCase.arguments[index] || '...'}`)
        .join(', ');
};

const getMissingArgumentName = (testCase) => {
    const missingIndex = testCase.arguments.findIndex((argument) => argument.trim() === '');

    return missingIndex === -1 ? null : parameterNames.value[missingIndex];
};

const indentBlock = (source, spaces) => {
    const indentation = ' '.repeat(spaces);

    return source
        .split('\n')
        .map((line) => `${indentation}${line}`)
        .join('\n');
};

const getCPrintStatement = (returnType, invocation) => {
    const normalizedReturnType = (returnType ?? '').replace(/\s+/g, ' ').trim().toLowerCase();

    if (normalizedReturnType === '' || normalizedReturnType === 'void') {
        return `  ${invocation};`;
    }

    if (normalizedReturnType.includes('char') && normalizedReturnType.includes('*')) {
        return `  printf("%s", ${invocation});`;
    }

    if (normalizedReturnType.includes('float') || normalizedReturnType.includes('double')) {
        return `  printf("%g", (double) ${invocation});`;
    }

    if (normalizedReturnType.includes('char') && !normalizedReturnType.includes('*')) {
        return `  printf("%c", ${invocation});`;
    }

    if (normalizedReturnType.includes('*')) {
        return `  printf("%p", (void*) ${invocation});`;
    }

    return `  printf("%lld", (long long) ${invocation});`;
};

const buildValidationSource = (language, argumentsSource) => {
    const invocationArguments = argumentsSource.trim();
    const callExpression = `solution(${invocationArguments})`;

    switch (language) {
        case 'python':
            return `import json

${form.solution}

__algo_result = ${callExpression}
if isinstance(__algo_result, str):
    print(__algo_result)
elif __algo_result is None:
    print("None")
else:
    print(json.dumps(__algo_result))`;
        case 'java':
            return `public class Main {
${indentBlock(form.solution, 2)}

  public static void main(String[] args) {
    Main obj = new Main();
    Object __algo_result = obj.${callExpression};
    System.out.println(String.valueOf(__algo_result));
  }
}`;
        case 'c':
            return `#include <stdio.h>
#include <stdbool.h>

${form.solution}

int main() {
${getCPrintStatement(solutionSignature.value.returnType, callExpression)}
  return 0;
}`;
        case 'javascript':
        case 'typescript':
        default:
            return `${form.solution}

const __algoResult = ${callExpression};

if (typeof __algoResult === 'string') {
  console.log(__algoResult);
} else if (typeof __algoResult === 'undefined') {
  console.log('undefined');
} else {
  console.log(JSON.stringify(__algoResult));
}`;
    }
};

const readExecutionOutput = (submission) =>
    normalizeExecutionOutput(
        submission.stdout ??
        submission.stderr ??
        submission.compile_output ??
        'No output returned.',
    );

const validateTestCase = async (testCase) => {
    if (!form.solution.trim()) {
        testCase.status = 'error';
        testCase.message = 'Add a solution before validating an example.';
        testCase.result = '';
        return;
    }

    if (!solutionSignature.value.detected) {
        testCase.status = 'error';
        testCase.message = 'Write a recognizable solution signature so the argument fields can be generated.';
        testCase.result = '';
        return;
    }

    const missingArgumentName = getMissingArgumentName(testCase);

    if (missingArgumentName) {
        testCase.status = 'error';
        testCase.message = `Fill in the "${missingArgumentName}" argument before validating.`;
        testCase.result = '';
        return;
    }

    testCase.isValidating = true;
    testCase.status = 'running';
    testCase.message = 'Running through Piston...';

    try {
        const response = await axios.post(
            route('execute'),
            {
                language: form.language,
                code: buildValidationSource(form.language, getTestCaseArgumentsSource(testCase)),
            },
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        const submission = response.data.data;
        testCase.result = readExecutionOutput(submission);

        if (submission.status === 'Completed') {
            testCase.status = 'success';
            testCase.message = 'Validated. The returned value below came back from the execution API.';
            return;
        }

        testCase.status = 'error';
        testCase.message = 'Execution failed. The compiler or runtime output is shown below.';
    } catch (error) {
        testCase.status = 'error';
        testCase.result = normalizeExecutionOutput(
            error.response?.data?.message ?? error.message ?? 'Unable to validate this example.',
        );
        testCase.message = 'The validation request did not complete successfully.';
    } finally {
        testCase.isValidating = false;
    }
};

const validateAllTestCases = async () => {
    for (const testCase of form.testCases) {
        await validateTestCase(testCase);
    }
};

const buildProblemPayload = () => ({
    title: form.title.trim(),
    description: form.description.trim(),
    language: form.language,
    difficulty: form.difficulty,
    solution: form.solution,
    explanation: form.explanation.trim(),
    visibility: form.visibility,
    test_cases: form.testCases.map((testCase) => ({
        label: testCase.label,
        arguments: testCase.arguments.map((argument) => argument.trim()),
        input: getTestCaseArgumentsSource(testCase),
        expected_output: testCase.result,
        status: testCase.status,
        is_default: true,
    })),
});

const readRequestError = (error, fallback) => {
    const validationErrors = error.response?.data?.errors;

    if (validationErrors) {
        const firstError = Object.values(validationErrors).flat()[0];

        if (typeof firstError === 'string' && firstError !== '') {
            return firstError;
        }
    }

    return error.response?.data?.message ?? error.message ?? fallback;
};

const createProblem = async () => {
    if (!canCreateProblem.value) {
        return;
    }

    isCreating.value = true;
    createSuccess.value = '';
    createError.value = '';

    try {
        const response = await axios.post(
            route('problem-creation.store'),
            buildProblemPayload(),
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        const problem = response.data?.data?.problem;
        createSuccess.value = problem
            ? `Problem "${problem.title}" created successfully.`
            : 'Problem created successfully.';
    } catch (error) {
        createError.value = readRequestError(error, 'Unable to create the problem right now.');
    } finally {
        isCreating.value = false;
    }
};
</script>

<template>
    <Head title="Problem Creation" />

    <AuthenticatedLayout>

        <div class="admin-status-banner" :class="{ 'admin-active': isAdmin, 'admin-inactive': !isAdmin }">
            <p>{{ adminStatusMessage }}</p>
        </div>
        <section v-if="activeView === 'list'" class="dashboard-shell panel" style="margin: 2rem; padding: 2rem;">
            <div v-if="isAdmin" class="admin-dashboard-panel">
                <h3>Admin dashboard</h3>
                <div class="admin-dashboard-cards">
                    <article class="dashboard-card gradient-blue">
                        <div class="card-top"><svg class="card-icon" viewBox="0 0 24 24" fill="none"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" fill="currentColor"/></svg><span>Total users</span></div>
                        <strong>{{ dashboardCounts.total_users }}</strong>
                    </article>
                    <article class="dashboard-card gradient-green">
                        <div class="card-top"><svg class="card-icon" viewBox="0 0 24 24" fill="none"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z" fill="currentColor"/></svg><span>Admins</span></div>
                        <strong>{{ dashboardCounts.total_admins }}</strong>
                    </article>
                    <article class="dashboard-card gradient-indigo">
                        <div class="card-top"><svg class="card-icon" viewBox="0 0 24 24" fill="none"><path d="M3 13h18v-2H3v2z" fill="currentColor"/></svg><span>Problems</span></div>
                        <strong>{{ dashboardCounts.problems_on_site }}</strong>
                    </article>
                    <article class="dashboard-card gradient-orange">
                        <div class="card-top"><svg class="card-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4z" fill="currentColor"/></svg><span>Your problems</span></div>
                        <strong>{{ dashboardCounts.created_problems }}</strong>
                    </article>
                    <article class="dashboard-card gradient-pink">
                        <div class="card-top"><svg class="card-icon" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Submissions</span></div>
                        <strong>{{ dashboardCounts.submissions }}</strong>
                    </article>
                </div>
            </div>
            <div class="panel-head" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <span class="panel-kicker">Workspace Manager</span>
                    <h2>Your Problems</h2>
                </div>
                <button type="button" class="primary-action" @click="openCreateMode">
                    + Create New Problem
                </button>
            </div>

            <div v-if="displayProblems.length === 0" class="empty-state-container" style="text-align: center; padding: 4rem 1rem;">
                <p style="opacity: 0.6; margin-bottom: 1.5rem;">You have not configured or published any tasks yet.</p>
                <button type="button" class="secondary-action" @click="openCreateMode">Set up your first problem</button>
            </div>

            <div v-else class="table-responsive" style="width: 100%; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px;">
                            <th style="padding: 12px;">Problem Details</th>
                            <th style="padding: 12px;">Difficulty</th>
                            <th style="padding: 12px;">Default Environment</th>
                            <th style="padding: 12px; text-align: right;">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="problem in displayProblems" :key="problem.id" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 12px;">
                                <strong>{{ problem.title }}</strong>
                                <div style="font-size: 0.8rem; opacity: 0.5;">
                                    Visibility: {{ problem.visibility || 'public' }}
                                    <span v-if="problem.fake" style="margin-left: 0.6rem; padding: 0.12rem 0.45rem; border-radius: 999px; background: rgba(59,130,246,0.12); color: #1d4ed8; font-size: 0.75rem;">Sample</span>
                                </div>
                            </td>
                            <td style="padding: 12px;">
                                <span :class="['difficulty-pill', `difficulty-${problem.difficulty}`]" style="display: inline-block; padding: 2px 8px; font-size: 0.75rem;">
                                    {{ problem.difficulty }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-transform: capitalize;">{{ problem.language }}</td>
                            <td style="padding: 12px; text-align: right;">
                                <button type="button" class="secondary-action" style="margin-right: 0.5rem; padding: 4px 10px; font-size: 0.85rem;" @click="openEditMode(problem)">
                                    Modify
                                </button>
                                <button type="button" class="secondary-action action-secondary" style="padding: 4px 10px; font-size: 0.85rem; border-color: rgba(239,68,68,0.4); color: #ef4444;" @click="deleteProblem(problem.problemId)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section v-else class="creation-shell">
            <div style="margin: 0 2rem 1.5rem 2rem;">
                <button type="button" class="secondary-action" @click="cancelForm">
                    ← Back to Dashboard List
                </button>
            </div>

        <section class="creation-shell">
            <div class="creation-grid">
                <div class="creation-main">


                    <form class="creation-form" @submit.prevent="validateAllTestCases">
                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <span class="panel-kicker">Problem Setup</span>
                                    <h2>Core details</h2>
                                </div>
                                <div :class="['difficulty-pill', difficultyTone]">
                                    {{ form.difficulty }}
                                </div>
                            </div>

                            <div class="field-stack">
                                <label class="field">
                                    <span class="field-label">Problem title</span>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        placeholder="e.g. Longest Subarray With Equal Sum"
                                    />
                                </label>

                                <label class="field">
                                    <span class="field-label">Problem difficulty</span>
                                    <select v-model="form.difficulty">
                                        <option value="easy">Easy</option>
                                        <option value="medium">Medium</option>
                                        <option value="hard">Hard</option>
                                    </select>
                                </label>

                                <label class="field">
                                    <span class="field-label">Problem description</span>
                                    <textarea
                                        v-model="form.description"
                                        rows="8"
                                        placeholder="Describe the challenge, input format, constraints, and expected behavior."
                                    />
                                </label>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <span class="panel-kicker">Reference Logic</span>
                                    <h2>Problem solution</h2>
                                </div>
                                <div class="editor-toolbar">
                                    <label class="language-control">
                                        <span>Language</span>
                                        <select v-model="form.language">
                                            <option
                                                v-for="option in languageOptions"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </label>
                                </div>
                            </div>

                            <div class="field editor-field">
                                <div class="signature-bar">
                                    <span class="field-label">Problem solution</span>
                                    <div v-if="solutionSignature.detected" class="signature-chip-group">
                                        <span class="signature-chip">
                                            {{
                                                parameterNames.length === 0
                                                    ? 'solution()'
                                                    : `solution(${parameterNames.join(', ')})`
                                            }}
                                        </span>
                                    </div>
                                    <p v-else class="signature-warning">
                                        Write a recognizable `solution(...)` signature to generate test
                                        case inputs.
                                    </p>
                                </div>

                                <div class="editor-surface solution-editor">
                                    <div class="editor-surface-top">
                                        <span class="editor-dot"></span>
                                        <span class="editor-dot"></span>
                                        <span class="editor-dot"></span>
                                        <strong>{{ solutionFileName }}</strong>
                                    </div>
                                    <VueMonacoEditor
                                        v-model:value="form.solution"
                                        :language="form.language"
                                        :theme="monacoTheme"
                                        :options="solutionEditorOptions"
                                        class="solution-monaco"
                                        style="height: 180px"
                                        @before-mount="handleBeforeMount"
                                    />
                                </div>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <span class="panel-kicker">Explanation</span>
                                    <h2>Describe the approach</h2>
                                </div>
                                <span class="panel-tag">Required</span>
                            </div>

                            <div class="field-stack">
                                <label class="field">
                                    <span class="field-label">Explanation</span>
                                    <textarea
                                        v-model="form.explanation"
                                        rows="7"
                                        placeholder="Explain the idea, why the algorithm works, and any important tradeoffs."
                                    />
                                </label>

                                <fieldset class="visibility-group">
                                    <legend class="field-label">Visibility</legend>
                                    <label class="visibility-option">
                                        <input v-model="form.visibility" type="radio" value="public" />
                                        <span>
                                            <strong>Public</strong>
                                            <small>Visible in the shared problem set.</small>
                                        </span>
                                    </label>
                                    <label class="visibility-option">
                                        <input v-model="form.visibility" type="radio" value="private" />
                                        <span>
                                            <strong>Private</strong>
                                            <small>Only available to you for now.</small>
                                        </span>
                                    </label>
                                </fieldset>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <div>
                                    <span class="panel-kicker">Examples</span>
                                    <h2>Two test case examples</h2>
                                </div>
                                <span class="panel-tag">Dynamic Args</span>
                            </div>

                            <div class="testcase-grid">
                                <article
                                    v-for="testCase in form.testCases"
                                    :key="testCase.id"
                                    :class="['testcase-card', `testcase-${testCase.status}`]"
                                >
                                    <div class="testcase-head">
                                        <strong>{{ testCase.label }}</strong>
                                        <span>{{ parameterNames.length }} argument{{ parameterNames.length === 1 ? '' : 's' }}</span>
                                    </div>

                                    <div v-if="solutionSignature.detected && parameterNames.length > 0" class="parameter-grid">
                                        <label
                                            v-for="(parameterName, index) in parameterNames"
                                            :key="`${testCase.id}-${parameterName}-${index}`"
                                            class="field"
                                        >
                                            <span class="field-label">{{ parameterName }}</span>
                                            <input
                                                v-model="testCase.arguments[index]"
                                                type="text"
                                                :placeholder="`Enter ${parameterName}`"
                                            />
                                        </label>
                                    </div>

                                    <div v-else-if="solutionSignature.detected" class="signature-empty">
                                        This solution takes no arguments. Validation will call `solution()`.
                                    </div>

                                    <div v-else class="signature-empty signature-empty-warning">
                                        Argument fields will appear here once the solution signature is detected.
                                    </div>

                                    <div class="testcase-actions">
                                        <button
                                            type="button"
                                            :class="[
                                                'secondary-action',
                                                'testcase-validate-button',
                                                `validate-${testCase.status}`,
                                            ]"
                                            :disabled="testCase.isValidating"
                                            @click="validateTestCase(testCase)"
                                        >
                                            {{ testCase.isValidating ? 'Validating ...' : 'Validate' }}
                                        </button>
                                        <p class="testcase-hint">
                                            {{ formatArgumentPreview(testCase) }}
                                        </p>
                                    </div>

                                    <label class="field">
                                        <span class="field-label">API result</span>
                                        <textarea
                                            v-model="testCase.result"
                                            class="result-box"
                                            rows="4"
                                            readonly
                                            placeholder="Returned output appears here after validation."
                                        />
                                    </label>

                                    <p :class="['testcase-message', `message-${testCase.status}`]">
                                        {{
                                            testCase.message ||
                                            'Validate this example to fetch the returned value from Piston.'
                                        }}
                                    </p>
                                </article>
                            </div>
                        </section>

                        <section class="action-strip">
                            <div class="action-buttons">
                                <button type="submit" class="secondary-action action-secondary">
                                    Validate both examples
                                </button>
                                <button
                                    type="button"
                                    class="primary-action"
                                    :disabled="!canCreateProblem"
                                    @click="createProblem"
                                >
                                    {{ isCreating ? 'Creating...' : 'Create problem' }}
                                </button>
                            </div>
                            <p>
                                The create button stays locked until every field is filled and both
                                examples validate successfully.
                            </p>
                        </section>

                        <div v-if="createSuccess || createError" :class="['create-feedback', createError ? 'feedback-error' : 'feedback-success']">
                            {{ createError || createSuccess }}
                        </div>
                    </form>
                </div>

                <aside class="creation-sidebar">
                    <div class="sidebar-sticky-stack">
                        <section class="panel summary-panel">
                            <div class="panel-head">
                                <div>
                                    <span class="panel-kicker">Preview</span>
                                    <h2>Problem snapshot</h2>
                                </div>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Title</span>
                                <p>{{ form.title || 'Untitled problem' }}</p>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Language</span>
                                <p>{{ selectedLanguage.label }}</p>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Difficulty</span>
                                <p class="summary-difficulty">{{ form.difficulty }}</p>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Visibility</span>
                                <p class="summary-difficulty">{{ form.visibility }}</p>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Description</span>
                                <p>
                                    {{
                                        form.description ||
                                        'The problem statement preview will appear here as you write it.'
                                }}
                                </p>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Explanation</span>
                                <p>
                                    {{
                                        form.explanation ||
                                        'The solution explanation preview will appear here as you write it.'
                                    }}
                                </p>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Detected signature</span>
                                <p>
                                    {{
                                        solutionSignature.detected
                                            ? parameterNames.length === 0
                                                ? 'solution()'
                                                : `solution(${parameterNames.join(', ')})`
                                            : 'No recognizable solution signature yet.'
                                    }}
                                </p>
                            </div>

                            <div class="summary-block">
                                <span class="summary-label">Examples</span>
                                <ul class="summary-list">
                                    <li v-for="testCase in form.testCases" :key="testCase.id">
                                        <strong>{{ testCase.label }}:</strong>
                                        {{ formatArgumentPreview(testCase) }}
                                    </li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </aside>
            </div>
            </section>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.creation-shell {
    padding: 34px 24px 40px;
}

.creation-grid {
    width: min(1320px, 100%);
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1.4fr) minmax(300px, 0.72fr);
    gap: 24px;
    align-items: start;
}

.creation-main,
.creation-sidebar {
    display: grid;
    gap: 20px;
}

.creation-sidebar {
    position: sticky;
    top: 90px;
    align-self: start;
    height: fit-content;
    overflow: visible;
}



.admin-status-banner {
        width: min(1320px, 100%);
        margin: 1rem auto 0;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        font-size: 0.95rem;
        color: #111827;
        background: rgba(59, 130, 246, 0.12);
        border: 1px solid rgba(59, 130, 246, 0.25);
        box-shadow: 0 1px 8px rgba(15, 23, 42, 0.04);
    }

    .admin-status-banner.admin-inactive {
        background: rgba(239, 68, 68, 0.12);
        border-color: rgba(239, 68, 68, 0.25);
        color: #991b1b;
    }

    .admin-status-banner.admin-active {
        background: rgba(34, 197, 94, 0.12);
        border-color: rgba(34, 197, 94, 0.25);
        color: #065f46;
    }

.admin-dashboard-panel {
        margin-bottom: 1.5rem;
        padding: 1rem 1.25rem;
        border-radius: 16px;
        background: rgba(15, 23, 42, 0.04);
        border: 1px solid rgba(148, 163, 184, 0.24);
    }

    .admin-dashboard-panel h3 {
        margin: 0 0 1rem;
        font-size: 1.05rem;
        color: #111827;
    }

    .admin-dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .dashboard-card {
        padding: 1rem 1.1rem;
        border-radius: 14px;
        background: white;
        border: 1px solid rgba(209, 213, 219, 0.8);
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.03);
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
    }

    .card-top {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        color: rgba(17,24,39,0.9);
        font-weight: 600;
    }
    .card-icon { width: 22px; height: 22px; color: rgba(17,24,39,0.9);}
    .dashboard-card strong { font-size: 1.6rem; }

    .gradient-blue { background: linear-gradient(135deg, #eff6ff, #e0f2fe); border: none; }
    .gradient-green { background: linear-gradient(135deg, #ecfdf5, #bbf7d0); border: none; }
    .gradient-indigo { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border: none; }
    .gradient-orange { background: linear-gradient(135deg, #fff7ed, #ffedd5); border: none; }
    .gradient-pink { background: linear-gradient(135deg, #fff1f2, #ffe4e6); border: none; }

    .dashboard-card span {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .dashboard-card strong {
        font-size: 1.5rem;
        color: #111827;
    }

    .section-header {
    padding: 8px 4px 2px;
}

.eyebrow,
.panel-kicker,
.summary-label,
.testcase-head span {
    display: inline-block;
    color: var(--ab-cyan);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.section-header h1,
.panel h2 {
    margin: 10px 0 0;
    color: var(--site-text);
    line-height: 1.02;
}

.section-header h1 {
    max-width: 820px;
    font-size: clamp(34px, 4vw, 58px);
    font-weight: 850;
}

.section-copy {
    max-width: 760px;
    margin: 18px 0 0;
    color: var(--site-muted);
    font-size: 16px;
    line-height: 1.8;
}

.creation-form {
    display: grid;
    gap: 20px;
}

.panel {
    border: 1px solid var(--site-border);
    border-radius: 8px;
    background:
        linear-gradient(180deg, var(--site-panel-strong), var(--site-panel));
    box-shadow: var(--site-shadow);
    overflow: hidden;
}

.panel-head {
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 14px;
    padding: 20px 22px 0;
}

.panel-head h2 {
    font-size: 24px;
    font-weight: 800;
}

.panel-tag,
.difficulty-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 0 12px;
    border: 1px solid var(--site-border);
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
}

.panel-tag {
    background: rgba(56, 217, 255, 0.08);
    color: var(--ab-cyan);
}

.difficulty-pill {
    min-width: 92px;
}

.difficulty-easy {
    border-color: rgba(24, 242, 195, 0.28);
    background: rgba(24, 242, 195, 0.12);
    color: var(--ab-teal);
}

.difficulty-medium {
    border-color: rgba(255, 205, 84, 0.28);
    background: rgba(255, 205, 84, 0.12);
    color: #ffd872;
}

.difficulty-hard {
    border-color: rgba(255, 93, 122, 0.28);
    background: rgba(255, 93, 122, 0.12);
    color: #ff9db0;
}

.field-stack,
.summary-panel,
.notes-panel {
    padding: 20px 22px 22px;
}

.field-stack {
    display: grid;
    gap: 18px;
}

.field {
    display: grid;
    gap: 9px;
}

.editor-field {
    padding: 20px 22px 22px;
}

.field-label {
    color: var(--site-text);
    font-size: 13px;
    font-weight: 700;
}

.field input,
.field select,
.field textarea {
    width: 100%;
    border: 1px solid var(--site-border);
    border-radius: 8px;
    background: var(--site-input);
    color: var(--site-text);
    padding: 14px 15px;
    font-size: 14px;
    line-height: 1.6;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.field input::placeholder,
.field textarea::placeholder {
    color: var(--site-placeholder);
}

.field input:focus,
.field select:focus,
.field textarea:focus {
    outline: none;
    border-color: var(--ab-cyan);
    background: var(--site-input-focus);
    box-shadow: 0 0 0 3px rgba(56, 217, 255, 0.12);
}

.field textarea {
    resize: vertical;
}

.visibility-group {
    display: grid;
    gap: 12px;
    padding: 0;
    border: 0;
    margin: 0;
}

.visibility-option {
    display: flex;
    align-items: start;
    gap: 12px;
    padding: 14px 15px;
    border: 1px solid var(--site-border);
    border-radius: 8px;
    background: var(--site-input);
    cursor: pointer;
}

.visibility-option input {
    width: 16px;
    height: 16px;
    margin-top: 2px;
    accent-color: var(--ab-cyan);
}

.visibility-option span {
    display: grid;
    gap: 4px;
}

.visibility-option strong {
    color: var(--site-text);
    font-size: 14px;
}

.visibility-option small {
    color: var(--site-muted);
    font-size: 12px;
    line-height: 1.5;
}

.editor-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.language-control {
    display: grid;
    gap: 6px;
}

.language-control span {
    color: var(--site-muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.language-control select {
    min-width: 150px;
    border: 1px solid var(--site-border);
    border-radius: 8px;
    background: var(--site-input);
    color: var(--site-text);
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 700;
}

.language-control select:focus {
    outline: none;
    border-color: var(--ab-cyan);
    box-shadow: 0 0 0 3px rgba(56, 217, 255, 0.12);
}

.signature-bar {
    display: grid;
    gap: 10px;
}

.signature-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.signature-chip {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 0 12px;
    border: 1px solid rgba(24, 242, 195, 0.2);
    border-radius: 999px;
    background: rgba(24, 242, 195, 0.08);
    color: var(--ab-teal);
    font-size: 12px;
    font-weight: 800;
}

.signature-warning,
.signature-empty {
    margin: 0;
    color: var(--site-muted);
    font-size: 13px;
    line-height: 1.7;
}

.signature-empty {
    padding: 12px 14px;
    border: 1px dashed var(--site-border-soft);
    border-radius: 8px;
    background: var(--site-card);
}

.signature-empty-warning {
    color: var(--site-warning);
}

.editor-surface {
    overflow: hidden;
    border: 1px solid var(--site-border);
    border-radius: 8px;
    background: var(--site-input);
    box-shadow: inset 0 0 0 1px rgba(56, 217, 255, 0.04);
}

.editor-surface-top {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    border-bottom: 1px solid var(--site-border);
    background: var(--site-editor-top);
    color: var(--site-muted);
    font-size: 12px;
    font-weight: 700;
}

.editor-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
}

.editor-dot:nth-child(1) {
    background: var(--ab-danger);
}

.editor-dot:nth-child(2) {
    background: var(--ab-cyan);
}

.editor-dot:nth-child(3) {
    background: var(--ab-teal);
}

.solution-monaco {
    display: block;
    height: 180px !important;
}

.solution-editor :deep(.monaco-editor),
.solution-editor :deep(.overflow-guard) {
    border-radius: 0 0 8px 8px;
}

.testcase-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    padding: 20px 22px 22px;
}

.testcase-card {
    border: 1px solid var(--site-border-soft);
    border-radius: 8px;
    background: var(--site-card);
    padding: 18px;
    display: grid;
    gap: 16px;
}

.testcase-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--site-border-soft);
}

.testcase-head strong {
    color: var(--site-text);
    font-size: 16px;
    font-weight: 800;
}

.parameter-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.testcase-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.secondary-action.testcase-validate-button {
    min-width: 80px;
    min-height: 44px;
    padding: 0 18px;
    border-color: rgba(3, 17, 20, 0.42);
    background:
        linear-gradient(135deg, rgba(56, 217, 255, 0.18), rgba(24, 242, 195, 0.12)),
        var(--site-editor-top);
    box-shadow: 0 12px 24px rgba(4, 20, 29, 0.16);
    letter-spacing: 0.02em;
}

.secondary-action.testcase-validate-button:hover {
    border-color: rgba(56, 217, 255, 0.72);
    background:
        linear-gradient(135deg, rgba(56, 217, 255, 0.26), rgba(24, 242, 195, 0.16)),
        var(--site-editor-top);
    box-shadow: 0 16px 30px rgba(4, 20, 29, 0.22);
}

.secondary-action.testcase-validate-button.validate-success {
    border-color: rgba(24, 242, 195, 0.4);
    background:
        linear-gradient(135deg, rgba(24, 242, 195, 0.18), rgba(56, 217, 255, 0.12)),
        var(--site-editor-top);
}

.secondary-action.testcase-validate-button.validate-error {
    border-color: rgba(255, 93, 122, 0.42);
    background:
        linear-gradient(135deg, rgba(255, 93, 122, 0.16), rgba(255, 170, 118, 0.12)),
        var(--site-editor-top);
}

.secondary-action.testcase-validate-button.validate-running {
    border-color: rgba(56, 217, 255, 0.76);
    background:
        linear-gradient(135deg, rgba(56, 217, 255, 0.28), rgba(24, 242, 195, 0.18)),
        var(--site-editor-top);
    box-shadow: 0 0 0 1px rgba(56, 217, 255, 0.28), 0 18px 34px rgba(21, 113, 145, 0.2);
}

.secondary-action.testcase-validate-button:disabled {
    opacity: 0.88;
}

.secondary-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 14px;
    border: 1px solid var(--site-border);
    border-radius: 8px;
    background: var(--site-editor-top);
    color: var(--site-text);
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.secondary-action:hover {
    transform: translateY(-1px);
    border-color: var(--ab-border-strong);
    background: rgba(56, 217, 255, 0.1);
}

.secondary-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.testcase-hint,
.testcase-message {
    margin: 0;
    font-size: 13px;
    line-height: 1.7;
}

.testcase-hint {
    color: var(--site-muted);
}

.result-box {
    min-height: 110px;
    font-family: Consolas, 'Courier New', monospace;
}

.testcase-message {
    color: var(--site-muted);
}

.message-success {
    color: var(--ab-teal);
}

.message-error {
    color: #ffb3c1;
}

.message-running {
    color: var(--ab-cyan);
}

.testcase-success {
    border-color: rgba(24, 242, 195, 0.28);
}

.testcase-error {
    border-color: rgba(255, 93, 122, 0.28);
}

.testcase-running {
    border-color: rgba(56, 217, 255, 0.28);
}

.action-strip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 18px 22px;
    border: 1px solid var(--site-border);
    border-radius: 8px;
    background: var(--site-panel);
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.action-secondary {
    min-width: 180px;
}

.action-strip p,
.summary-panel p,
.note-list {
    margin: 0;
    color: var(--site-muted);
    font-size: 14px;
    line-height: 1.7;
}

.primary-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 46px;
    padding: 0 18px;
    border: 1px solid transparent;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
    color: #021218;
    font-size: 14px;
    font-weight: 900;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.primary-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(24, 242, 195, 0.2);
}

.primary-action:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.create-feedback {
    padding: 14px 16px;
    border: 1px solid var(--site-border);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
}

.feedback-success {
    border-color: rgba(24, 242, 195, 0.24);
    background: rgba(24, 242, 195, 0.1);
    color: var(--ab-teal);
}

.feedback-error {
    border-color: rgba(255, 93, 122, 0.24);
    background: rgba(255, 93, 122, 0.12);
    color: #ffb3c1;
}

.summary-panel {
    display: grid;
    gap: 18px;
}

.summary-block {
    display: grid;
    gap: 8px;
    padding-top: 16px;
    border-top: 1px solid var(--site-border-soft);
}

.summary-block:first-of-type {
    padding-top: 0;
    border-top: 0;
}

.summary-block p,
.summary-list {
    margin: 0;
    color: var(--site-text);
    font-size: 14px;
    line-height: 1.7;
    word-break: break-word;
}

.summary-difficulty {
    text-transform: capitalize;
}

.summary-list {
    padding-left: 18px;
}

.note-list {
    padding-left: 18px;
}

@media (max-width: 1080px) {
    .creation-grid {
        grid-template-columns: 1fr;
    }

    .creation-sidebar {
        position: static;
        height: auto;
    }

    .sidebar-sticky-stack {
        position: static;
    }
}

@media (max-width: 720px) {
    .creation-shell {
        padding: 26px 16px 32px;
    }

    .panel-head,
    .field-stack,
    .summary-panel,
    .notes-panel,
    .testcase-grid,
    .action-strip,
    .editor-field {
        padding-left: 16px;
        padding-right: 16px;
    }

    .panel-head,
    .editor-toolbar,
    .action-strip {
        flex-direction: column;
        align-items: stretch;
    }

    .testcase-grid,
    .parameter-grid {
        grid-template-columns: 1fr;
    }

    .primary-action {
        width: 100%;
    }
}
</style>
