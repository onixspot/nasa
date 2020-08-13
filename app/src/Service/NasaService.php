<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use DateTimeInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Entity\Asteroid;
use DateTime;
use PhpParser\JsonDecoder;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Component\Form\FormFactoryInterface;

class NasaService
{
    public const BASE_URL = 'https://api.nasa.gov';

    /** @var string */
    private $apiKey;

    /** @var HttpClientInterface */
    private $client;
    /** @var FormFactoryInterface */
    private $formFactory;

    /**
     * NasaService constructor.
     *
     * @param string               $apiKey
     * @param HttpClientInterface  $client
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(string $apiKey, HttpClientInterface $client, FormFactoryInterface $formFactory)
    {
        $this->apiKey      = $apiKey;
        $this->client      = $client;
    }

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     *
     * @return array
     *
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function feed(DateTimeInterface $startDate, DateTimeInterface $endDate): array
    {
        $decoder  = new JsonDecoder();
        $response = $this->client->request(
            Request::METHOD_GET,
            $this->buildUrl(
                '/neo/rest/v1/feed',
                ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]
            )
        );
        ['near_earth_objects' => $objects] = $decoder->decode($response->getContent());
        $asteroids = [];
        foreach ($objects as $object) {
            $asteroids[] = array_map([$this, 'handleObject'], $object);
        }

        return array_merge(...$asteroids);
    }

    private function buildUrl(string $urlPath, array $params = []): string
    {
        $params['api_key'] = $this->apiKey;
        $qs                = http_build_query($params);

        return sprintf('%s/%s?%s', self::BASE_URL, ltrim($urlPath, '/'), $qs);
    }

    private function handleObject($object): Asteroid
    {
        $data = $object['close_approach_data'][0];

        return (new Asteroid())
            ->setReference((int) $object['neo_reference_id'])
            ->setSpeed((double) $data['relative_velocity']['kilometers_per_hour'])
            ->setIsHazardous((bool) $object['is_potentially_hazardous_asteroid'])
            ->setDate(new DateTime($data['close_approach_date_full']))
            ->setName($object['name']);
    }
}
