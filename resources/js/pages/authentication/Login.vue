<template>
    <div>
        <v-row
            ><v-text-field
                label="Email"
                placeholder=""
                variant="outlined"
            ></v-text-field
        ></v-row>
        <v-row>
            <v-text-field
                label="Password"
                placeholder=""
                variant="outlined"
                class="mt-5"
            ></v-text-field
        ></v-row>
        <v-row
            ><v-checkbox color="#4EC690" label="Remember me"></v-checkbox
        ></v-row>
        <v-row>
            <v-btn color="#4EC690" block class="primary--text"
                >LOG IN</v-btn
            ></v-row
        >
        <v-row :justify="'space-between'" class="mt-10">
            <router-link class="tw-text-[#1976d2]" to=""
                >Forgot password?</router-link
            >
            <div
                class="tw-text-[#1976d2] tw-cursor-pointer tw-text-right"
                @click.stop="$emit('change-auth-type', 'signup')"
            >
                Don't have an account? Sign up
            </div>
        </v-row>
    </div>
</template>
