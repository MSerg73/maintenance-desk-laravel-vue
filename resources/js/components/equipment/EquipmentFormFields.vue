<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    EquipmentFormData,
    EquipmentStatusOption,
} from '@/types/equipment';

withDefaults(
    defineProps<{
        equipment?: EquipmentFormData;
        statuses: EquipmentStatusOption[];
        errors?: Record<string, string>;
    }>(),
    {
        equipment: () => ({
            name: '',
            code: '',
            location: null,
            status: 'active',
        }),
        errors: () => ({}),
    },
);
</script>

<template>
    <div class="grid gap-5">
        <div class="grid gap-2">
            <Label for="name">Equipment name</Label>
            <Input
                id="name"
                name="name"
                :default-value="equipment.name"
                required
                maxlength="255"
                placeholder="Packaging machine"
            />
            <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="code">Equipment code</Label>
            <Input
                id="code"
                name="code"
                :default-value="equipment.code"
                required
                maxlength="100"
                placeholder="EQ-1001"
            />
            <InputError :message="errors.code" />
        </div>

        <div class="grid gap-2">
            <Label for="location">Location</Label>
            <Input
                id="location"
                name="location"
                :default-value="equipment.location ?? ''"
                maxlength="255"
                placeholder="Production line 1"
            />
            <InputError :message="errors.location" />
        </div>

        <div class="grid gap-2">
            <Label for="status">Status</Label>
            <select
                id="status"
                name="status"
                :value="equipment.status"
                class="border-input bg-background ring-offset-background focus-visible:ring-ring h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                required
            >
                <option
                    v-for="status in statuses"
                    :key="status.value"
                    :value="status.value"
                >
                    {{ status.label }}
                </option>
            </select>
            <InputError :message="errors.status" />
        </div>
    </div>
</template>
