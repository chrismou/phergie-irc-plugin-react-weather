<?php
/**
 * interface for various providers for the Weather plugin for Phergie (https://github.com/phergie/phergie-irc-bot-react)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Weather
 */

namespace Chrismou\Phergie\Plugin\Weather\Provider;

use Phergie\Irc\Plugin\React\Command\CommandEvent;

interface WeatherProviderInterface {

    function __construct(array $config=array());

    function validateParams(array $params);

    function getApiRequestUrl(CommandEvent $event);

    function getSuccessLines(CommandEvent $event, $apiResponse);

    function getRejectLines(CommandEvent $event, $apiError);

    function getNoResultsLines(CommandEvent $event, $apiResponse);

    function getHelpLines();

}