# One Time Keys

> For more information please see the special topic `json25519.md`.

This API supports encrypted requests and responses by utilizing `libsodium`. To setup an encrypted session between your client and the API you should first make a `GET` request to `/api/v1/server/otk`.

This endpoint will return the following payload:

```json
{
    "data": {
        "public": "hcjI0rrEnNVuKMAUeBHPFCoweZaiMSVtkLcmFOC2Nlc=",
        "signing": "dfANKVd/HoMifC+p7I5XmkAROHicKwhmrmuY5ESCtOM=",
        "signature": "hNrs6EY1OB6y70mp6OJnB6dDk7oyK4URKB41m4iBeHPf1q172cY8VYXL1dfLG+CarMK8BSEN5ySGUoN1+W3jBw==",
        "hash": "d77e410a2275b4b4b77cf4205d719ab6695f41a37c8849eb07f38d6354058ecb",
        "expires_at": 1489681621
    },
    "status": 200
}
```

The data provided in this response is for a _single_ use only. Once the API consumes it, it will be deleted.

The `public`, and `signing` elements are base64 encoded public keys for use with `crypto_box_seal_open` and `crypto_sign_open`, respectively. The `signature` element is the base64 encoded detached signature, and can be verified as follows:


```php
\Sodium\crypto_sign_verify_detached(
    \base64_decode($response['data']['signature']),
    \base64_decode($response['data']['public']),
    \base64_decode($response['data']['signing'])
);
```

Before continuing with future requests you should verify that the message signature is valid to ensure that the request was not tampered with during transit.

The `hash` element is the unique identifier user to identify this keypair for additional requests.

> This is a single use public key, and the hash will only be used once. Once you have established a session with the API you will be provided with a similar payload valid for the duration of your token.