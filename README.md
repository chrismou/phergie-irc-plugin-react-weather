# Weather plugin for [Phergie](http://github.com/phergie/phergie-irc-bot-react/)

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for returning weather information for a given location.

[![Build Status](https://travis-ci.org/chrismou/phergie-irc-plugin-react-weather.svg)](https://travis-ci.org/chrismou/phergie-irc-plugin-react-weather)

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "chrismou/phergie-irc-plugin-react-weather": "dev-master"
    }
}
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

The default provider is OpenWeatherSearch, which requires a free api key to use (which you can get from 
[here](http://openweathermap.org/appid).
 
Once you've got your appid, you can set up the plugin as follows:

```php
new \Chrismou\Phergie\Plugin\Weather\Plugin(array(

    "config" => array("appid" => "YOUR_APP_ID")

))
```

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
