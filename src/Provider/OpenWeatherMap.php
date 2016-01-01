<?php

/**
 * OpenWeatherMap provider for the Weather plugin for Phergie
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2016 Chris Chrisostomou (https://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Weather
 */

namespace Chrismou\Phergie\Plugin\Weather\Provider;

use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;

class OpenWeatherMap implements WeatherProviderInterface
{
    /**
     * @var string
     */
    protected $apiUrl = 'http://api.openweathermap.org/data/2.5/weather';

    /**
     * @var string
     */
    protected $appId = "";

    public function __construct(array $config = array())
    {
        if (isset($config['appId'])) {
            $this->appId = $config['appId'];
        }
    }

    /**
     * Return the url for the API request
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     *
     * @return string
     */
    public function getApiRequestUrl(Event $event)
    {
        $params = $event->getCustomParams();
        $query = trim(implode(" ", $params));

        $querystringParams = array(
            'q' => $query,
            'appid' => $this->appId
        );

        return sprintf("%s?%s", $this->apiUrl, http_build_query($querystringParams));
    }

    /**
     * Validate the provided parameters
     * The plugin requires at least one parameter (in most cases, this will be a location string)
     *
     * @param array $params
     *
     * @return boolean
     */
    public function validateParams(array $params)
    {
        return (count($params)) ? true : false;
    }

    /**
     * Returns an array of lines to send back to IRC when the http request is successful
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiResponse
     *
     * @return array
     */
    public function getSuccessLines(Event $event, $apiResponse)
    {
        $data = json_decode($apiResponse);
        if (isset($data->name) && $data->name) {
            return array(
                sprintf(
                    "%s, %s | %s | Temp: %dC | Humidity: %s%% | Sunrise: %s | Sunset: %s",
                    $data->name,
                    $data->sys->country,
                    $data->weather[0]->main,
                    round($data->main->temp - 273.15),
                    $data->main->humidity,
                    date("H:i:s", $data->sys->sunrise),
                    date("H:i:s", $data->sys->sunset)
                )
            );
        } else {
            return $this->getNoResultsLines($event, $apiResponse);
        }
    }

    /**
     * Return an array of lines to send back to IRC when there are no results
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiResponse
     *
     * @return array
     */
    public function getNoResultsLines(Event $event, $apiResponse)
    {
        return array('No weather data found for this location');
    }

    /**
     * Return an array of lines to send back to IRC when the request fails
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiError
     *
     * @return array
     */
    public function getRejectLines(Event $event, $apiError)
    {
        return array('Something went wrong... ಠ_ಠ');
    }

    /**
     * Returns an array of lines for the help response
     *
     * @return array
     */
    public function getHelpLines()
    {
        return array(
            'Usage: weather [place] [country]',
            '[place] - address, town, city, zip code, etc. Can be multiple words',
            'Instructs the bot to query OpenWeatherMap for weather info for the specified location'
        );
    }
}
