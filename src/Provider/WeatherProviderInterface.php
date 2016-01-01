<?php
/**
 * Interface for Weather plugin providers
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2016 Chris Chrisostomou (https://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Weather
 */

namespace Chrismou\Phergie\Plugin\Weather\Provider;

use Phergie\Irc\Plugin\React\Command\CommandEvent;

interface WeatherProviderInterface
{
    /**
     * @param array $config
     */
    public function __construct(array $config = array());

    /**
     * Return the url for the API request
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @return string
     */
    public function getApiRequestUrl(CommandEvent $event);

    /**
     * Validate the provided parameters
     *
     * @param array $params
     * @return boolean
     */
    public function validateParams(array $params);

    /**
     * Returns an array of lines to send back to IRC when the http request is successful
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiResponse
     * @return array
     */
    public function getSuccessLines(CommandEvent $event, $apiResponse);

    /**
     * Return an array of lines to send back to IRC when there are no results
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiResponse
     * @return array
     */
    public function getNoResultsLines(CommandEvent $event, $apiResponse);

    /**
     * Return an array of lines to send back to IRC when the request fails
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param string $apiError
     * @return array
     */
    public function getRejectLines(CommandEvent $event, $apiError);

    /**
     * Returns an array of lines for the help response
     *
     * @return array
     */
    public function getHelpLines();
}
