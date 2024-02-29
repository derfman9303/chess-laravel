<template>
    <div id="homepage-container">
        <div class="row pt-5 justify-content-center">
            <div class="col">
                <h1 class="text-center">
                    Frederic Hodges Chess
                </h1>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-auto">
                <v-btn color="primary m-2">
                    Single Player
                </v-btn>
                <v-btn color="primary m-2">
                    Multiplayer
                </v-btn>
            </div>
        </div>
    </div>
</template>

<style>
    #homepage-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
    }
</style>

<script>

    export default {
        name: 'HomepageComponent',

        created() {

        },

        mounted() {

        }
    }
</script>