# API Documentation

This document provides general documentation surrounding the API.

## Special Topics

The following topics have their own page.

| Topic                                | Description                                                        |
|--------------------------------------|--------------------------------------------------------------------|
| [Authentication](Authentication.md)  | This page details how to authenticate against the API              |
| [Rate Limiting](Rate Limiting.md)    | This page provides general information on API rate limiting        |
| [Responses](Responses.md)            | This page outlines the response structure of the API               |
| [Encrypted json+25519](json25519.md) | This page outlines encrypted requests and responses                |

## Global Access Control

In the instance where you wish your API to be publically available, but want to restrict access to your API to known clients, or clients who agree to your terms and conditions. To facilitate this, the API offers two special configuration options, which allow you to specify a specific HTTP header and a secret value for that HTTP header. By setting these values, any client that does not submit the header with the secret value will receive an HTTP 403 error:

```json
{
    "data": null,
    "error": {
        "message": "",
        "code": 0
    }
    "status": 401
}
```

These configuration values can be set within your `config/config.yml` file.

> Note that both of these options must be set for the access control filter to take affect.

| Configuration Options    | Description                                |
|--------------------------|--------------------------------------------|
| `yii2:accessHeader`      | The HTTP header you want to check for      |
| `yii2:accessHeaderSecret`| The secret value to compare                |

As an example, assuming these values are set as follows:

```yaml
yii2:
  accessHeader: "X-Access-Header"
  accessHeaderSecret: "MySecretHeader"
```

In order to gain access to _any_ and all HTTP endpoints, the client would need to send the following extra headers along with _each_ requests (including HTTP `OPTIONS`) in order to not receive an HTTP 401.

```
X-Access-Header: MySecretHeader
```