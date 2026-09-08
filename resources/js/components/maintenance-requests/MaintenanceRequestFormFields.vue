<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    EquipmentOption,
    MaintenanceRequestFormData,
    SelectOption,
    UserOption,
} from '@/types/maintenance-request';

withDefaults(
    defineProps<{
        maintenanceRequest?: MaintenanceRequestFormData;
        equipmentOptions: EquipmentOption[];
        userOptions: UserOption[];
        priorities: SelectOption[];
        canAssign?: boolean;
        errors?: Record<string, string>;
    }>(),
    {
        maintenanceRequest: () => ({
            equipment_id: '',
            title: '',
            description: '',
            priority: '',
            assigned_to: null,
        }),
        canAssign: false,
        errors: () => ({}),
    },
);
</script>

<template>
    <div class="grid gap-5">
        <div class="grid gap-2">
            <Label for="equipment_id">Equipment</Label>
            <select
                id="equipment_id"
                name="equipment_id"
                :value="maintenanceRequest.equipment_id"
                class="border-input bg-background ring-offset-background focus-visible:ring-ring h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                required
            >
                <option value="" disabled>Select equipment</option>
                <option
                    v-for="equipment in equipmentOptions"
                    :key="equipment.id"
                    :value="equipment.id"
                >
                    {{ equipment.name }} · {{ equipment.code }}
                </option>
            </select>
            <InputError :message="errors.equipment_id" />
        </div>

        <div class="grid gap-2">
            <Label for="title">Request title</Label>
            <Input
                id="title"
                name="title"
                :default-value="maintenanceRequest.title"
                maxlength="255"
                required
                placeholder="Short summary, e.g. Mixer bearing vibration"
            />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="description">Fault / maintenance description</Label>
            <textarea
                id="description"
                name="description"
                :value="maintenanceRequest.description"
                rows="6"
                maxlength="2000"
                required
                placeholder="Describe the fault, symptoms and required work"
                class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring min-h-32 w-full rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
            />
            <InputError :message="errors.description" />
        </div>

        <div class="grid gap-2">
            <Label for="priority">Priority</Label>
            <select
                id="priority"
                name="priority"
                :value="maintenanceRequest.priority"
                class="border-input bg-background ring-offset-background focus-visible:ring-ring h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                required
            >
                <option value="" disabled>Select priority</option>
                <option
                    v-for="priority in priorities"
                    :key="priority.value"
                    :value="priority.value"
                >
                    {{ priority.label }}
                </option>
            </select>
            <InputError :message="errors.priority" />
        </div>

        <div v-if="canAssign" class="grid gap-2">
            <Label for="assigned_to">Assignee</Label>
            <select
                id="assigned_to"
                name="assigned_to"
                :value="maintenanceRequest.assigned_to ?? ''"
                class="border-input bg-background ring-offset-background focus-visible:ring-ring h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
            >
                <option value="">Unassigned</option>
                <option
                    v-for="user in userOptions"
                    :key="user.id"
                    :value="user.id"
                >
                    {{ user.name }} · {{ user.email }}
                </option>
            </select>
            <InputError :message="errors.assigned_to" />
        </div>
    </div>
</template>
