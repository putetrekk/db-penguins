<?php


namespace App\Services;


use MongoDB\Client;

class MongoDBService
{
    private $client;

    public function Client()
    {
        if ($this->client != null)
            return $this->client;

        $host = env('MONGODB_HOST', '127.0.0.1');
        $port = env('MONGODB_PORT', '27017');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');

        $this->client = new Client(
            "mongodb://${user}:${pass}@${host}:${port}/?compressors=disabled&gssapiServiceName=mongodb"
        );
        return $this->client;
    }
}
