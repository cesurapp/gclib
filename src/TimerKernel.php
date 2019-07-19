<?php

namespace App;

use App\Library\Helper;
use Swoole\Http\Server as HttpServer;

class TimerKernel
{
    /**
     * @var HttpServer
     */
    private $server;

    /**
     * @var array
     */
    private $container = [];

    /**
     * TimerKernel Constructor.
     *
     * @param HttpServer $server
     */
    public function __construct(HttpServer $server)
    {
        $this->server = $server;

        // Load Timers
        $timers = include Helper::getRootDir('config/timers.php');
        foreach ($timers as $timer) {
            switch ($timer[0]) {
                case 'timer':
                    $server->tick($timer[2] ? 1000 : $timer[1] * 1000, [$this, 'call'], [$timer[3], $timer[1] * 1000, $timer[2]]);
                    break;
                case 'timeout':
                    $server->after($timer[2] ? 1000 : $timer[1] * 1000, [$this, 'call'], [$timer[3], $timer[1] * 1000, $timer[2]]);
                    break;
            }
        }
    }

    /**
     * Call Timer Controller
     *
     * @param $id
     * @param array $params
     */
    public function call($id, array $params = []): void
    {
        if (is_array($id)) {
            $params = $id;
            $id = null;
        }

        [$class, $method] = explode('::', $params[0]);

        // Create AbstractController
        if (!isset($this->container[$class])) {
            $this->container[$class] = new $class($this->server, $method, $params[1], $params[2], $id);
        }

        // Call Method
        call_user_func([$this->container[$class], $method]);
    }
}