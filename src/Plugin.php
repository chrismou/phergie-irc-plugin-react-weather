<?php
/**
 * Phergie plugin for returning weather information for a given location (https://github.com/chrismou/phergie-irc-plugin-react-weather)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2016 Chris Chrisostomou (https://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Weather
 */

namespace Chrismou\Phergie\Plugin\Weather;

use Phergie\Irc\Bot\React\AbstractPlugin;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;
use GuzzleHttp\Message\Response;
use Phergie\Plugin\Http\Request as HttpRequest;
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
        $providerConfig = array();
        $provider = 'Chrismou\Phergie\Plugin\Weather\Provider\OpenWeatherMap';

        if (isset($config['config'])) {
            $providerConfig = (is_array($config['config'])) ? $config['config'] : array($config['config']);
        }

        if (isset($config['provider'])) {
            $provider = $config['provider'];
        }

        $this->provider = new $provider($providerConfig);
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
     * Handler for the weather command
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     */
    public function handleCommand(Event $event, Queue $queue)
    {
        if ($this->provider->validateParams($event->getCustomParams())) {
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
        $this->sendIrcResponse($event, $queue, $this->provider->getHelpLines());
    }

    /**
     * Set up the API request and set the callbacks
     *
     * @param \Phergie\Irc\Plugin\React\Command\CommandEvent $event
     * @param \Phergie\Irc\Bot\React\EventQueueInterface $queue
     *
     * @return \Phergie\Plugin\Http\Request
     */
    protected function getApiRequest(Event $event, Queue $queue)
    {
        $self = $this;
        return new HttpRequest(array(
            'url' => $this->provider->getApiRequestUrl($event),
            'resolveCallback' => function (Response $response) use ($self, $event, $queue) {
                $self->sendIrcResponse($event, $queue, $this->provider->getSuccessLines($event, $response->getBody()));
            },
            'rejectCallback' => function (Response $error) use ($self, $event, $queue) {
                $self->sendIrcResponse($event, $queue, $this->provider->getRejectLines($event, $error->getBody()));
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
}
