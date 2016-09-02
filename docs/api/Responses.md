# API Responses

This API skeleton will always provide the following API response in JSON format:

```json
{
    "status": <int_http_status_code>,
    "data": null|true|false|{}|[]
    "error": {
        "code": <integer>,
        "message": "<user_safe_message>"
    }
}
```

The `status` response key indicates the HTTP response code that was sent back. This key should _always_ match the HTTP response sent back by the server.

The `data` attribute contains the main response that your client should consume. Depending upon the response, different values may be returned.

- For any error scenario, the `data` attribute will be set to `null`.
- For boolean actions, `true` or `false` may be returned.
- An object may be returned containing an array of data sets `{}`
- A single array may be returned `[]`

Be sure to read the relevant API documentation for each endpoint to decipher the meaning of the response body.

In the event of an error, the `error` key will be populated. If provided by the API, a unique error code will be provided, as well as the exception message that was thrown.