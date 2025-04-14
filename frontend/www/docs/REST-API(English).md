# omegaUp API

The omegaUp platform is built on a [REST](https://en.wikipedia.org/wiki/Representational_state_transfer) API that can be accessed directly. All endpoints use standard HTTP methods (`GET` or `POST`) and respond with appropriate HTTP status codes along with a JSON-formatted response.

To ensure user privacy and prevent cheating, **only HTTPS** connections are allowed. Any requests made over plain HTTP will fail, returning an `HTTP 301 Permanent Redirect`.

## Base URL

All API endpoints are prefixed with:

https://omegaup.com/api/


In this documentation, only the part of the URL **after** this prefix is shown. For example, the endpoint for retrieving the server time is referred to as `time/get`, but the complete URL is:

https://omegaup.com/api/time/get/


## Authentication

Some API endpoints are public and require no authentication—you can even access them by visiting the URL in a browser. However, certain endpoints are protected and require the user to be logged in.

To authenticate, use the [`user/login`](https://github.com/omegaup/omegaup/blob/main/frontend/www/docs/User-API.md) endpoint. Upon successful login, you will receive an `auth_token`. You must include this token in a cookie named `ouat` (omegaUp Auth Token) for all subsequent authenticated requests.

**Important:** omegaUp supports only one active session at a time. Logging in programmatically will invalidate your browser session, and vice versa.

## API Categories

- [Contests API](./Contests-API.md)
- [Problems API](./Problems-API.md)
- [Runs API](./Runs-API.md)
- [Users API](./Users-API.md)
- [Clarifications API](./Clarifications-API.md)


## Example: Get Server Time

To retrieve the current time from the server, make a `GET` request to:

https://omegaup.com/api/time/get/


This is a public endpoint and does not require authentication. If successful, it will return:

```json
{
  "time": 1436577101,
  "status": "ok"
}
```

### Endpoint: `GET time/get/`

#### Description

Returns the current UNIX timestamp according to the server's internal clock. This can be useful for synchronizing a potentially incorrect local clock.

#### Required Permissions

None

#### Request Parameters

None

#### Response Format

| Field   | Type   | Description                                      |
|---------|--------|--------------------------------------------------|
| status  | string | Will return `"ok"` if the request was successful |
| time    | int    | UNIX timestamp representing the server time      |

