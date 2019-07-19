<?php

namespace App\Controller;

use App\Library\AbstractController;
use App\Library\Helper;
use App\Timers\MaxmindDbUpdate;
use MaxMind\Db\Reader;

class GeoController extends AbstractController
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * Countries
     *
     * @var array
     */
    private $countries = [];

    /**
     * IP Cache
     *
     * @var array
     */
    private $storage = [];

    /**
     * IP Cache Valid
     *
     * @var array
     */
    private $storageInvalid;

    /**
     * GeoAbstractController constructor.
     *
     * @throws Reader\InvalidDatabaseException
     */
    public function __construct()
    {
        // Open City DB
        $db = Helper::getRootDir(MaxmindDbUpdate::$files['city']['path']);
        if (file_exists($db)) {
            $this->reader = new Reader($db);
        }

        // Open JSON File
        $json = Helper::getRootDir('data/Country.json');
        if (file_exists($json)) {
            $this->countries = json_decode(file_get_contents($json), true);
        }
    }

    /**
     * Geo Location
     *
     * @param string|null $IP
     * @throws Reader\InvalidDatabaseException
     */
    public function location(string $IP = null): void
    {
        // Get IP
        $IP = $IP ?? $this->getIP();
        if (!$IP || !filter_var($IP, FILTER_VALIDATE_IP)) {
            $this->errorResponse();
            return;
        }

        // Cache Invalid
        if (!$this->storageInvalid || ($this->storageInvalid < time())) {
            $this->storageInvalid = time() + 3600;
            $this->storage = [];
        }

        // Find MM DB & Json
        if (!isset($this->storage[$IP])) {
            $mmdb = $this->reader->get($IP);
            if ($mmdb) {
                $this->storage[$IP] = $this->countries[$mmdb['country']['iso_code']];

                // Response
                $this->storage[$IP]['location'] = $mmdb['location'];
                $this->storage[$IP]['city'] = $mmdb['city'];
            } else {
                $this->errorResponse();
                return;
            }
        }

        $this->jsonResponse($this->storage[$IP]);
    }

    /**
     * Get Client IP Address
     *
     * @return string
     */
    private function getIP(): string
    {
        return $this->request->server['remote_addr'];
    }
}