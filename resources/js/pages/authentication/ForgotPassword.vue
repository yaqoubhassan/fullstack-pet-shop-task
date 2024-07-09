<template>
    <v-container class="!tw-flex !tw-justify-center">
        <div
            class="!tw-mt-24 !tw-flex !tw-flex-col !tw-items-center !tw-max-w-[50%]"
        >
            <v-row>
                <div class="!tw-text-[24px]">Recover password</div>
            </v-row>
            <v-row class="!tw-mt-10">
                <div class="!tw-font-light">
                    Enter the email associated with your account and we’ll send
                    an email with instructions to reset your password.
                </div>
            </v-row>
            <v-row class="!tw-mt-10 !tw-w-full">
                <v-text-field
                    label="Email Address*"
                    placeholder=""
                    variant="outlined"
                    :required="true"
                    :type="'email'"
                ></v-text-field>
            </v-row>
            <v-row class="!tw-mt-5 !tw-w-full">
                <v-btn color="#4EC690" block class="text-white"
                    >GET RECOVERY LINK
                </v-btn>
            </v-row>
        </div>
    </v-container>
</template>
