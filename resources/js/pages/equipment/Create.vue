<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import EquipmentController from '@/actions/App/Http/Controllers/EquipmentController';
import EquipmentFormFields from '@/components/equipment/EquipmentFormFields.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/equipment';
import type { EquipmentStatusOption } from '@/types/equipment';

defineProps<{
    statuses: EquipmentStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Equipment',
                href: index(),
            },
            {
                title: 'Add equipment',
                href: create(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Add equipment" />

    <div class="mx-auto w-full max-w-2xl space-y-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Add equipment</h1>
            <p class="text-muted-foreground text-sm">
                Register a maintainable asset in Maintenance Desk.
            </p>
        </div>

        <Form
            v-bind="EquipmentController.store.form()"
            v-slot="{ errors, processing }"
            class="space-y-6 rounded-xl border p-6"
        >
            <EquipmentFormFields :statuses="statuses" :errors="errors" />

            <div class="flex justify-end gap-2">
                <Button as-child variant="ghost">
                    <Link :href="index()">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Saving…' : 'Create equipment' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
