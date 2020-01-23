<?php


class Episodes
{

    const EPISODES_CACHE_KEY = 'simpsons-episodes';
    const SEASONS_CACHE_KEY = 'simpsons-seasons';
    const CACHE_EXPIRY = 5000;

    public $client;
    public $redis;

    public function __construct()
    {
        //Get the episodes from the API
        $this->client = new GuzzleHttp\Client();

        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    /**
     * @param null $seasonFilter
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($seasonFilter = null)
    {
        $data = [];

        if ($this->redis->get(self::EPISODES_CACHE_KEY)) {

            $data = json_decode($this->redis->get(self::EPISODES_CACHE_KEY), TRUE);

        } else {

            try {

                $res = $this->client->request('GET', 'http://3ev.org/dev-test-api/');
                $resBody = $res->getBody();

                $data = json_decode($resBody, true);

                usort($data, function($a, $b) {
                    $value =  $a['season'] <=> $b['season'];
                    return (0 == $value) ? $a['episode'] <=> $b['episode'] : $value;
                });

                if (!$seasonFilter) {
                    $this->redis->set(
                        self::EPISODES_CACHE_KEY,
                        json_encode($data),
                        self::CACHE_EXPIRY
                    );
                }

            } catch (\GuzzleHttp\Exception\ServerException $exception) {
                $error = sprintf('Error: Code [%s], Message [%s]', $exception->getCode(), $exception->getMessage());
            } catch (Exception $exception) {
                $error = sprintf('Error: Code [%s], Message [%s]', $exception->getCode(), $exception->getMessage());
            }

        }

        if ($seasonFilter) {
            return array_filter($data, function ($item) use ($seasonFilter) {
                return ((int) $item['season'] == (int) $seasonFilter);
            });
        }


        return $data;
    }

    /**
     * @return array|mixed
     */
    public function retrieveSeasons()
    {

        if ($this->redis->get(self::SEASONS_CACHE_KEY)) {
            return json_decode($this->redis->get(self::SEASONS_CACHE_KEY));
        }

        $episodes = json_decode($this->redis->get(self::EPISODES_CACHE_KEY), TRUE);

        if (empty($episodes)) {
            return [];
        }

        $seasons = array_values(
            array_unique(
                array_map(function ($i) {
                    return $i['season'];
                }, $episodes)
            )
        );

        $this->redis->set(
            self::SEASONS_CACHE_KEY,
            json_encode($seasons),
            self::CACHE_EXPIRY
        );

        return $seasons;
    }

}