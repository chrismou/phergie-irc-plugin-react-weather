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


```php
new \Chrismou\Phergie\Plugin\Weather\Plugin(array(

    "config" => array("appid" => "YOUR_APP_ID")

))
```

There's also a Weather Underground provider included. It's a bit of a work in progress - it currently requires a city and country and tends to not find 
the location as often - but it does work.  If you think you can improve it, feel free to fork/fix/pull request.  Or send me the info. EIther way. :-)

Weather underground also requires an API key, which can you get for free from [here](http://www.wunderground.com/weather/api/)

```php
new \Chrismou\Phergie\Plugin\Weather\Plugin(array(
	
	'provider' => 'Chrismou\\Phergie\\Plugin\\Weather\\Provider\\Wunderground',
    "config" => array("appid" => "YOUR_APP_ID")

))
```
Or you can alway write your own! Feel free to fork, improve, and put in a pull request.

### Current request limits:
**Open Weather Map**: 4,000,000/day (max. 3000/min)
**Weather Underground**: 500/day (max. 10/min)

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
