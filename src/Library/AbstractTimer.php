<?php

namespace App\Library;

use Swoole\Http\Server as HttpServer;

class AbstractTimer
{
    /**
     * @var HttpServer
     */
    private $server;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var int
     */
    private $timerId;

    public function __construct(HttpServer $server, string $method, int $interval, bool $firstStart = false, int $timerId = null)
    {
        $this->server = $server;
        $this->method = $method;
        $this->interval = $interval;
        $this->timerId = $timerId;

        if ($firstStart) {
            $this->reload();
        }
    }

    /**
     * Restart Timer.
     *
     * @param int|null $interval
     * @param string|null $method
     */
    final protected function reload(int $interval = null, string $method = null): void
    {
        // Stop
        $this->stop();

        if ($this->timerId) {
            $this->server->tick($interval ?? $this->interval, [$this, $method ?? $this->method]);
        } else {
            $this->server->after($interval ?? $this->interval, [$this, $method ?? $this->method]);
        }
    }

    /**
     * Clear Timer works only Timer.
     */
    final protected function stop(): void
    {
        if ($this->timerId) {
            $this->server->clearTimer($this->timerId);
        }
    }
}