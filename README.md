# Weather plugin for [Phergie](http://github.com/phergie/phergie-irc-bot-react/)

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for returning weather information for a given location.

[![Build Status](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/badges/build.png?b=master)](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/?branch=master)

## About
This plugin provides a method for performing weather lookups for a specified town/city/zip code. OpenWeatherMap, the default provider, simply requires a location to search on,
whereas the Wunderground provider requires a location and a country. Future providers may have similar inconsistencies, but if you're using the 
[CommandHelp plugin](https://github.com/phergie/phergie-irc-plugin-react-commandhelp) you should always be able to find the correct syntax for your provider of choice by by running 
"help weather" from within IRC.

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
        "chrismou/phergie-irc-plugin-react-weather": "~1"
    }
}
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

The default provider is OpenWeatherSearch, which requires a free api key to use (you can get one from 
[here](http://openweathermap.org/appid)).

To use OpenWeatherMap, you only need to provide the API key:

```php
new \Chrismou\Phergie\Plugin\Weather\Plugin(array(

    "config" => array("appid" => "YOUR_APP_ID")

))
```

There's also a Weather Underground provider included. It's a bit of a work in progress - it currently requires a city and country and tends to not find 
the location as often - but it works.  If you think you can improve it, feel free to fork/fix/pull request or send me your suggestions. :-)

Weather underground also requires an API key, which can you get for free from [here](http://www.wunderground.com/weather/api/).

```php
new \Chrismou\Phergie\Plugin\Weather\Plugin(array(
	
	'provider' => 'Chrismou\\Phergie\\Plugin\\Weather\\Provider\\Wunderground',
    "config" => array("appid" => "YOUR_APP_ID")

))
```
Or you can alway write your own - feel free to fork and improve!

#### Current request limits:
* **Open Weather Map**: 4,000,000/day (max. 3000/min)
* **Weather Underground**: 500/day (max. 10/min)

## Tests

To run the unit test suite:

```
curl -s https://getcomposer.org/installer | php
php composer.phar install
./vendor/bin/phpunit
```

## License

Released under the BSD License. See `LICENSE`.
