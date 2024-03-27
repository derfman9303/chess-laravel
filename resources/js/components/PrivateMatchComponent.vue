<template>
    <h1>Private match</h1>
    <p>Give this link to whoever you want to join your game:</p>
    <p>{{ joinUrl }}</p>
    <v-btn @click="checkIfOccupied(true)">
        Start game
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
                joinUrl: null,
                userIsPlayerOne: true,
                occupied: false,
            }
        },

        methods: {
            makeMove() {
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

            subscribeToChannel() {
                console.log("Subscribing...");
                Echo.channel('privatematch-' + this.key)
                .listen('.opponentMoved', (e) => {
                    console.log('listening event', e);
                });
            },

            buildJoinUrl() {
                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);

                if (urlParams.has('key')) {
                    this.key = urlParams.get('key');
                    this.userIsPlayerOne = false;
                }

                this.joinUrl = window.location.href + '?key=' + this.key;
            },

            checkIfOccupied(buttonClick = false) {
                axios.post('/is-channel-occupied', {channel: 'privatematch-' + this.key})
                    .then(response => {
                        this.occupied = response.data.occupied;
                        console.log(this.occupied);

                        // If user is not player 1, and no other player has joined, then they should be allowed to join. Otherwise, the game is full
                        if (!this.userIsPlayerOne && !this.occupied) {
                            this.subscribeToChannel();
                        } else if (!!this.userIsPlayerOne && !this.occupied) {
                            if (!!buttonClick) {
                                alert("Player 2 has not joined the game yet :(");
                            }
                        } else if (!!this.userIsPlayerOne && !!this.occupied) {
                            // TODO: Start game
                            alert("Game starting!");
                        } else {
                            // TODO: Better alert message
                            alert("Unable to join: Game is already full.");
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },
        },

        created() {

        },

        mounted() {
            this.buildJoinUrl();
            this.checkIfOccupied();
        }
    }
</script>