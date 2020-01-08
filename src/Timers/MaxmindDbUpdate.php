<?php

namespace App\Timers;

use App\Library\AbstractTimer;
use App\Library\Helper;

class MaxmindDbUpdate extends AbstractTimer
{
    /**
     * MaxMind database is updated one time per month.
     * The database is downloaded once in 15 days.
     *
     * @var int
     */
    private $updateInterval = 15;

    /**
     * DB File Path and Download URL
     *
     * @var array
     */
    public static $files = [
        /*'country' => [
            'path' => 'data/GeoLite2-Country.mmdb',
            'version' => 'data/GeoLite2-Country.version',
            'const' => 'DBCOUNTRY'
        ],*/
        'city' => [
            'path' => 'data/GeoLite2-City.mmdb',
            'version' => 'data/GeoLite2-City.version',
            'const' => 'DBCITY'
        ]
    ];

    /**
     * Initialize Update
     */
    public function init(): void
    {
        echo "Started Maxmind-DB update timer...\n";

        foreach (self::$files as $file) {
            $verPath = Helper::getRootDir($file['version']);
            $verNumber = file_exists($verPath) ? (int)file_get_contents($verPath) : false;

            // Check Version
            if (($verNumber === false) || $verNumber < time()) {
                echo "Maxmind-DB downloading new version...\n";
                
                // Download Tar File
                $tar = Helper::getRootDir(str_replace('.mmdb', '.tar.gz', $file['path']));
                
                if ($this->downFile($tar, $_ENV[$file['const']])) {
                    // Extract Archive
                    $mmdb = $this->extractTarFile($tar);

                    if ($mmdb) {
                        // Move
                        rename($mmdb, Helper::getRootDir($file['path']));

                        // Remove Tar|Dir
                        @unlink($tar);
                        @rmdir(dirname($mmdb));

                        // Save Version
                        @file_put_contents($verPath, time() + ($this->updateInterval * 86400));

                        echo sprintf("Maxmind-DB download completed! => %s \n", date('d-m-Y H:i'));
                    }

                }
            }
        }
    }

    /**
     * Download File with CURL
     *
     * @param string $path
     * @param string $url
     *
     * @return bool
     */
    private function downFile(string $path, string $url): bool
    {
        try {
            // Download File
            set_time_limit(0);
            $file = fopen($path, 'w+');
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_TIMEOUT, 300);
            curl_setopt($curl, CURLOPT_FILE, $file);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($curl);
            curl_close($curl);
            fclose($file);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Extract .mmdb file
     *
     * @param string $path
     *
     * @return string
     */
    public function extractTarFile(string $path): string
    {
        // Open Archive
        $phar = new \PharData($path);

        // Find mmdb file
        $mmdb = '';
        foreach ($phar->getChildren() as $child) {
            $mmdb = (string)$child;
            if (pathinfo($mmdb, PATHINFO_EXTENSION) === 'mmdb') {
                $mmdb = explode('.tar.gz/', $child)[1];
                break;
            }
        }

        // Extract
        if ($mmdb) {
            $phar->extractTo(dirname($path), $mmdb, true);
            return dirname($path) . '/' . $mmdb;
        }

        echo '.mmdb file not found!';
        return '';
    }
}