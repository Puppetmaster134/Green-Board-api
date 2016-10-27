# Green-Board-api
## Repository for the GreenBoard API

All endpoints must have a valid api key appended to the url as a GET parameter

example: `{server-url}/rest/public/api.php/{endpoint}/{endpoint-params}?api_key=abc123`
### Endpoints Currently Available:
**GetTrailById**

This gets a json-encoded trail object by the trail's id in the database. Typically used for debugging.

`{url}/rest/public/api.php/GetTrailById/{id}`

**WriteTrailToDB**

This writes a new trail to the database

`{url}/rest/public/api.php/GetTrailById/?lat={lat}&lng={lng}&trailObj={trailObj}`

**Register User**

This registers a new user through greenboard registration

`{url}/rest/public/api.php/RegisterUser/?username={username}&password={password}&email={email}`

**Login**

This logs a user in and returns their api key

`{url}/rest/public/api.php/GetTrailById/?username={username}&password={password}`

### Endpoints In Development:
**GetTrailByArea**

Test
