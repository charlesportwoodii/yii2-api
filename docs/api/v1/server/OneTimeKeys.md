# Bootstrapping an Encrypted Session

> For more information please see the special topic `json25519.md`.

This API supports encrypted requests and responses by utilizing `libsodium`. To setup an encrypted session between your client and the API you should first make a `GET` request to `/api/v1/server/otk`.

This endpoint will return the following payload:

```json
{
    "data": {
        "public": "9D8PQuHWQPfHEvRV/xhvdTy5UrTgFJbaxevCbEgG03g=",
        "hash": "ac5783741de9e3b382f7bbc9ca56dc7e2f4a5d85ffcddc12154483d432e0923c"
    },
    "status": 200
}
```

With this information you can bootstrap an encrypted, authenticated session between your client and the API. The `public` element is a base64 encoded public key that you can use with `crypto_box_*` methods to encrypt messages between your client and the API. The `hash` element is the unique identifier user to identify this keypair for additional requests, and will be submitted to the API as a `x-hashid`

> Note that this public key is a one time use key only, and will be deleted once it is consumed. Additionally this key is only valid for 15 minutes after creation. If the key is not consumed before then, the key will expire.