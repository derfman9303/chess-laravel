<template>
        <div class="position-absolute top-50 start-50 translate-middle">
            <div class="row d-flex flex-nowrap">
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
                <div class="square dark"></div>
                <div class="square light"></div>
            </div>
        </div>
</template>

<script>
    export default {
        name: 'BoardComponent',
    }
</script>

<style scoped>
    .square {
        height: 100px;
        width: 100px;
        padding: 0;
    }

    .square.light {
        background-color: #b5b4b3;
    }

    .square.dark {
        background-color: #70706f;
    }
</style>