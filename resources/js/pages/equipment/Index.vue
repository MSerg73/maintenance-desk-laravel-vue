<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CirclePlus, Pencil, Search, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { create, destroy, edit, index } from '@/routes/equipment';
import type {
    EquipmentFilters,
    EquipmentPaginator,
    EquipmentStatusOption,
} from '@/types/equipment';

const props = defineProps<{
    equipment: EquipmentPaginator;
    filters: EquipmentFilters;
    statuses: EquipmentStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Equipment',
                href: index(),
            },
        ],
    },
});

const page = usePage();
const q = ref(props.filters.q);
const status = ref(props.filters.status);

const success = computed(() => page.props.flash?.success);
const error = computed(() => page.props.flash?.error);

const applyFilters = (): void => {
    router.get(
        index.url(),
        {
            q: q.value || undefined,
            status: status.value || undefined,
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
    router.get(index.url(), {}, { preserveState: true, replace: true });
};

const removeEquipment = (id: number, name: string): void => {
    if (!window.confirm(`Delete equipment "${name}"?`)) {
        return;
    }

    router.delete(destroy.url(id), {
        preserveScroll: true,
    });
};

const statusClass = (value: string): string => {
    if (value === 'active') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300';
    }

    if (value === 'maintenance') {
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-300';
    }

    return 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300';
};
</script>

<template>
    <Head title="Equipment" />

    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Equipment</h1>
                <p class="text-muted-foreground text-sm">
                    Maintain the equipment register used by maintenance
                    requests.
                </p>
            </div>

            <Button as-child>
                <Link :href="create()">
                    <CirclePlus class="size-4" />
                    Add equipment
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
            class="grid gap-3 rounded-xl border p-4 md:grid-cols-[minmax(0,1fr)_220px_auto_auto]"
            @submit.prevent="applyFilters"
        >
            <div class="relative">
                <Search
                    class="text-muted-foreground absolute top-1/2 left-3 size-4 -translate-y-1/2"
                />
                <Input
                    v-model="q"
                    class="pl-9"
                    placeholder="Search by name, code or location"
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
                            <th class="px-4 py-3 font-medium">Equipment</th>
                            <th class="px-4 py-3 font-medium">Code</th>
                            <th class="px-4 py-3 font-medium">Location</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Requests
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in equipment.data"
                            :key="item.id"
                            class="border-b last:border-b-0"
                        >
                            <td class="px-4 py-3 font-medium">
                                {{ item.name }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ item.code }}
                            </td>
                            <td class="text-muted-foreground px-4 py-3">
                                {{ item.location || '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    variant="outline"
                                    :class="statusClass(item.status)"
                                >
                                    {{ item.status_label }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ item.maintenance_requests_count }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        as-child
                                        variant="ghost"
                                        size="icon"
                                        title="Edit"
                                    >
                                        <Link :href="edit(item.id)">
                                            <Pencil class="size-4" />
                                        </Link>
                                    </Button>

                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Delete"
                                        :disabled="
                                            item.maintenance_requests_count > 0
                                        "
                                        @click="
                                            removeEquipment(item.id, item.name)
                                        "
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="equipment.data.length === 0">
                            <td
                                colspan="6"
                                class="text-muted-foreground px-4 py-12 text-center"
                            >
                                No equipment matches the current filters.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-if="equipment.last_page > 1"
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
        >
            <p class="text-muted-foreground text-sm">
                Showing {{ equipment.from ?? 0 }}–{{ equipment.to ?? 0 }} of
                {{ equipment.total }}
            </p>

            <div class="flex flex-wrap gap-1">
                <Button
                    v-for="link in equipment.links"
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
