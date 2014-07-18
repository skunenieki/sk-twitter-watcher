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
