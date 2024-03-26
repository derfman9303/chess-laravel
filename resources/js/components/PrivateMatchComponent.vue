<template>
    <h1>Private match</h1>
    <v-btn @click="clickButton">

    </v-btn>
</template>

<script>
    import axios from 'axios';

    export default {
        name: 'PrivateMatchComponent',
        data() {
            return {
                board: null,
                key: Date.now(),
            }
        },

        methods: {
            clickButton() {
                let moveData = {
                    board: null,
                    key: this.key,
                };

                axios.post('/make-move', moveData)
                    .then(response => {
                        console.log(response);
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
        },

        created() {

        },

        mounted() {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);

            if (urlParams.has('key')) {
                this.key = urlParams.get('key');
            }

            const joinUrl = window.location.href + '?key=' + this.key;

            console.log(joinUrl);

            Echo.channel('privatematch-' + this.key)
            .listen('.opponentMoved', (e) => {
                console.log('listening event', e);
            });
        }
    }
</script>