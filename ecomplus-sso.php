<?php

class EcomSSO
{
  /*
  Based on Official Single-Sign-On for Discourse (sso)
  https://meta.discourse.org/t/official-single-sign-on-for-discourse-sso/13045
  */

  // sso_secret: a secret string used to hash SSO payloads
  // Ensures payloads are authentic
  private $secret;

  // sso_url: the offsite URL users will be sent to when attempting to log on
  // Base E-Com Plus URL for admin authentication
  private $url = 'https://admin.e-com.plus/session/sso/v1/';

  function __construct ($secret, $service = 'market') {
    if (!is_string($secret) || strlen($secret) !== 32) {
      throw new Exception('Secret must be a string with 32 chars');
    }
    // save new secret token
    $this->secret = $secret;
    // complete auth URL with E-Com Plus microservice name
    // default to Market
    $this->url .= $service;
  }

  private function generate_nonce () {
    // create a new random hexadecimal string
    $c = [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'a', 'b', 'c', 'd', 'e', 'f' ];
    $count = count($c);
    $nonce = '';
    for ($i = 0; $i < 32; $i++) {
      $nonce .= $c[rand(0, $count)];
    }
    // save nonce as domain cookie
    // expires with current browser session
    setcookie('sso_nonce', $nonce, 0, '/');
    return $nonce;
  }

  private function get_nonce () {
    // get saved nonce from cookie
    return $_COOKIE['sso_nonce'];
  }

  private function generate_payload ($nonce = null) {
    // generate raw payload based on nonce
    if (!$nonce) {
      $nonce = $this->generate_nonce();
    }
    return 'nonce=' . $nonce;
  }

  private function encoded_payload ($payload = null) {
    // encode payload to Base64
    if (!$payload) {
      $payload = $this->generate_payload();
    }
    return base64_encode($payload);
  }

  private function hash_signature ($hash) {
    // payload is validated using HMAC-SHA256
    return hash_hmac('sha256', $hash, $this->secret);
  }

  /* Public methods */

  public function login_url ($redirect = false) {
    // new login flux
    // generate recirect URL
    $hash = $this->encoded_payload();
    $url = $this->url .
      '?sso=' . urlencode($hash) .
      '&sig=' . $this->hash_signature($hash);
    if ($redirect) {
      header('Location: ' . $url);
    }
    return $url;
  }

  public function handle_response () {
  }
}
