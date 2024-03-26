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
                <v-btn
                    color="primary m-2"
                    @click="routePage('/singleplayer')"
                >
                    Single Player
                </v-btn>
                <v-menu>
                    <template v-slot:activator="{ props }">
                        <v-btn
                            color="primary"
                            v-bind="props"
                        >
                            Multiplayer
                        </v-btn>
                    </template>
                    <v-list>
                        <v-list-item
                            v-for="(item, index) in items"
                            :key="index"
                            :value="index"
                            @click="routePage(item.route)"
                        >
                            <v-list-item-title>{{ item.title }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
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

        data() {
            return {
                items: [
                    {
                        title: 'Private game',
                        route: '/private-match',
                    },
                    {
                        title: 'Random match',
                        route: '/random-match',
                    }
                ],   
            };
        },

        methods: {
            routePage(route) {
                window.location.href = route;
            },
        },

        created() {

        },

        mounted() {
            Echo.channel('notification')
            .listen('.broadcastEvent', (e) => {
                console.log('listening event', e);
            });
        }
    }
</script>