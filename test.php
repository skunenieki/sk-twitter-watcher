<?php

require_once 'vendor/autoload.php';

class FilterTrackConsumer extends OauthPhirehose
{
  /**
   * Enqueue each status
   *
   * @param string $status
   */
  public function enqueueStatus($status)
  {
    /*
     * In this simple example, we will just display to STDOUT rather than enqueue.
     * NOTE: You should NOT be processing tweets at this point in a real application, instead they should be being
     *       enqueued and processed asyncronously from the collection process.
     */
    $data = json_decode($status, true);
    if (is_array($data) && isset($data['entities']['media'])) {
        foreach ($data['entities']['media'] as $media) {
            if ($media['type'] == 'photo') {
                error_log(json_encode($data));
                exit;
            }
        }
    }
  }
}

$consumerKey = 'Kbilqq03C5lNlov5aHVdQ';
$consumerSecret = 'MU3e9dI7lxlP2AmkEqY8G1dlqkgagHTsVJwXWx1VTAY';
$accessToken = '230392868-iTzTLsMGD3aMQ1nePLqVSSniWH1z0dREZrIjCdpQ';
$accessTokenSecret = 'pOo2jhmysrLYVyaFX6Ltv4wTnlHDgHo3jQnb6CqORc';


$sc = new FilterTrackConsumer($accessToken, $accessTokenSecret, Phirehose::METHOD_FILTER);
$sc->consumerSecret = $consumerSecret;
$sc->consumerKey = $consumerKey;
$sc->setTrack(array('skunenieki'));
$sc->consume(true);

