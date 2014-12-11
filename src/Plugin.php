<?php
/**
 * Phergie plugin for returning weather information for a given location (https://github.com/chrismou/phergie-irc-plugin-react-weather)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Weather
 */

namespace Chrismou\Phergie\Plugin\Weather;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;
use WyriHaximus\Phergie\Plugin\Http\Request as HttpRequest;
use Chrismou\Phergie\Plugin\Weather\Provider\WeatherProviderInterface;

/**
 * Plugin class.
 *
 * @category Chrismou
 * @package Chrismou\Phergie\Plugin\Weather
 */
class Plugin extends AbstractPlugin
{
    /**
     * @var WeatherProviderInterface
     */
    protected $provider;

    /**
     * Accepts plugin configuration.
     *
     * Supported keys:
     *      provider
     *      config
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $providerConfig = (isset($config['config'])) ? $config['config'] : array();
        $provider = (isset($config['provider'])) ? $config['provider'] : 'Chrismou\Phergie\Plugin\Weather\Provider\OpenWeatherMap';
        $this->provider = new $provider((is_array($providerConfig))?$providerConfig:array($providerConfig));
    }

    /**
     * Return an array of commands and associated methods
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'command.weather' => 'handleCommand',
            'command.weather.help' => 'handleCommandHelp',
        );
    }

    /**
     * Validate the provided parameters
     * The plugin requires at least one parameter (in most cases, this will be a location string)
     *
     * @param array $params
     *
     * @return true|false
     */
    public function validateParams(array $params)
    {
        return (count($params)) ? true : false;
    }

    /**
     * Handler for the weather command
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommand(Event $event, Queue $queue)
    {
        if ($this->validateParams($event->getCustomParams())) {
            $request = $this->getApiRequest($event, $queue);
            $this->getEventEmitter()->emit('http.request', array($request));
        } else {
            $this->handleCommandhelp($event, $queue);
        }
    }

    /**
     * Handler for the weather help command
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommandHelp(Event $event, Queue $queue)
    {
        $this->sendIrcResponse($event, $queue, $this->getHelpLines());
    }

    /**
     * Set up the API request and set the callbacks
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     *
     * @return \WyriHaximus\Phergie\Plugin\Http\Request
     */
    protected function getApiRequest(Event $event, Queue $queue)
    {
        $self = $this;
        return new HttpRequest(array(
            'url' => $this->provider->getApiRequestUrl($event, $queue),
            'resolveCallback' => function ($data) use ($self, $event, $queue) {
                $self->sendIrcResponse($event, $queue, $this->provider->getSuccessLines($event, $data));
            },
            'rejectCallback' => function ($error) use ($self, $event, $queue) {
                $self->sendIrcResponse($event, $queue, $this->provider->getRejectLines($event, $error));
            }
        ));
    }

    /**
     * Send an array of response lines back to IRC
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     * @param array $ircResponse
     */
    protected function sendIrcResponse(Event $event, Queue $queue, array $ircResponse)
    {
        foreach ($ircResponse as $ircResponseLine) {
            $this->sendIrcResponseLine($event, $queue, $ircResponseLine);
        }
    }

    /**
     * Send a single response line back to IRC
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     * @param string $ircResponseLine
     */
    protected function sendIrcResponseLine(Event $event, Queue $queue, $ircResponseLine)
    {
        $queue->ircPrivmsg($event->getSource(), $ircResponseLine);
    }

    /**
     * Return an instance of a weather provider
     *
     * @return WeatherProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Returns an array of lines for the help response
     *
     * @return array
     */
    public function getHelpLines()
    {
        return array(
            'Usage: weather [location]',
            '[location] - address, city, postcode, etc',
            'Instructs the bot to query OpenWeatherMap for weather info for the specified location'
        );
    }
}
