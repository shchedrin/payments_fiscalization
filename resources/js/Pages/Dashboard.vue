<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import { Table } from "@protonemedia/inertiajs-tables-laravel-query-builder";

defineProps(["payments"])
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <Table
                :resource="payments"
                :striped="true">
                <template v-slot:tableFilter="slotProps">
                    <input
                        placeholder="Custom Global Search Component..."
                        @input="alert(queryBuilderString)"
                    />
                </template>
            </Table>
        </div>
    </AuthenticatedLayout>
</template>
