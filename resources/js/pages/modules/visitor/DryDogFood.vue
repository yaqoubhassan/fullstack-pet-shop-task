<script lang="ts" setup>
import type { TImageWithText } from "@/types";
import { ref } from "vue";
import enduranceImage from "/public/images/endurance.png";

const endurance = ref(enduranceImage);

defineProps<Pick<TImageWithText, "title">>();
</script>

<template>
    <div>
        <p class="mb-5 !tw-text-[60px] !tw-text-[#4EC690] !tw-font-[300]">
            {{ title }}
        </p>
        <v-carousel hide-delimiter-background hide-delimiters height="auto">
            <template v-slot:prev="{ props }">
                <v-btn
                    icon="mdi mdi-chevron-left"
                    variant="plain"
                    @click="props.onClick"
                ></v-btn>
            </template>
            <template v-slot:next="{ props }">
                <v-btn
                    icon="mdi mdi-chevron-right"
                    variant="plain"
                    @click="props.onClick"
                ></v-btn>
            </template>
            <v-carousel-item>
                <v-row class="!tw-mx-20 !tw-items-center">
                    <v-col v-for="i in 4" :key="i">
                        <v-hover>
                            <template v-slot:default="{ isHovering, props }">
                                <v-card
                                    v-bind="props"
                                    :elevation="isHovering ? '2' : 0"
                                >
                                    <v-card-item>
                                        <v-img
                                            max-height="220"
                                            max-width="220"
                                            :lazy-src="endurance"
                                            alt="Tips"
                                            :src="endurance"
                                        />
                                        <div class="!tw-flex !tw-flex-col">
                                            <p class="!tw-text-[20px]">
                                                Brit care endurance
                                            </p>
                                            <p
                                                class="!tw-text-[16px] !tw-font-light"
                                            >
                                                Animonda
                                            </p>
                                            <p class="!tw-text-[20px]">200kn</p>
                                        </div>
                                    </v-card-item>
                                </v-card>
                            </template>
                        </v-hover>
                    </v-col>
                </v-row>
            </v-carousel-item>
        </v-carousel>
    </div>
</template>
