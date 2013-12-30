iron-tictactoe
==============

simple hackathon project to implement "parallel game tree search" for TicTacToe using IronWorker, IronCache, IronMQ, and SendGrid

Overview:
---

This project implements "parallel game tree search" (for a simple game of TicTacToe) using all 3 APIs provided by Iron.io (**IronWorker**, **IronCache**, **IronMQ**) as well as **SendGrid** to deliver the final results by email. It is implemented using **PHP**.

This project was done for mostly academic interest, rather than being a practical application, but I think it is a good demonstration of the capabilities of Iron.io, and how the 3 APIs can be used together.

My goal was to solve an interesting problem that takes advantage of parallel execution, with a large number of **IronWorkers** performing tasks that are small pieces of a larger puzzle. The **IronCache** is used to keep track of intermediate results and **IronQueues** are used to keep track of tasks that will be executed later.

Tic-tac-toe is a common children's game, with players taking turns placing "X" and "O" in a 3x3 grid, each trying to form a horizontal, vertical, or diagonal line. It is often used in undergraduate computer science courses to illustrate game tree search algorithms, so it should be understandable to a wide audience. A good description of tic-tac-toe is available on [wikipedia](http://en.wikipedia.org/wiki/Tic-tac-toe).

The interesting twist in this project is the "parallel" algorithm and how it is implemented using Iron.io **Workers**, **Message Queues**, and **Caches**.

What Iron TicTacToe does:
---

*Iron TicTacToe* tells you whether a given tic-tac-toe position is *win*, *lose*, or *draw*.

The input is any valid tic-tac-toe game board.  The 3x3 grid is represented as a 9 character string, such as "XOX......".  Player "X" always moves first, so in this example, player "O" will move next.

The output is either "X", "O", or ".", ... indicating a guaranteed win for player "X", a guaranteed win for player "O", or a draw ".", respectively. Both players are assumed to use optimal strategies.

A regular game of tic-tac-toe always ends in a draw if players always make the best move possible, but if one player makes a mistake, it is sometimes possible for the other player to guarantee a win.

How Iron TicTacToe works:
---

The parallel algorithm has 2 main parts. The first part expands the game tree. The second part passes the results from finished games (at the leaves) back up the tree towards the root. In the middle of the game tree search, some IronWorkers are busy expanding one part of the game tree while other IronWorkers are busy passing results up another part of the game tree. The algorithm finishes when all the results are reported back to the root, and an email is sent with the final solution.

"iron_tictactoe_worker"
---

The input payload is a tic-tac-toe board represented as a 9 character string. By counting the number of "X", "O", and "." in the string, we know which player goes next, and the number of child nodes.

An **IronCache** is used to store current information about each tic-tac-toe board. This includes the best possible outcome for the current player (so far), and the number of updates from child nodes that are still pending. It is important to recognize that different parent nodes can have the same child node in common. Using the cache ensures that work is not duplicated.

An **IronQueue** is used to store "callbacks".  These are essentially pointers back up the game tree. These queues are created on-the-fly. The name of the queue is the child board, and messages on the queue are parent boards.

If a child board represents an unfinished game, a new **IronWorker** task is created to expand it. If a child board is a finished game, the cache is updated (*win*, *lose*, or *draw*), and new task is created for "iron_callback_worker" to propagate the results up the tree.

"iron_callback_worker"
---

The input payload is a tic-tac-toe board, which we can think of as a recently finished child node. Messages are pulled off the corresponding *IronQueue* to learn which parent nodes need to be updated. This is done by submitting new tasks to "iron_update_worker".

"iron_update_worker"
---

This updates information about a parent tic-tac-toe board based on results from a child tic-tac-toe board that has recently completed. If this is the last child to report back, we create a new task for an "iron_callback_worker" to continue propagating results back up the tree. If this is the root node, we send an email message to report the final solution.

How you can try using Iron TicTacToe:
---

For my own testing, I submitted tasks using the command line:
	% iron_worker queue iron_tictactoe \
		--payload '{"board":"XOOXOX..X", "email":"dxjones@gmail.com"}'


To let other people submit tictactoe boards, I created a very simple web form at <a href="http://voxica.net/iron-tictactoe/" target="_new"><b>http://voxica.net/iron-tictactoe/</b></a>

You can get a sense of the parallel execution by looking at this screenshot (<a href="http://voxica.net/iron-tictactoe/iron-tictactoe-tasks.png" target="_new">http://voxica.net/iron-tictactoe/iron-tictactoe-tasks.png</a>) showing IronWorker tasks queued and running.

Limitations:
---

This project was implemented as a proof-of-concept demo. Consequently, it assumes there is only one tic-tac-toe puzzle being solved at a time. If you tried the online demo, please wait until one task completes before submitting another one.


Observations:
---

I found all of the Iron.io capabilities remarkably easy to work with.
The command line tool (**iron_worker**) for uploading code, running tasks locally, and submitting payloads to workers, very easy to use (even though it is clearly doing a lot of work behind the scenes).

The HUD (**hud.iron.io**) is a great tool for monitoring the current status of workers, queues, and caches. It would be nice to be able to search IronWorker logs for specific key words or error messages when debugging.

The one challenge I encountered was trying to use IronMQ "Push Queues" in combination with IronWorkers.

Along the way, I found 2 inconsistencies (not quite "bugs") in the PHP libraries.

1. "IronWorker.class.php" ... Payloads are submitted to IronWorkers using postTask() as an associative array, but inside an IronWorker, getPayload() returns an object. For consistency, it would be better to allow payloads to be submitted and retrieved in the same format. This can be implemented, without breaking any existing code, by adding an optional flag to getPayload(), which is passed to json_decode() to return an associative array.

2. "IronMQ.class.php ... deleteMessage() returns a result that is still json_encoded. For consistency, it should follow the same convention as postMessage(), which is json_decoded.

Future work:
---

In my original design, I intended to use IronMQ "Push Queues" to handle the updates for parent nodes. The idea was to have one queue for each parent node. To avoid race conditions, there can only be 1 IronWorker at a time updating each parent node, but independent parent nodes can be updated in parallel.

Since I was running out of time before the deadline, I skipped the Push Queues and just submit tasks directly to an "iron_update_worker", but the limited concurrency creates a bottleneck.

Exercises left to the reader:
---

There are 3 main directions someone might explore based on this project:

1. Taking into account the symmetries of tic-tac-toe boards, a large number of tic-tac-toe boards are actually "equivalent". By being a little smarter about how tic-tac-toe boards are represented in the IronCache, the size of the game tree (and the amount of work) could be significantly reduced.

2. In serial game tree search, a popular optimization is Alpha-Beta pruning. It would be interesting to implement parallel Alpha-Beta pruning, to further reduce the amount of work.

3. Tic-tac-toe is a simple game, and useful when the goal is understanding the game tree search algorithm. Once the algorithm is implemented, it would be interesting to generalize it to more complex games, such as checkers, chess, or go.

Please contact me if you have any comments.

**-- David Jones, dxjones@gmail.com**
