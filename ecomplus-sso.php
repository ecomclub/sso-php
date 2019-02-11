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

  function __construct ($secret = null, $service = 'market') {
    if ($secret === null && isset($_ENV['SSO_SECRET'])) {
      // get secret from environment variable by default
      $secret = $_ENV['SSO_SECRET'];
    }
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
    return @$_COOKIE['sso_nonce'];
  }

  private function generate_payload ($nonce = null) {
    // generate raw payload based on nonce
    if (!$nonce) {
      $nonce = $this->generate_nonce();
    }
    return 'nonce=' . $nonce;
  }

  private function hash_signature ($data) {
    // payload is validated using HMAC-SHA256
    return hash_hmac('sha256', $data, $this->secret);
  }

  /* Public methods */

  public function login_url ($redirect = false) {
    // new login flux
    // generate recirect URL
    $encoded_payload = base64_encode($this->generate_payload());
    $url = $this->url .
      '?sso=' . $encoded_payload .
      '&sig=' . $this->hash_signature($encoded_payload);
    if ($redirect) {
      header('Location: ' . $url);
    }
    return $url;
  }

  public function user_info ($encoded_payload, $signature) {
    // setup user object
    // not logged by default
    $user = array('logged' => false);
    // check signature first
    if ($this->hash_signature($encoded_payload) === $signature) {
      // validated
      $user['logged'] = true;
      $payload = @base64_decode($encoded_payload);
      parse_str($payload, $params);
      // check nonce ID
      if (@$params['nonce'] === $this->get_nonce()) {
        /*
        add user attributes:
        name; external_id; email; username; require_activation;
        custom.locale; custom.edit_storefront; custom.store_id;
        */
        foreach ($params as $key => $value) {
          if ($key !== 'nonce') {
            $user[$key] = $value;
          }
        }
      }
    }
    return $user;
  }

  public function handle_response () {
    // user is redirected back from login URL
    // callback URL: https://{service}.e-com.plus/session/sso_login
    // call this function on route specified above
    if (isset($_GET['sso']) && isset($_GET['sig'])) {
      // get user object
      return $this->user_info($_GET['sso'], $_GET['sig']);
    } else {
      // invalid URL query string
      return null;
    }
  }
}
