<template>
    <div class="mb-6">
        <v-breadcrumbs
            class="!tw-pl-0 !tw-pt-0"
            active-color="blue"
            :items="[
                {
                    title: 'Dashboard',
                    active: true,
                    href: '/admin/dashboard',
                },
                ...crumbs,
            ]"
            divider="/"
        ></v-breadcrumbs>
        <v-divider class="border-opacity-100"></v-divider>
    </div>
</template>
<script>
export default {
    props: ["crumbs"],
};
</script>
