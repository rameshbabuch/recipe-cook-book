<?php
/**
 * Created by PhpStorm.
 * User: rameshpaul
 * Date: 7/12/16
 * Time: 9:21 PM
 */

use Mailgun\Mailgun;

class Mailer
{

  private $domain;
  private $apiKey;

  public function __construct($config)
  {
    $this->domain = $config['domain'];
    $this->apiKey = $config['apiKey'];
  }

  public function send($to, $subject = '', $message = '')
  {
    $mg = new Mailgun($this->apiKey);

    $response = $mg->sendMessage($this->domain, array(
        'html' => $message,
        'from' => 'chrb.rameshbabu@gmail.com',
        'to' => $to,
        'subject' => $subject
      )
    );

    if ($response->http_response_code != 200) {
      throw new Exception("Mail not sent - Mailgun returned " . $response->http_response_code);
    }
  }
}