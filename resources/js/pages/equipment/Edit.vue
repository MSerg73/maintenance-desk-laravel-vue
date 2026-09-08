<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import EquipmentController from '@/actions/App/Http/Controllers/EquipmentController';
import EquipmentFormFields from '@/components/equipment/EquipmentFormFields.vue';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/equipment';
import type {
    EquipmentFormData,
    EquipmentStatusOption,
} from '@/types/equipment';

const props = defineProps<{
    equipment: EquipmentFormData & { id: number };
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
</script>

<template>
    <Head :title="`Edit ${equipment.name}`" />

    <div class="mx-auto w-full max-w-2xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Edit equipment
            </h1>
            <p class="text-muted-foreground text-sm">
                Update register data for {{ equipment.name }}.
            </p>
        </div>

        <Form
            v-bind="EquipmentController.update.form.put(equipment.id)"
            v-slot="{ errors, processing }"
            class="space-y-6 rounded-xl border p-6"
        >
            <EquipmentFormFields
                :equipment="equipment"
                :statuses="statuses"
                :errors="errors"
            />

            <div class="flex justify-end gap-2">
                <Button as-child variant="ghost">
                    <Link :href="index()">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Saving…' : 'Save changes' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
