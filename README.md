# sso-php
Single-Sign-On for E-Com Plus platform users with PHP

## Reference

Based on [Official Single-Sign-On for Discourse (sso)](https://meta.discourse.org/t/official-single-sign-on-for-discourse-sso/13045)

## Usage

1. Save the secret (32 bytes string) on __SSO_SECRET__ environment variable
2. Import the script and create new object with `EcomSSO` class
3. Call `login_url` to redirect user to new login flow
4. Call `handle_response` at callback endpoint __/session/sso_login__

## Samples

```php
require './ecomplus-sso.php';
$sso = new EcomSSO();
```

### Start login flow

```php
$sso->login_url(true);
```

### Handle callback redirect

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

### Custom secret and service

Custom secret token

```php
// default is to get from SSO_SECRET env
$sso = new EcomSSO('cb68251eefb5211e58c00ff1395f0c0b');
```

Custom service name

```php
// default service name is 'market'
// must match with subdomain name
// eg.: 'builder' for 'builder.e-com.plus'
$sso = new EcomSSO(null, 'builder');
```

Both custom secret and service

```php
$sso = new EcomSSO('cb68251eefb5211e58c00ff1395f0c0b', 'builder');
```
