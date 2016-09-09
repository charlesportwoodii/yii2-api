# Registration

New users can register for your service by sending a `POST` request to `/api/v1/user/register` with the following payload:

```
POST /api/v1/user/register
{
    "email": "<new_email>",
    "username": "<username>",
    "password": "<password>",
    "password_verify": "<password_verify>"
}
```

> Note that validation will fail if the `username` or `email` is already in use by another user.

If a validation errors occurs, the standard error response will be returned.

```json
{
    "data": null,
    "status": 400,
    "error": {
        "message": {...}
    }
}
```

If the user was successfully registered, an HTTP 200 will be returned.

```json
{
    "data": true,
    "status": 200
}
```

# Activation

Before the user can login to their account, they must first activate their account. As part of the registration process, the user will receive an email containing a unique activation code. This activation code can be used to activate their account by sending a `POST` request to `/api/v1/user/activate`

```
POST /api/v1/user/activate
{
    "activation_code": "<string_activation_code>"
}
```

If the activation code is invalid, an HTTP 400 will be returned.

```json
{
    "data": null,
    "status": 400,
    "error": {
        "message": {
            "activation_code": {...}
        }
    }
}
```

If the code was valid and matches the code on record, an HTTP 200 will be returned, and the user will be able to login.

```json
{
    "data": true,
    "status": 200
}
```