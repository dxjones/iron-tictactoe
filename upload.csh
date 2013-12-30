#!/bin/csh -f

set echo
iron_worker upload iron_tictactoe.worker
iron_worker upload iron_callback.worker
iron_worker upload iron_update.worker -c 1

