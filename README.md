# sso-php
Single-Sign-On for E-Com Plus platform users with PHP

## Reference

Based on [Official Single-Sign-On for Discourse (sso)](https://meta.discourse.org/t/official-single-sign-on-for-discourse-sso/13045)

## Usage

1. Save the secret (32 bytes string) on __SSO_SECRET__ environment variable
2. Import the script and create new object with `EcomSSO` class
3. Call `login_url` to redirect user to new login flow
4. Call `handle_response` at callback endpoint __/session/sso_login__

## Sample

```php
require './ecomplus-sso.php';
$sso = new EcomSSO();
```

### Start Login Flow

```php
$sso->login_url(true);
```

### Handle Callback Redirect

```php
$user = $sso->handle_response();
if ($user !== null) {
  if ($user['logged']) {
    /*
    user attributes:
    name; external_id; email; username; require_activation;
    custom.locale; custom.edit_storefront; custom.store_id;
    */
    if ($user['email']) {
      // do the stuff
    }
  } else {
    // user unlogged
    http_response_code(401);
  }
} else {
  // invalid request
  http_response_code(400);
}
```
