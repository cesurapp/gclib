<?php

return [
    // Exchange Rates
    ['GET', '/exrate/{bank:ecb|tcmb}/latest', 'App\Controller\ExRateController::latestRate'],
    ['GET', '/exrate/{bank:ecb}/{date}', 'App\Controller\ExRateController::customRate'],
    ['GET', '/exrate/{bank:ecb}/{dateStart}/{dateEnd}', 'App\Controller\ExRateController::customRateRange'],

    // Geo Location
    ['GET', '/geolocate[/{ip}]', 'App\Controller\GeoController::location'],
];