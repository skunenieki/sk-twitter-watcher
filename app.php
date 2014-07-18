<?php

require_once 'vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPConnection;

$consumerKey       = getenv('TWITTER_CONSUMER_KEY');
$consumerSecret    = getenv('TWITTER_CONSUMER_SECRET');
$accessToken       = getenv('TWITTER_ACCESS_TOKEN');
$accessTokenSecret = getenv('TWITTER_TOKEN_SECRET');

$exchange = 'router';
$queue    = 'msgs';
$port     = '5672';

$amqpUrl = getenv('CLOUDAMQP_URL');
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

class FilterTrackConsumer extends OauthPhirehose
{
    /**
    * Enqueue each status
    *
    * @param string $status
    */
    public function enqueueStatus($status)
    {
        $data = json_decode($status, true);
        if (is_array($data) && isset($data['entities']['media'])) {
            foreach ($data['entities']['media'] as $media) {
                if ($media['type'] == 'photo') {
                    $msg_body = json_encode($data);
                    $msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
                    $amqp->basic_publish($msg, $this->exchange);

                    error_log($msg_body);
                }
            }
        }
    }

    /**
     * Set AMQP channel and exchange name
     * @param PhpAmqpLib\Channel\AbstractChannel $amqp     AbstractChannel instance
     * @param string                             $exchange Name of exnahnge
     */
    public function setAmqpAndExchange($amqp, $exchange) {
        $this->amqp     = $amqp;
        $this->exchange = $exchange;
    }
}
