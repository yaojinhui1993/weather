<?php

namespace Yaojinhui\Weather;

use GuzzleHttp\Client;
use Yaojinhui\Weather\Exceptions\InvalidArgumentException;
use Yaojinhui\Weather\Exceptions\HttpException;

class Weather
{
    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions) ;
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function getWeather($city, $extensions = 'base', $format = 'json')
    {
        $url = 'http://api.map.baidu.com/telematics/v3/weather';

        if (!in_array(strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: ' . $format);
        }

        if (!in_array(strtolower($extensions), ['base', 'all'])) {
            throw new InvalidArgumentException('Invalid extensions value(base/all): ' . $format);
        }

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type,
        ]);

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();
            return $format === 'json' ? json_decode($response, true) : $response;
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
