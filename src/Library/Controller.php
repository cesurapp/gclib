<?php

namespace App\Library;

use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Base Controller
 */
class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Kernel Set Request|Response
     *
     * @param Request $request
     * @param Response $response
     */
    final function set(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * JSON Response
     *
     * @param array $data
     */
    public function jsonResponse($data = []): void
    {
        $this->response->header('Content-Type', 'application/json');
        $this->response->end(json_encode($data));
    }

    /**
     * HTTP Error Response
     *
     * @param string $message
     * @param int $code
     */
    public function errorResponse(string $message = '404 not found!', int $code = 404): void
    {
        $this->response->header('Content-Type', 'application/json');
        $this->response->status($code);
        $this->response->end(json_encode(['code' => $code, 'message' => $message]));
    }
}