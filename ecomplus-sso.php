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

  // E-Com Plus platform microservice name
  private $service;

  function __construct ($secret, $service = 'market') {
    if (!is_string($secret) || strlen($secret) !== 32) {
      throw new Exception('Secret must be a string with 32 chars');
    }
    $this->secret = $secret;
    $this->$service = $service;
  }
}
