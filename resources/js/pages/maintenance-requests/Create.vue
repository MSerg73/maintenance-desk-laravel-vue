<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import MaintenanceRequestController from '@/actions/App/Http/Controllers/MaintenanceRequestController';
import MaintenanceRequestFormFields from '@/components/maintenance-requests/MaintenanceRequestFormFields.vue';
import { Button } from '@/components/ui/button';
import type {
    EquipmentOption,
    SelectOption,
    UserOption,
} from '@/types/maintenance-request';

defineProps<{
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
            {
                title: 'New request',
                href: MaintenanceRequestController.create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="New maintenance request" />

    <div class="mx-auto w-full max-w-2xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                New maintenance request
            </h1>
            <p class="text-muted-foreground text-sm">
                New requests always enter the workflow in the New state.
            </p>
        </div>

        <Form
            v-bind="MaintenanceRequestController.store.form()"
            v-slot="{ errors, processing }"
            class="space-y-6 rounded-xl border p-6"
        >
            <MaintenanceRequestFormFields
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
                    {{ processing ? 'Saving…' : 'Create request' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
