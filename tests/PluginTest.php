<?php
/**
 * Phergie plugin for Return weather information for a given location (https://github.com/chrismou/phergie-irc-plugin-react-weather)
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-weather for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
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
     * @var \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected $event;

    /**
     * @var \Phergie\Irc\Bot\React\EventQueueInterface
     */
    protected $queue;

    protected function setUp()
    {
        $this->event = Phake::mock('Phergie\Irc\Plugin\React\Command\CommandEvent');
        $this->queue = Phake::mock('Phergie\Irc\Bot\React\EventQueueInterface');
    }

    /**
     * Tests that getSubscribedEvents() returns an array.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', $this->getPlugin()->getSubscribedEvents());
    }

    public function testOpenWeatherMap() {
        $this->plugin = $this->getPlugin(array(
            "provider" => new \Chrismou\Phergie\Plugin\Weather\Provider\OpenWeatherMap,
            "config" => array("appId" => "")
        ));
        $httpConfig = $this->doCommandTest();
        $this->doResolveTest(file_get_contents(__DIR__.'/_data/OpenWeatherMapResults.json'), $httpConfig);
        $this->doResolveNoResultsTest(file_get_contents(__DIR__.'/_data/OpenWeatherMapNoResults.json'), $httpConfig);
        $this->doRejectTest($httpConfig);
        $this->doCommandHelpTest();
    }

    /**
     * Tests handCommand() is doing what it's supposed to
     *
     * @param string $command
     * @param array $params
     *
     * @return array $httpConfig
     */
    protected function doCommandTest()
    {
        Phake::when($this->event)->getCustomParams()->thenReturn(array("Leeds,UK"));
        $this->plugin->handleCommand($this->event, $this->queue);
        Phake::verify($this->plugin->getEventEmitter())->emit('http.request', Phake::capture($httpConfig));
        $this->verifyHttpConfig($httpConfig);
        $request = reset($httpConfig);
        return $request->getConfig();
    }

    /**
     * Tests handCommandHelp() is doing what it's supposed to
     *
     * @param array \Chrismou\Phergie\Plugin\Weather\Plugin
     */
    protected function doCommandHelpTest()
    {
        Phake::when($this->event)->getSource()->thenReturn('#channel');
        Phake::when($this->event)->getCommand()->thenReturn('PRIVMSG');
        Phake::when($this->event)->getCustomParams()->thenReturn(array("Leeds,UK"));

        $this->plugin->handleCommandHelp($this->event, $this->queue);

        $helpLines = $this->plugin->getProvider()->getHelpLines();
        $this->assertInternalType('array', $helpLines);

        foreach ((array)$helpLines as $responseLine) {
            Phake::verify($this->queue)->ircPrivmsg('#channel', $responseLine);
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
     * @param string $command
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
     *
     * @param string $command
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
     * @param string $command
     * @param string $command
     */

    protected function doPostCallbackTests($data, $callback, $responseLines)
    {
        // Test we've had an array back and it has at least one response message
        $this->assertInternalType('array', $responseLines);
        $this->assertArrayHasKey(0, $responseLines);

        // Run the resolveCallback callback
        $callback($data, $this->event, $this->queue);

        // Verify if each expected line was sent
        foreach ($responseLines as $responseLine) {
            Phake::verify($this->queue)->ircPrivmsg('#channel', $responseLine);
        }
    }

    /**
     * Tests handCommand() is doing what it's supposed to
     *
     * @param array $httpConfig
     * @param string $provider
     */
    protected function verifyHttpConfig(array $httpConfig)
    {
        // Check we have an array with one element
        $this->assertInternalType('array', $httpConfig);
        $this->assertCount(1, $httpConfig);

        $request = reset($httpConfig);

        // Check we have an instance of the http plugin
        $this->assertInstanceOf('\WyriHaximus\Phergie\Plugin\Http\Request', $request);

        // Check the url stored by htttp is the same as what we've called
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
}
