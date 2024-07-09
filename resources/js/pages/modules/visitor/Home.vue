<script lang="ts" setup>
import ImageWithText from "@/components/ImageWithText.vue";
import Search from "@/components/Search.vue";
import { ref } from "vue";
import Banner from "./Banner.vue";
import DryDogFood from "./DryDogFood.vue";
import dogImage from "/public/images/dry_food_dog.png";
import tipsImage from "/public/images/tips.png";

const dog = ref(dogImage);
const tips = ref(tipsImage);
</script>

<template>
    <v-container style="max-width: 1200px">
        <v-container class="!tw-max-w-[50%]">
            <Search />
        </v-container>
        <Banner class="mx-auto" style="margin-bottom: 103px" />
        <v-row class="!tw-my-20">
            <DryDogFood title="Dry dog food" />
        </v-row>
        <ImageWithText
            :image="dog"
            :title="'Treat your pup'"
            :subtitle="'Discover our dog treat selection'"
        />
        <v-row class="!tw-my-20">
            <DryDogFood title="Pet treats and chews" />
        </v-row>
        <ImageWithText
            :reverse="true"
            :image="tips"
            :title="'Get the best tips'"
            :subtitle="'Read our blog'"
        />
    </v-container>
</template>
