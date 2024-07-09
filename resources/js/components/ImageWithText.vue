<script lang="ts" setup>
import type { TImageWithText } from "@/types";

defineProps<TImageWithText>();
</script>

<template>
    <div>
        <v-row
            class="!tw-items-center"
            :class="reverse ? '!tw-flex-row-reverse' : ''"
        >
            <v-col>
                <v-img
                    max-height="400"
                    max-width="708"
                    :lazy-src="image"
                    alt="Dog"
                    :src="image"
                />
            </v-col>
            <v-col>
                <div class="!tw-text-[#4EC690] !tw-text-[60px] !tw-font-[300]">
                    {{ title }}
                </div>
                <v-btn
                    flat
                    color="#4EC690"
                    class="!tw-text-white !tw-normal-case !tw-underline"
                >
                    {{ subtitle }}
                </v-btn>
            </v-col>
        </v-row>
    </div>
</template>
