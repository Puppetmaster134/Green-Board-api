# Green-Board-api
## Repository for the GreenBoard API

All endpoints must have a valid api key appended to the url as a GET parameter

example: {server-url}/rest/public/api.php/{endpoint}/{endpoint-params}?api_key=abc123
### Endpoints Currently Available:
**GetTrailById**

This gets a json-encoded trail object by the trail's id in the database. Typically used for debugging.

{url}/rest/public/api.php/GetTrailById/{id}

**WriteTrailToDB**
This writes a new trail to the database

{url}/rest/public/api.php/GetTrailById/?lat={lat}&lng={lng}&trailObj={trailObj}

### Endpoints In Development:
**GetTrailByArea**
