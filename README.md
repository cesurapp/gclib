# IP Geolocation & Exchange Rate API

The service is free. Maxmind-DB is used for geographic location determination. The European Central Bank and the Central Bank of the Republic of Turkey used to exchange rates.

## Development
Written using Swoole Http Server. It achieves an average "35k/sec" request on a 4-core machine. 

#### PHP Library
* [Swoole](https://github.com/swoole/swoole-src)
* [FastRoute](https://github.com/nikic/FastRoute)
* [Maxmind Reader](https://github.com/maxmind/MaxMind-DB-Reader-php)

#### Automatic Update
Swool Scheduler is made with continuous update. The update is checked every 24 hours. The database is downloaded 2 times in one month. 

## Installation
You can run the api with Docker.
```yaml
version: '3.4'

services:
    gclib:
        image: appaydin/gclib
        ports:
            - 90:9500
        environment:
            - SWOOLE_PORT=9500
            - SWOOLE_WORKER=2
            - DBCITY=https://download.maxmind.com/...
```

Generate DBCITY URL: https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=LICENSE_KEY&suffix=tar.gz

LICENSE_KEY: You can obtain the license key from your Maxmind account.

#### Geo Location API
Geo locate client: 
```http request
GET http://127.0.0.1:90/geolocate
```
Custom IP adress:
```http request
GET http://127.0.0.1:90/geolocate/IP
```

#### Exhange Rate API
Latest exchange rates:
```http request
GET http://127.0.0.1:90/exrate/ecb/latest #European Central Bank
GET http://127.0.0.1:90/exrate/tcmb/latest #The Central Bank of the Republic of Turkey
```
Custom exchange rate (only ecb):
```http request
GET http://127.0.0.1:90/exrate/ecb/2019-07-18 #European Central Bank
```
Custom exchange rate range (only ecb):
```http request
GET http://127.0.0.1:90/exrate/ecb/2019-07-02/2019-07-05 #European Central Bank
```
Custom Parameters:
```
Change Base Currency

GET ?base=USD
GET ?base=EUR

List Specific Currencies

GET ?symbol=USD,TRY,EUR
GET ?base=EUR&symbol=USD,TRY
```