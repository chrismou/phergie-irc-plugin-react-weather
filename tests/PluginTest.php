<?php
/**
 * Phergie plugin for Return weather information for a given location (https://github.com/chrismou/phergie-irc-plugin-react-weather)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2015 Chris Chrisostomou (https://mou.me)
 * @license http://phergie.org/license New BSD License
 * @package Chrismou\Phergie\Plugin\Weather
 */

namespace Chrismou\Phergie\Tests\Plugin\Weather;

use Phake;
use Phergie\Irc\Bot\React\EventQueueInterface as Queue;
use Phergie\Irc\Plugin\React\Command\CommandEvent as Event;
use Chrismou\Phergie\Plugin\Weather\Plugin;

/**
 * Tests for the Plugin class.
 *
 * @category Chrismou
 * @package Chrismou\Phergie\Plugin\Weather
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Chrismou\Phergie\Plugin\Weather\Plugin
     */
    protected $plugin;

    /**
     * @var \Phergie\Irc\Plugin\React\Command\CommandEvent
     */
    protected $event;

    /**
     * @var \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected $queue;

    protected function setUp()
    {
        $this->event = $this->getMockEvent();
        $this->queue = $this->getMockQueue();
    }

    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', $this->getPlugin()->getSubscribedEvents());
    }

    /**
     * Tests the end to end process using the OpenWeatherMap provider
     */
    public function testOpenWeatherMap()
    {
        $this->plugin = $this->getPlugin(array(
            'provider' => 'Chrismou\\Phergie\\Plugin\\Weather\\Provider\\OpenWeatherMap',
            'config' => array('appId' => '')
        ));
        $httpConfig = $this->doCommandTest();
        $this->doResolveTest(file_get_contents(__DIR__ . '/_data/OpenWeatherMapResults.json'), $httpConfig);
        $this->doResolveNoResultsTest(file_get_contents(__DIR__ . '/_data/OpenWeatherMapNoResults.json'), $httpConfig);
        $this->doRejectTest($httpConfig);
        $this->doCommandHelpTest();

        // OpenWeatherMap requires at least 1 param
        $this->doCommandInvalidParamsResponseTest(array());
    }

    /**
     * Tests the end to end process using the OpenWeatherMap provider
     */
    public function testWunderground()
    {
        $this->plugin = $this->getPlugin(array(
            "provider" => 'Chrismou\\Phergie\\Plugin\\Weather\\Provider\\Wunderground',
            "config" => array("appId" => '')
        ));
        $httpConfig = $this->doCommandTest();
        $this->doResolveTest(file_get_contents(__DIR__ . '/_data/WundergroundResults.json'), $httpConfig);
        $this->doResolveNoResultsTest(file_get_contents(__DIR__ . '/_data/WundergroundNoResults.json'), $httpConfig);
        $this->doRejectTest($httpConfig);
        $this->doCommandHelpTest();

        // Wunderground requires at least 2 params so test both scenarios
        $this->doCommandInvalidParamsResponseTest(array());
        $this->doCommandInvalidParamsResponseTest(array("Leeds"));
    }

    /**
     * Tests handleCommand() is doing what it's supposed to
     * @return array $httpConfig
     */
    protected function doCommandTest()
    {
        Phake::when($this->event)->getCustomParams()->thenReturn(array("Leeds", "UK"));
        $this->plugin->handleCommand($this->event, $this->queue);
        Phake::verify($this->plugin->getEventEmitter())->emit('http.request', Phake::capture($httpConfig));
        $this->verifyHttpConfig($httpConfig);
        $request = reset($httpConfig);
        return $request->getConfig();
    }

    /**
     * Tests handleCommandHelp() is doing what it's supposed to
     */
    protected function doCommandHelpTest()
    {
        Phake::when($this->event)->getSource()->thenReturn('#channel');
        Phake::when($this->event)->getCommand()->thenReturn('PRIVMSG');
        Phake::when($this->event)->getCustomParams()->thenReturn(array("Leeds", "UK"));

        $this->plugin->handleCommandHelp($this->event, $this->queue);

        $helpLines = $this->plugin->getProvider()->getHelpLines();
        $this->assertInternalType('array', $helpLines);

        foreach ((array)$helpLines as $responseLine) {
            Phake::verify($this->queue)->ircPrivmsg('#channel', $responseLine);
        }
    }

    /**
     * Tests handleCommand() returns the provider's help response if invalid params are supplied
     *
     * @param array $invalidParams
     */
    protected function doCommandInvalidParamsResponseTest(array $invalidParams = array())
    {
        // Grab a freshly mocked event/queue
        $event = $this->getMockEvent();
        $queue = $this->getMockQueue();

        Phake::when($event)->getSource()->thenReturn('#channel');
        Phake::when($event)->getCommand()->thenReturn('PRIVMSG');
        Phake::when($event)->getCustomParams()->thenReturn($invalidParams);

        $this->plugin->handleCommand($event, $queue);

        $helpLines = $this->plugin->getProvider()->getHelpLines();

        foreach ((array)$helpLines as $responseLine) {
            Phake::verify($queue)->ircPrivmsg('#channel', $responseLine);
        }
    }

    /**
     * Tests handCommand() handles resolveCallback correctly
     *
     * @param string $command
     * @param array $httpConfig
     */
    protected function doResolveTest($data, array $httpConfig)
    {
        $this->doPreCallbackSetup();
        $callback = $httpConfig['resolveCallback'];
        $responseLines = $this->plugin->getProvider()->getSuccessLines($this->event, $data);
        $this->doPostCallbackTests($data, $callback, $responseLines);
    }

    /**
     * Tests handCommand() handles resolveCallback correctly
     *
     * @param string $command
     * @param array $httpConfig
     */
    protected function doResolveNoResultsTest($data, array $httpConfig)
    {
        $this->doPreCallbackSetup();
        $callback = $httpConfig['resolveCallback'];
        $responseLines = $this->plugin->getProvider()->getNoResultsLines($this->event, $data);
        $this->doPostCallbackTests($data, $callback, $responseLines);
    }

    /**
     * Tests handCommand() handles rejectCallback correctly
     *
     * @param array $httpConfig
     */
    protected function doRejectTest(array $httpConfig)
    {
        $error = "Foobar";
        $this->doPreCallbackSetup();
        $callback = $httpConfig['rejectCallback'];
        $responseLines = $this->plugin->getProvider()->getRejectLines($this->event, $error);
        $this->doPostCallbackTests($error, $callback, $responseLines);
    }

    /**
     * Sets mocks pre-callback
     */

    protected function doPreCallbackSetup()
    {
        Phake::when($this->event)->getSource()->thenReturn('#channel');
        Phake::when($this->event)->getCommand()->thenReturn('PRIVMSG');
    }

    /**
     * Sets mocks in preparation for a callback test
     *
     * @param string $data
     * @param callable $callback
     * @param array $responseLines
     */

    protected function doPostCallbackTests($data, $callback, $responseLines)
    {
        // Test we've had an array back and it has at least one response message
        $this->assertInternalType('array', $responseLines);
        $this->assertArrayHasKey(0, $responseLines);

        $this->assertInternalType('callable', $callback);

        // Run the resolveCallback callback
        $callback($data, $this->event, $this->queue);

        // Verify if each expected line was sent
        foreach ($responseLines as $responseLine) {
            Phake::verify($this->queue)->ircPrivmsg('#channel', $responseLine);
        }
    }

    /**
     * Verify the http object looks like what we're expecting
     *
     * @param array $httpConfig
     */
    protected function verifyHttpConfig(array $httpConfig)
    {
        // Check we have an array with one element
        $this->assertInternalType('array', $httpConfig);
        $this->assertCount(1, $httpConfig);

        $request = reset($httpConfig);

        // Check we have an instance of the http plugin
        $this->assertInstanceOf('\Phergie\Plugin\Http\Request', $request);

        // Check the url stored by http is the same as what we've called
        $this->assertSame($this->plugin->getProvider()->getApiRequestUrl($this->event), $request->getUrl());

        // Grab the response config and check the required callbacks exist
        $config = $request->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('resolveCallback', $config);
        $this->assertInternalType('callable', $config['resolveCallback']);
        $this->assertArrayHasKey('rejectCallback', $config);
        $this->assertInternalType('callable', $config['rejectCallback']);
    }

    /**
     * Returns a configured instance of the class under test.
     *
     * @param array $config
     *
     * @return \Chrismou\Phergie\Plugin\Weather\Plugin
     */
    protected function getPlugin(array $config = array())
    {
        $plugin = new Plugin($config);
        $plugin->setEventEmitter(Phake::mock('\Evenement\EventEmitterInterface'));
        $plugin->setLogger(Phake::mock('\Psr\Log\LoggerInterface'));

        return $plugin;
    }

    /**
     * Returns a mock command event.
     *
     * @return \Phergie\Irc\Plugin\React\Command\CommandEvent
     */
    protected function getMockEvent()
    {
        return Phake::mock('Phergie\Irc\Plugin\React\Command\CommandEvent');
    }

    /**
     * Returns a mock event queue.
     *
     * @return \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected function getMockQueue()
    {
        return Phake::mock('Phergie\Irc\Bot\React\EventQueueInterface');
    }
}
