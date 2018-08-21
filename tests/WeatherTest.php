<?php

namespace Yaojinhui\Weather\Tests;

use Mockery;
use GuzzleHttp\Psr7\Response;
use Yaojinhui\Weather\Weather;
use PHPUnit\Framework\TestCase;
use Yaojinhui\Weather\Exceptions\InvalidArgumentException;
use Mockery\Matcher\AnyArgs;
use Yaojinhui\Weather\Exceptions\HttpException;

class WeatherTest extends TestCase
{
    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-ak');

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Invalid extensions value(base/all): foo');

        $w->getWeather('深圳', 'foo');

        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }

    /** @test */
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Invalid response format: array');

        $w->getWeather('深圳', 'base', 'array');

        $this->fail('Failed to assert getWeather throw exception with invalid argument');
    }

    public function testGetWeatherWithJson()
    {
        $response = new Response(200, [], '{"success": true}');
        $client = \Mockery::mock(Client::class);

        $client->allows()->get(
            'http://api.map.baidu.com/telematics/v3/weather',
            [
                'query' => [
                    'key' => 'mock-key',
                    'city' => '深圳',
                    'output' => 'json',
                    'extensions' => 'base'
                ]
            ]
        )->andReturn($response);

        $w = Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $w->getWeather('深圳'));
    }

    public function testGetWeatherWithXml()
    {
        $response = new Response(200, [], '<hello>content</hello>');
        $client = \Mockery::mock(Client::class);

        $client->allows()->get(
            'http://api.map.baidu.com/telematics/v3/weather',
            [
                'query' => [
                    'key' => 'mock-key',
                    'city' => '深圳',
                    'extensions' => 'base',
                    'output' => 'xml',
                ]
            ]
        )->andReturn($response);

        $w = Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>', $w->getWeather('深圳', 'base', 'xml'));
    }

    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = Mockery::mock(Client::class);
        $client->allows()
            ->get(new AnyArgs())
            ->andThrow(new \Exception('request timeout'));

        $w = Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $w->getWeather('深圳');
    }

    public function testGetHttpClient()
    {
        $w = new Weather('mock-key');

        $this->assertInstanceOf(\GuzzleHttp\ClientInterface::class, $w->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $w = new Weather('mock-key');

        $this->assertNull($w->getHttpClient()->getConfig('timeout'));

        $w->setGuzzleOptions(['timeout' => 5000]);

        $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
    }
}
