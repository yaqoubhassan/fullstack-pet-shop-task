<template>
    <div id="banner">
        <p>Up to 30% sale</p>
        <p>On select dog food and toys</p>
    </div>
</template>

<script setup>
import banner from "/public/images/banner.jpeg";
</script>
<style scoped lang="scss">
#banner {
    background-image: url("/public/images/banner.jpeg");
    background-size: cover;
    background-position: center;
    aspect-ratio: 2.5;
    padding: 32px 41px;

    p {
        background-color: #00000042;
        color: white;
        width: max-content;

        &:first-child {
            font-size: 3.75rem;
            font-weight: 300;
            margin-bottom: 7px;
            padding: 8px 10px;
        }

        &:last-child {
            font-size: 1.25rem;
            font-weight: 500;
            padding: 4px 10px;
        }
    }
}
</style>
