<?php

use PhpAmqpLib\Message\AMQPMessage;

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
                    $message = array(
                        'source' => 'twitter',
                        'url'    => $media['media_url'] . ':large',
                        'author' => $data['user']]'screen_name'],
                        'time'   => strtotime($data['created_at']),
                    );
                    $msg_body = json_encode($message);
                    $msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
                    $this->amqp->basic_publish($msg, $this->exchange);

                    $this->log('Pushed message to queue!');
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
