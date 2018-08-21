<?php

namespace Yaojinhui\Weather;

use GuzzleHttp\Client;
use Yaojinhui\Weather\Exceptions\InvalidArgumentException;
use Yaojinhui\Weather\Exceptions\HttpException;

class Weather
{
    protected $ak;
    protected $sn;
    protected $guzzleOptions = [];

    public function __construct(string $ak, string $sn = null)
    {
        $this->ak = $ak;
        $this->sn = $sn;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions) ;
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function getWeather(string $location, string $format = 'json', string $coordType = null)
    {
        $url = 'http://api.map.baidu.com/telematics/v3/weather';

        if (!\in_array($format, ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: ' . $format);
        }
        $query = array_filter([
            'ak' => $this->ak,
            'sn' => $this->sn,
            'location' => $location,
            'coord_type' => $coordType,
        ]);

        try {
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        return $format === 'json' ? \json_decode($response, true) : $response;
    }
}
