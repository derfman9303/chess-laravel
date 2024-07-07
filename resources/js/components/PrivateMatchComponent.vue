<template>
    <div v-if="!gameStarted">
        <div v-if="!!userIsPlayerOne">
            <p>Give this link to whoever you want to join your game:</p>
            <p>{{ joinUrl }}</p>
            <v-btn @click="checkIfOccupied(true)">
                Start game
            </v-btn>
        </div>
        <div v-else>
            <p>Waiting for game to start...</p>
        </div>
    </div>
    <div v-else>
        <BoardComponent :multiplayer="true" :rotate-board="rotateBoard" :player-color="playerColor" :game-key="key" ref="boardComponent" color="black"></BoardComponent>
    </div>
</template>

<script>
    import axios from 'axios';
    import BoardComponent from './BoardComponent';
    import chessMixin from '../mixins/chessMixin';

    export default {
        name: 'PrivateMatchComponent',
        mixins: [chessMixin],
        data() {
            return {
                key: Date.now().toString(),
                joinUrl: null,
                userIsPlayerOne: true,
                playerOneIsWhite: true,
                playerColor: "white",
                rotateBoard: false,
                occupied: false,
                timeLimit: 30,
                gameStarted: false,
                color: null,
            }
        },

        components: {
            BoardComponent,
        },

        methods: {
            subscribeToChannel() {
                console.log("Subscribing...");
                Echo.channel('privatematch-' + this.key)
                .listen('.playerMoved', (e) => {

                    this.$refs.boardComponent.reloadBoardWithResponseData(e);
                })
                .listen('.startGame', (e) => {
                    this.playerOneIsWhite = e.playerOneIsWhite;
                    this.timeLimit = e.timeLimit;

                    this.startGame();
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

                        // If user is not player 1, and no other player has joined, then they should be allowed to join. Otherwise, the game is full
                        if (!this.userIsPlayerOne && !this.occupied) {
                            this.subscribeToChannel();
                        } else if (!!this.userIsPlayerOne && !this.occupied) {
                            if (!!buttonClick) {
                                alert("Player 2 has not joined the game yet :(");
                            }
                        } else if (!!this.userIsPlayerOne && !!this.occupied) {
                            this.startGameRequest();
                        } else {
                            // TODO: Better alert message
                            alert("Unable to join: Game is already full.");
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
            },

            startGameRequest() {
                axios.post('/start-game', {key: this.key})
                    .then(response => {

                    })
                    .catch(error => {
                        console.log(error);
                    });

                this.subscribeToChannel();
            },

            /**
             * Logic that should be ran once the start-game event has been received
             */
            startGame() {
                this.rotateBoard = this.checkIfRotateBoard();
                this.playerColor = this.rotateBoard ? "black" : "white";

                this.gameStarted = true;
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