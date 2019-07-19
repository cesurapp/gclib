<?php

/**
 * [
 *  timer|timeout,              =>  timer, repeats continuously. timeout, only works one time
 *  3600                        =>  interval, repeat or standby time (second) => for 1 hours 3600
 *  true|false                  =>  firstStart, run at server startup
 *  App\Timers\Class::method    =>  class and method to be executed
 * ]
 */

return [
    ['timer', 86400, true, 'App\Timers\MaxmindDbUpdate::init'],
];