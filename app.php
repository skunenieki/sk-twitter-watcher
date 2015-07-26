<?php

require_once 'vendor/autoload.php';
require_once 'FilterTrackConsumer.php';

try {
    $dotenv = new Dotenv\Dotenv(__DIR__);
    $dotenv->load();
} catch(Exception $e) {
    //
}

use PhpAmqpLib\Connection\AMQPConnection;

$amqpUrl           = getenv('CLOUDAMQP_URL');
$accessToken       = getenv('TWITTER_ACCESS_TOKEN');
$consumerKey       = getenv('TWITTER_CONSUMER_KEY');
$consumerSecret    = getenv('TWITTER_CONSUMER_SECRET');
$accessTokenSecret = getenv('TWITTER_TOKEN_SECRET');

$exchange = 'router';
$queue    = 'msgs';
$port     = '5672';

$parts   = explode('/', $amqpUrl);
$user    = $vhost = $parts[3];
$parts   = explode('@', $parts[2]);
$host    = $parts[1];
$pass    = str_replace($user.':', '', $parts[0]);

$conn = new AMQPConnection($host, $port, $user, $pass, $vhost);
$ch = $conn->channel();

$ch->queue_declare($queue, false, true, false, false);
$ch->exchange_declare($exchange, 'direct', false, true, false);
$ch->queue_bind($queue, $exchange);

$sc = new FilterTrackConsumer($accessToken, $accessTokenSecret, Phirehose::METHOD_FILTER);
$sc->consumerSecret = $consumerSecret;
$sc->consumerKey = $consumerKey;
$sc->setTrack(array('skunenieki'));
$sc->setAmqpAndExchange($ch, $exchange);
$sc->consume(true);

$ch->close();
$conn->close();
