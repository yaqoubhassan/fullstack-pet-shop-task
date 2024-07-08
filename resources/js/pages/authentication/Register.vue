<template>
    <div>
        <v-row class="tw-space-x-5"
            ><v-text-field
                label="First Name*"
                placeholder=""
                variant="outlined"
            ></v-text-field
            ><v-text-field
                label="Last Name*"
                placeholder=""
                variant="outlined"
            ></v-text-field
        ></v-row>
        <v-row>
            <v-text-field
                label="Email Address*"
                placeholder=""
                variant="outlined"
                class="mt-3"
            ></v-text-field
        ></v-row>
        <v-row>
            <v-text-field
                label="Phone Number*"
                placeholder=""
                variant="outlined"
                class="mt-3"
            ></v-text-field
        ></v-row>
        <v-row>
            <v-text-field
                label="Address*"
                placeholder=""
                variant="outlined"
                class="mt-3"
            ></v-text-field
        ></v-row>
        <v-row>
            <v-text-field
                label="Password*"
                placeholder=""
                variant="outlined"
                class="mt-3"
            ></v-text-field
        ></v-row>
        <v-row>
            <v-text-field
                label="Confirm Password*"
                placeholder=""
                variant="outlined"
                class="mt-3"
            ></v-text-field
        ></v-row>
        <v-row
            ><v-checkbox
                color="#4EC690"
                label="I want to receive inspiration, marketing promotions and updates
                via email."
            ></v-checkbox
        ></v-row>
        <v-row>
            <v-btn color="#4EC690" block class="text-white"
                >SIGN UP</v-btn
            ></v-row
        >
        <v-row class="mt-10" :justify="'center'">
            <div
                class="tw-text-[#1976d2] tw-cursor-pointer tw-text-right"
                @click.stop="$emit('change-auth-type', 'login')"
            >
                Already have an account? Login
            </div>
        </v-row>
    </div>
</template>
