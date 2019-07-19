<?php

return [
    ['GET', '/exrate/{bank:ecb|tcmb}/latest', 'App\Controller\ExRateController::latestRate'],
    ['GET', '/exrate/{bank:ecb}/{date}', 'App\Controller\ExRateController::customRate'],
    ['GET', '/exrate/{bank:ecb}/{dateStart}/{dateEnd}', 'App\Controller\ExRateController::customRateRange']
];