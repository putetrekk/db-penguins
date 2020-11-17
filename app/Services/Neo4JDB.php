<?php


namespace App\Services;

use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\ClientInterface;

class Neo4JDB
{
    private $client;

    /**
     * @return ClientInterface
     */
    public function Client()
    {
        if ($this->client != null)
            return $this->client;

        $host = env('NEO4J_HOST', 'localhost');
        $port = env('NEO4J_PORT', '7687');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');

        $this->client = ClientBuilder::create()
            ->addConnection('default', "bolt://$user:$pass@$host:$port")
            ->build();

        return $this->client;
    }
}
