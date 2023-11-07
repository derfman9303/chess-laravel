<template>
        <div class="position-absolute top-50 start-50 translate-middle">
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
    export default {
        name: 'BoardComponent',
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
            }
        },

        methods: {
            newPiece(type, color, row, square) {
                return {
                    type: type,
                    color: color,
                    row: row,
                    square: square,
                    moved: false,
                    captured: false,
                    index: null,
                };
            },

        /**
         * Colors the board squares light/dark alternating. Called once at beginning of game
         */
            paintBoard() {
                for (let r = 0; r < this.grid.length; r++) {
                    for (let s = 0; s < this.grid[r].length; s++) {
                        if ((r % 2 === 0 && s % 2 === 1) || (r % 2 === 1 && s % 2 === 0)) {
                            this.grid[r][s].classList.add("dark");
                        } else {
                            this.grid[r][s].classList.add("light");  
                        }
                    }
                }
            },
        },

        created() {
            this.pieces = [
                this.newPiece('king', 'white', 7, 4),
                this.newPiece('king', 'black', 0, 4),
                this.newPiece('queen', 'white', 7, 3),
                this.newPiece('queen', 'black', 0, 3),
                this.newPiece('rook', 'white', 7, 0),
                this.newPiece('rook', 'white', 7, 7),
                this.newPiece('rook', 'black', 0, 0),
                this.newPiece('rook', 'black', 0, 7),
                this.newPiece('knight', 'white', 7, 1),
                this.newPiece('knight', 'white', 7, 6),
                this.newPiece('knight', 'black', 0, 1),
                this.newPiece('knight', 'black', 0, 6),
                this.newPiece('bishop', 'white', 7, 2),
                this.newPiece('bishop', 'white', 7, 5),
                this.newPiece('bishop', 'black', 0, 2),
                this.newPiece('bishop', 'black', 0, 5),
                this.newPiece('pawn', 'white', 6, 0),
                this.newPiece('pawn', 'white', 6, 1),
                this.newPiece('pawn', 'white', 6, 2),
                this.newPiece('pawn', 'white', 6, 3),
                this.newPiece('pawn', 'white', 6, 4),
                this.newPiece('pawn', 'white', 6, 5),
                this.newPiece('pawn', 'white', 6, 6),
                this.newPiece('pawn', 'white', 6, 7),
                this.newPiece('pawn', 'black', 1, 0),
                this.newPiece('pawn', 'black', 1, 1),
                this.newPiece('pawn', 'black', 1, 2),
                this.newPiece('pawn', 'black', 1, 3),
                this.newPiece('pawn', 'black', 1, 4),
                this.newPiece('pawn', 'black', 1, 5),
                this.newPiece('pawn', 'black', 1, 6),
                this.newPiece('pawn', 'black', 1, 7),
            ];
        },

        mounted() {
            this.squares = document.getElementsByClassName('square');
            this.grid = [
                [
                    this.squares[0],
                    this.squares[1],
                    this.squares[2],
                    this.squares[3],
                    this.squares[4],
                    this.squares[5],
                    this.squares[6],
                    this.squares[7],
                ],
                [
                    this.squares[8],
                    this.squares[9],
                    this.squares[10],
                    this.squares[11],
                    this.squares[12],
                    this.squares[13],
                    this.squares[14],
                    this.squares[15],
                ],
                [
                    this.squares[16],
                    this.squares[17],
                    this.squares[18],
                    this.squares[19],
                    this.squares[20],
                    this.squares[21],
                    this.squares[22],
                    this.squares[23],
                ],
                [
                    this.squares[24],
                    this.squares[25],
                    this.squares[26],
                    this.squares[27],
                    this.squares[28],
                    this.squares[29],
                    this.squares[30],
                    this.squares[31],
                ],
                [
                    this.squares[32],
                    this.squares[33],
                    this.squares[34],
                    this.squares[35],
                    this.squares[36],
                    this.squares[37],
                    this.squares[38],
                    this.squares[39],
                ],
                [
                    this.squares[40],
                    this.squares[41],
                    this.squares[42],
                    this.squares[43],
                    this.squares[44],
                    this.squares[45],
                    this.squares[46],
                    this.squares[47],
                ],
                [
                    this.squares[48],
                    this.squares[49],
                    this.squares[50],
                    this.squares[51],
                    this.squares[52],
                    this.squares[53],
                    this.squares[54],
                    this.squares[55],
                ],
                [
                    this.squares[56],
                    this.squares[57],
                    this.squares[58],
                    this.squares[59],
                    this.squares[60],
                    this.squares[61],
                    this.squares[62],
                    this.squares[63],
                ],
            ];

            this.paintBoard();
        }
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