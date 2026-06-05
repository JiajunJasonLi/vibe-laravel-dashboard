<script setup>
import { computed, ref } from 'vue';

const logText = ref(`INFO Service started
ERROR Timeout while connecting to database
warning: database latency exceeded threshold
error retry failed after timeout`);

const keywordGroups = ref([
    { name: 'severity', keywords: ['error', 'warning'] },
    { name: 'systems', keywords: ['database', 'timeout'] },
]);

const results = ref(null);
const errorMessage = ref('');
const isSubmitting = ref(false);

const rows = computed(() => {
    if (!results.value?.groups) {
        return [];
    }

    return Object.entries(results.value.groups).flatMap(([groupName, group]) =>
        Object.entries(group.keywords).map(([keyword, count]) => ({
            groupName,
            keyword,
            count,
            groupTotal: group.total,
        })),
    );
});

const canSubmit = computed(() => {
    return logText.value.trim() !== '' && keywordGroups.value.some((group) => {
        return group.name.trim() !== '' && group.keywords.some((keyword) => keyword.trim() !== '');
    });
});

function addGroup() {
    keywordGroups.value.push({
        name: `group-${keywordGroups.value.length + 1}`,
        keywords: [''],
    });
}

function removeGroup(groupIndex) {
    keywordGroups.value.splice(groupIndex, 1);
}

function addKeyword(group) {
    group.keywords.push('');
}

function removeKeyword(group, keywordIndex) {
    group.keywords.splice(keywordIndex, 1);
}

function buildPayload() {
    const groups = {};

    keywordGroups.value.forEach((group) => {
        const name = group.name.trim();
        const keywords = group.keywords.map((keyword) => keyword.trim()).filter(Boolean);

        if (name && keywords.length > 0) {
            groups[name] = keywords;
        }
    });

    return {
        log_text: logText.value,
        keyword_groups: groups,
    };
}

async function submit() {
    errorMessage.value = '';
    isSubmitting.value = true;

    try {
        const response = await fetch('/api/log-stats', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(buildPayload()),
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload.message || 'Unable to analyze these logs.');
        }

        results.value = payload.data;
    } catch (error) {
        errorMessage.value = error.message;
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <main class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header class="flex flex-col gap-3 border-b border-zinc-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-teal-700">Log Stats</p>
                    <h1 class="mt-1 text-3xl font-semibold text-zinc-950">Keyword group analysis</h1>
                </div>

                <div class="rounded-md border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-700 shadow-sm">
                    <span class="font-medium text-zinc-950">{{ results?.total_matches ?? 0 }}</span>
                    total matches
                </div>
            </header>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(360px,0.9fr)]">
                <form class="space-y-5" @submit.prevent="submit">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                        <label for="log-text" class="text-sm font-medium text-zinc-900">Log text</label>
                        <textarea
                            id="log-text"
                            v-model="logText"
                            class="mt-2 min-h-96 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 font-mono text-sm leading-6 text-zinc-950 shadow-inner outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20"
                            placeholder="Paste logs here"
                        />
                    </div>

                    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-sm font-semibold text-zinc-950">Keyword groups</h2>
                            <button
                                type="button"
                                class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-800 transition hover:bg-zinc-50"
                                @click="addGroup"
                            >
                                Add group
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div
                                v-for="(group, groupIndex) in keywordGroups"
                                :key="groupIndex"
                                class="rounded-md border border-zinc-200 p-3"
                            >
                                <div class="flex gap-2">
                                    <input
                                        v-model="group.name"
                                        class="min-w-0 flex-1 rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20"
                                        placeholder="Group name"
                                    >
                                    <button
                                        type="button"
                                        class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40"
                                        :disabled="keywordGroups.length === 1"
                                        @click="removeGroup(groupIndex)"
                                    >
                                        Remove
                                    </button>
                                </div>

                                <div class="mt-3 space-y-2">
                                    <div
                                        v-for="(keyword, keywordIndex) in group.keywords"
                                        :key="keywordIndex"
                                        class="flex gap-2"
                                    >
                                        <input
                                            v-model="group.keywords[keywordIndex]"
                                            class="min-w-0 flex-1 rounded-md border border-zinc-300 px-3 py-2 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20"
                                            placeholder="Keyword"
                                        >
                                        <button
                                            type="button"
                                            class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40"
                                            :disabled="group.keywords.length === 1"
                                            @click="removeKeyword(group, keywordIndex)"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="mt-3 rounded-md px-3 py-2 text-sm font-medium text-teal-700 transition hover:bg-teal-50"
                                    @click="addKeyword(group)"
                                >
                                    Add keyword
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <button
                            type="submit"
                            class="rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="isSubmitting || !canSubmit"
                        >
                            {{ isSubmitting ? 'Analyzing...' : 'Analyze logs' }}
                        </button>

                        <p v-if="errorMessage" class="text-sm font-medium text-red-700">{{ errorMessage }}</p>
                    </div>
                </form>

                <aside class="rounded-lg border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-200 p-4">
                        <h2 class="text-base font-semibold text-zinc-950">Counts</h2>
                    </div>

                    <div v-if="rows.length" class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-50 text-xs font-semibold uppercase text-zinc-600">
                                <tr>
                                    <th class="px-4 py-3">Group</th>
                                    <th class="px-4 py-3">Keyword</th>
                                    <th class="px-4 py-3 text-right">Matches</th>
                                    <th class="px-4 py-3 text-right">Group total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                <tr v-for="row in rows" :key="`${row.groupName}-${row.keyword}`">
                                    <td class="px-4 py-3 font-medium text-zinc-950">{{ row.groupName }}</td>
                                    <td class="px-4 py-3 font-mono text-zinc-700">{{ row.keyword }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-zinc-950">{{ row.count }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-zinc-600">{{ row.groupTotal }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t border-zinc-200 bg-zinc-50">
                                <tr>
                                    <td colspan="2" class="px-4 py-3 font-semibold text-zinc-950">Total</td>
                                    <td colspan="2" class="px-4 py-3 text-right font-semibold tabular-nums text-zinc-950">
                                        {{ results.total_matches }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div v-else class="p-8 text-center text-sm text-zinc-600">
                        Run an analysis to see keyword counts.
                    </div>
                </aside>
            </section>
        </div>
    </main>
</template>
