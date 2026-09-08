<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import MaintenanceRequestController from '@/actions/App/Http/Controllers/MaintenanceRequestController';
import MaintenanceRequestFormFields from '@/components/maintenance-requests/MaintenanceRequestFormFields.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type {
    EquipmentOption,
    MaintenanceRequestFormData,
    SelectOption,
    UserOption,
} from '@/types/maintenance-request';

defineProps<{
    maintenanceRequest: MaintenanceRequestFormData & {
        id: number;
        status: string;
        status_label: string;
    };
    equipmentOptions: EquipmentOption[];
    userOptions: UserOption[];
    priorities: SelectOption[];
    canAssign: boolean;
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
</script>

<template>
    <Head title="Edit maintenance request" />

    <div class="mx-auto w-full max-w-2xl space-y-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Edit maintenance request
                </h1>
                <p class="text-muted-foreground text-sm">
                    Request details can be edited here; workflow status changes
                    are performed from the request register.
                </p>
            </div>

            <Badge variant="outline">
                {{ maintenanceRequest.status_label }}
            </Badge>
        </div>

        <Form
            v-bind="
                MaintenanceRequestController.update.form.put(
                    maintenanceRequest.id,
                )
            "
            v-slot="{ errors, processing }"
            class="space-y-6 rounded-xl border p-6"
        >
            <MaintenanceRequestFormFields
                :maintenance-request="maintenanceRequest"
                :equipment-options="equipmentOptions"
                :user-options="userOptions"
                :priorities="priorities"
                :can-assign="canAssign"
                :errors="errors"
            />

            <div class="flex justify-end gap-2">
                <Button as-child variant="ghost">
                    <Link :href="MaintenanceRequestController.index()"
                        >Cancel</Link
                    >
                </Button>

                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Saving…' : 'Save changes' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
