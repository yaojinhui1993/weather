<?php

namespace Yaojinhui\Weather;

use GuzzleHttp\Client;
use Yaojinhui\Weather\Exceptions\InvalidArgumentException;
use Yaojinhui\Weather\Exceptions\HttpException;

class Weather
{
    protected $key;

    protected $guzzleOptions = [];

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function getWeather($city, $extensions = 'live', $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo?parameters';

        $extensionsTypes = [
            'live' => 'base',
            'forcast' => 'all',
        ];

        if (!in_array(strtolower($extensions), ['live', 'forcast'])) {
            throw new InvalidArgumentException('Invalid extensions types(live/forcast): '.$extensions);
        }

        if (!in_array(strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'extensions' => $extensionsTypes[$extensions],
            'output' => $format,
        ]);

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();

            return 'json' === $format ? json_decode($response, true) : $response;
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'live', $format);
    }

    public function getForcastsWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'forcast', $format);
    }
}
