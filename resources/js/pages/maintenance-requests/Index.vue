<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CirclePlus, Pencil, Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import MaintenanceRequestController from '@/actions/App/Http/Controllers/MaintenanceRequestController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type {
    MaintenanceRequestFilters,
    MaintenanceRequestPaginator,
    SelectOption,
} from '@/types/maintenance-request';

const props = defineProps<{
    requests: MaintenanceRequestPaginator;
    filters: MaintenanceRequestFilters;
    statuses: SelectOption[];
    priorities: SelectOption[];
    permissions: {
        can_update: boolean;
        can_transition: boolean;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Maintenance requests',
                href: MaintenanceRequestController.index(),
            },
        ],
    },
});

const page = usePage();
const q = ref(props.filters.q);
const status = ref(props.filters.status);
const priority = ref(props.filters.priority);

const success = computed(() => page.props.flash?.success);
const error = computed(() => {
    const flashError = page.props.flash?.error;

    if (flashError) {
        return flashError;
    }

    const statusError = page.props.errors?.status;
    return typeof statusError === 'string' ? statusError : null;
});

const applyFilters = (): void => {
    router.get(
        MaintenanceRequestController.index.url(),
        {
            q: q.value || undefined,
            status: status.value || undefined,
            priority: priority.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = (): void => {
    q.value = '';
    status.value = '';
    priority.value = '';

    router.get(
        MaintenanceRequestController.index.url(),
        {},
        { preserveState: true, replace: true },
    );
};

const advanceStatus = (id: number, nextStatus: SelectOption): void => {
    router.patch(
        MaintenanceRequestController.updateStatus.url(id),
        {
            status: nextStatus.value,
        },
        {
            preserveScroll: true,
        },
    );
};

const formatDate = (value: string): string =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));

const statusClass = (value: string): string => {
    if (value === 'new') {
        return 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-300';
    }

    if (value === 'in_progress') {
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300';
    }

    if (value === 'done') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300';
    }

    return 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-300';
};
</script>

<template>
    <Head title="Maintenance requests" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Maintenance requests
                </h1>
                <p class="text-muted-foreground text-sm">
                    Track faults and work through the controlled maintenance
                    workflow.
                </p>
            </div>

            <Button as-child>
                <Link :href="MaintenanceRequestController.create()">
                    <CirclePlus class="size-4" />
                    New request
                </Link>
            </Button>
        </div>

        <div
            v-if="success"
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200"
        >
            {{ success }}
        </div>

        <div
            v-if="error"
            class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200"
        >
            {{ error }}
        </div>

        <form
            class="grid gap-3 rounded-xl border p-4 lg:grid-cols-[minmax(0,1fr)_190px_190px_auto_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="relative">
                <Search
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Input
                    v-model="q"
                    class="pl-9"
                    placeholder="Search title, description, equipment or code"
                />
            </div>

            <select
                v-model="status"
                class="border-input bg-background h-9 rounded-md border px-3 text-sm shadow-xs"
            >
                <option value="">All statuses</option>
                <option
                    v-for="item in statuses"
                    :key="item.value"
                    :value="item.value"
                >
                    {{ item.label }}
                </option>
            </select>

            <select
                v-model="priority"
                class="border-input bg-background h-9 rounded-md border px-3 text-sm shadow-xs"
            >
                <option value="">All priorities</option>
                <option
                    v-for="item in priorities"
                    :key="item.value"
                    :value="item.value"
                >
                    {{ item.label }}
                </option>
            </select>

            <Button type="submit" variant="secondary">Apply</Button>
            <Button type="button" variant="ghost" @click="clearFilters">
                Clear
            </Button>
        </form>

        <div class="overflow-hidden rounded-xl border">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 border-b text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 font-medium">Equipment</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Priority</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Assignee</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="item in requests.data"
                            :key="item.id"
                            class="border-b align-top last:border-b-0"
                        >
                            <td
                                class="text-muted-foreground px-4 py-3 text-xs whitespace-nowrap"
                            >
                                {{ formatDate(item.created_at) }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium">
                                    {{ item.equipment.name }}
                                </div>
                                <div
                                    class="text-muted-foreground font-mono text-xs"
                                >
                                    {{ item.equipment.code }}
                                </div>
                            </td>

                            <td class="max-w-md px-4 py-3">
                                <p class="font-medium">
                                    {{ item.title }}
                                </p>
                                <p
                                    class="text-muted-foreground mt-1 line-clamp-3 whitespace-pre-wrap"
                                >
                                    {{ item.description }}
                                </p>
                                <p class="text-muted-foreground mt-1 text-xs">
                                    Created by {{ item.creator.name }}
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <Badge variant="outline">{{
                                    item.priority_label
                                }}</Badge>
                            </td>

                            <td class="px-4 py-3">
                                <Badge
                                    variant="outline"
                                    :class="statusClass(item.status)"
                                >
                                    {{ item.status_label }}
                                </Badge>
                            </td>

                            <td class="px-4 py-3">
                                {{ item.assignee?.name ?? 'Unassigned' }}
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex min-w-36 justify-end gap-1">
                                    <Button
                                        v-if="permissions.can_update"
                                        as-child
                                        variant="ghost"
                                        size="icon"
                                        title="Edit request"
                                    >
                                        <Link
                                            :href="
                                                MaintenanceRequestController.edit(
                                                    item.id,
                                                )
                                            "
                                        >
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="
                                            permissions.can_transition &&
                                            item.next_status
                                        "
                                        size="sm"
                                        variant="secondary"
                                        @click="
                                            advanceStatus(
                                                item.id,
                                                item.next_status,
                                            )
                                        "
                                    >
                                        → {{ item.next_status.label }}
                                    </Button>

                                    <span
                                        v-else
                                        class="text-muted-foreground self-center text-xs"
                                    >
                                        Closed
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="requests.data.length === 0">
                            <td
                                colspan="7"
                                class="text-muted-foreground px-4 py-12 text-center"
                            >
                                No maintenance requests match the current
                                filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="requests.last_page > 1"
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
        >
            <p class="text-muted-foreground text-sm">
                Showing {{ requests.from ?? 0 }}–{{ requests.to ?? 0 }} of
                {{ requests.total }}
            </p>

            <div class="flex flex-wrap gap-1">
                <Button
                    v-for="link in requests.links"
                    :key="link.label"
                    as-child
                    size="sm"
                    :variant="link.active ? 'default' : 'outline'"
                    :disabled="!link.url"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-state
                        v-html="link.label"
                    />
                    <span v-else v-html="link.label" />
                </Button>
            </div>
        </div>
    </div>
</template>
