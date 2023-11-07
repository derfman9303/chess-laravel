<template>
        <div
            id="board"
            class="position-absolute top-50 start-50 translate-middle"
            @click="handleClick"
        >
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
            <div class="row d-flex flex-nowrap">
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
                <div class="square"></div>
            </div>
        </div>
</template>

<script>
    import chessMixin from '../mixins/chessMixin';

    export default {
        name: 'BoardComponent',
        mixins: [chessMixin],
        data() {
            return {
               grid: [],
               squares: [],
               board: [
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                    ['empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty', 'empty'],
                ],
                pieces: [],
                selectedPiece: null,

                // blackCaptured: null,
                // whiteCaptured: null,
            }
        },

        methods: {
            handleClick(event) {
                // Starting from the clicked element
                let currentElement = event.target;

                // Continue traversing up the DOM tree until a parent with the desired class is found
                while (currentElement && !currentElement.classList.contains('square')) {
                    currentElement = currentElement.parentElement;
                }

                if (currentElement && currentElement.classList.contains('square')) {
                    console.log(currentElement);
                }
            },
        },

        created() {
            this.definePiecesArray();
        },

        mounted() {
            this.squares = document.getElementsByClassName('square');

            this.defineGridArray();
            this.paintBoard();
            this.loadBoard();
            this.reloadGrid();
            this.setCoordinatesOfSquares();
        }
    }
</script>

<style>
    .square {
        height: 50px;
        width: 50px;
        padding: 0;
    }

    .square.light {
        background-color: #b5b4b3;
        border: 1px solid #b5b4b3;
    }

    .square.dark {
        background-color: #70706f;
        border: 1px solid #70706f;
    }

    .square svg {
        height: 100%;
        width: 100%;
        padding: 7.5px;
    }

    @media screen and (max-width: 769px) {
        #board {
            width: 90vw;
        }

        .square {
            height: calc(90vw / 8);
            width: calc(90vw / 8);
        }

        .square svg {
            padding: 5px;
        }

        .captured-tray {
            width: 90vw;
        }
    }
</style>