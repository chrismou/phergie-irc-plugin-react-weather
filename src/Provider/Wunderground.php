<?php

/**
 * OpenWeatherMap provider for the Weather plugin for Phergie
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Weather
 */

namespace Chrismou\Phergie\Plugin\Weather\Provider;

use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;

class Wunderground implements WeatherProviderInterface
{
    /**
     * @var string
     */
    protected $apiUrl = 'http://api.wunderground.com/api';

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
     * @return string
     */
    public function getApiRequestUrl(Event $event)
    {
        $params = $event->getCustomParams();

        // Final parameter should be the country
        $country = $params[count($params) - 1];
        // Remove the final paramater
        unset($params[count($params) - 1]);
        // Merge the remainder of the supplied params and remove disallowed punctuation
        $place = trim(implode("_", preg_replace('/[^\da-z\ ]/i', '', $params)));
        return sprintf("%s/%s/conditions/q/%s/%s.json", $this->apiUrl, $this->appId, $country, $place);
    }

    /**
     * Validate the provided parameters
     * The plugin requires at least one parameter (in most cases, this will be a location string)
     *
     * @param array $params
     * @return boolean
     */
    public function validateParams(array $params)
    {
        return (count($params) >= 2) ? true : false;
    }

    /**
     * Returns an array of lines to send back to IRC when the http request is successful
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiResponse
     * @return array
     */
    public function getSuccessLines(Event $event, $apiResponse)
    {
        $data = json_decode($apiResponse);
        if (isset($data->current_observation)) {
            $data = $data->current_observation;
            return array(
                sprintf(
                    "%s | %s | Temp: %dC | Humidity: %s | %s",
                    $data->display_location->full,
                    $data->weather,
                    round($data->temp_c),
                    $data->relative_humidity,
                    $data->forecast_url
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
     * @return array
     */
    public function getNoResultsLines(Event $event, $apiResponse)
    {
        return array('No weather found for this location');
    }

    /**
     * Return an array of lines to send back to IRC when the request fails
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiError
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
            '[place] - town, city, etc. Can be multiple words',
            '[country] - full name or country code (uk, us, etc)',
            'Instructs the bot to query Weather Undergound for info for the specified location'
        );
    }
}
