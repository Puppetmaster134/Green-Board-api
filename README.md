# Green-Board-api
## Repository for the GreenBoard API

Endpoints containing {api_key} must have a valid api key appended to the url as a GET parameter

example: `{server-url}/rest/public/api.php/{endpoint}/{endpoint-params}?key=abc123`

### Endpoints Currently Available:
**GetTrailById**

This gets a json-encoded trail object by the trail's id in the database. Typically used for debugging.

`{url}/rest/public/api.php/GetTrailById/{id}&key={api_key}`

**WriteTrailToDB**

This writes a new trail to the database

`{url}/rest/public/api.php/GetTrailById/?lat={lat}&lng={lng}&trailObj={trailObj}&key={api_key}`

**Register User**

This registers a new user through greenboard registration

`{url}/rest/public/api.php/RegisterUser/?username={username}&password={password}&email={email}`

**Login**(Brock)

This logs a user in and returns their api key

`{url}/rest/public/api.php/Login/?username={username}&password={password}`

**Register User With Facebook**

This registers a new user through greenboard registration

`{url}/rest/public/api.php/RegisterUserWithFB/?username={username}&email={email}&fbid={fbid}`

**Login With Facebook**

This logs a user in and returns their api key

`{url}/rest/public/api.php/LoginWithFB/?fbid={fbid}`

### Endpoints In Development:
**GetTrailByArea**

Test
