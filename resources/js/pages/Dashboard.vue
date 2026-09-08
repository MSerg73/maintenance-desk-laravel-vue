<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

type DashboardStats = {
    equipment: {
        total: number;
        active: number;
        maintenance: number;
        out_of_service: number;
    };
    requests: {
        new: number;
        in_progress: number;
        done: number;
        closed: number;
        high_or_critical_open: number;
    };
};

type RecentRequest = {
    id: number;
    title: string;
    status: string;
    status_label: string;
    priority: string;
    priority_label: string;
    created_at: string;
    equipment: {
        name: string;
        code: string;
    };
    assignee: string | null;
};

defineProps<{
    stats: DashboardStats;
    recentRequests: RecentRequest[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatDateTime = (value: string): string =>
    new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Maintenance overview
                </h1>
                <p class="text-muted-foreground mt-1 text-sm">
                    Current equipment state and maintenance request workflow.
                </p>
            </div>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Equipment total</CardDescription>
                        <CardTitle class="text-3xl">
                            {{ stats.equipment.total }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-muted-foreground text-sm">
                        Registered maintainable assets.
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Active</CardDescription>
                        <CardTitle class="text-3xl">
                            {{ stats.equipment.active }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-muted-foreground text-sm">
                        Equipment currently available for operation.
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Maintenance</CardDescription>
                        <CardTitle class="text-3xl">
                            {{ stats.equipment.maintenance }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-muted-foreground text-sm">
                        Equipment requiring or undergoing maintenance.
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Out of service</CardDescription>
                        <CardTitle class="text-3xl">
                            {{ stats.equipment.out_of_service }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-muted-foreground text-sm">
                        Equipment unavailable for normal operation.
                    </CardContent>
                </Card>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>New</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ stats.requests.new }}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>In progress</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ stats.requests.in_progress }}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Done</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ stats.requests.done }}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Closed</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ stats.requests.closed }}
                        </CardTitle>
                    </CardHeader>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>High / critical open</CardDescription>
                        <CardTitle class="text-2xl">
                            {{ stats.requests.high_or_critical_open }}
                        </CardTitle>
                    </CardHeader>
                </Card>
            </section>

            <Card>
                <CardHeader>
                    <CardTitle>Recent maintenance requests</CardTitle>
                    <CardDescription>
                        Latest activity across the maintenance workflow.
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div
                        v-if="recentRequests.length === 0"
                        class="text-muted-foreground py-8 text-center text-sm"
                    >
                        No maintenance requests yet.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b text-left text-xs tracking-wide uppercase"
                                >
                                    <th class="px-3 py-3 font-medium">
                                        Request
                                    </th>
                                    <th class="px-3 py-3 font-medium">
                                        Equipment
                                    </th>
                                    <th class="px-3 py-3 font-medium">
                                        Priority
                                    </th>
                                    <th class="px-3 py-3 font-medium">
                                        Status
                                    </th>
                                    <th class="px-3 py-3 font-medium">
                                        Assignee
                                    </th>
                                    <th class="px-3 py-3 font-medium">
                                        Created
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="request in recentRequests"
                                    :key="request.id"
                                    class="border-b last:border-b-0"
                                >
                                    <td class="px-3 py-3">
                                        <div class="font-medium">
                                            {{ request.title }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div>{{ request.equipment.name }}</div>
                                        <div
                                            class="text-muted-foreground text-xs"
                                        >
                                            {{ request.equipment.code }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <Badge variant="outline">
                                            {{ request.priority_label }}
                                        </Badge>
                                    </td>
                                    <td class="px-3 py-3">
                                        <Badge variant="secondary">
                                            {{ request.status_label }}
                                        </Badge>
                                    </td>
                                    <td class="px-3 py-3">
                                        {{ request.assignee ?? 'Unassigned' }}
                                    </td>
                                    <td
                                        class="text-muted-foreground px-3 py-3 whitespace-nowrap"
                                    >
                                        {{ formatDateTime(request.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
