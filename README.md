# Weather plugin for [Phergie](http://github.com/phergie/phergie-irc-bot-react/)

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin for returning weather information for a given location.

[![Build Status](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/badges/build.png?b=master)](https://scrutinizer-ci.com/g/chrismou/phergie-irc-plugin-react-weather/build-status/master)
[![Test Coverage](https://codeclimate.com/github/chrismou/phergie-irc-plugin-react-weather/badges/coverage.svg)](https://codeclimate.com/github/chrismou/phergie-irc-plugin-react-weather/coverage)
[![Code Climate](https://codeclimate.com/github/chrismou/phergie-irc-plugin-react-weather/badges/gpa.svg)](https://codeclimate.com/github/chrismou/phergie-irc-plugin-react-weather)

## About
This plugin provides a method for performing weather lookups for a specified town/city/zip code. OpenWeatherMap, the default provider, simply requires a location to search on,
whereas the Wunderground provider requires a location and a country. Future providers may have similar inconsistencies, but if you're using the 
[CommandHelp plugin](https://github.com/phergie/phergie-irc-plugin-react-commandhelp) you should always be able to find the correct syntax for your provider of choice by by running 
"help weather" from within IRC.

## Install

The recommended method of installation is [through composer](http://getcomposer.org).

```JSON
composer require chrismou/phergie-irc-plugin-react-weather
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

This plugin requires the [Command plugin](https://github.com/phergie/phergie-irc-plugin-react-command) to recognise commands, and the
[http plugin](https://github.com/WyriHaximus/PhergieHttp) to query Google for your search results.

If you're new to Phergie or Phergie plugins, see the [Phergie setup instructions](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#configuration)
for more information.  Otherwise, add the following references to your config file:

```php
return array(
	// ...
    'plugins' => array(
		new \Phergie\Irc\Plugin\React\Command\Plugin,   // dependency
		new \WyriHaximus\Phergie\Plugin\Dns\Plugin,     // dependency
		new \WyriHaximus\Phergie\Plugin\Http\Plugin,    // dependency
		new \Chrismou\Phergie\Plugin\Weather\Plugin(array(
        
            "config" => array("appId" => "YOUR_APP_ID")
        
        ))
	)
)
```

The default provider is OpenWeatherSearch, which requires a free api key to use (you can get one from 
[here](http://openweathermap.org/appid)).  To use OpenWeatherMap, you only need to provide the API key.

There's also a Weather Underground provider included. It's a bit of a work in progress - it currently requires a city and country and tends to not find 
the location as often - but it does work.  If you think you can improve it, feel free to fork/fix/pull request or send me your suggestions. :)

Weather underground also requires an API key, which can you get for free from [here](http://www.wunderground.com/weather/api/). You'll also need to
specify you're using this provider in your Phergie config:

```php
new \Chrismou\Phergie\Plugin\Weather\Plugin(array(
	
	'provider' => 'Chrismou\\Phergie\\Plugin\\Weather\\Provider\\Wunderground',
    "config" => array("appId" => "YOUR_APP_ID")

))
```
Or if you know of any other weather services, you can write your own - feel free to fork and improve!

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
