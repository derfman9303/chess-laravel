# Chess Bot in Laravel/Vue

This is my Laravel chess bot, which is an updated version of my Javascript one. At its core, it still functions the same, using the mini-max algorithm to simulate three moves into the future and select the move which leads to the best outcome for itself. However, there are a few key differences:

1. Obviously the tech stack is updated. Now, instead of the bot doing all the calculations in Javascript which is executed in the browser, it's all done in PHP which is server-side. A couple reasons for this is that computation load seemed to be getting pretty heavy for the browser, and sometimes the page would freeze up if the Javascript took too long to execute. Another reason is that it would be much simpler to create a public API that any front-end can make requests to, if I ever want to do something like that.

2. I've made a big change to how the bot calculates whether a move would put its own king into check. Previously it would make the move, recalculate the board state to determine if the king is in check, and then move the piece back. This is simple enough, but when you're doing this iteration three or more moves into the future, the computation load gets pretty heavy. So the new way it works is it calculates four things before simulating any moves:
    a. The pieces that can only make a single valid move, which would be to capture the piece they're currently protecting the king from;
    b. (if king is currently in check) A list of squares that you can move a piece to in order to cancel the check;
    c. The squares you can move your king to in order to avoid check;
    d. Whether the king is currently in check (true/false).

   If you know these things, you can determine if a move will put your king into check without running through all the iterations for every potential move, thus saving a ton of execution time.
   
