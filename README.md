# Green-Board-api
## Repository for the GreenBoard API

Endpoints containing {api_key} must have a valid api key appended to the url as a GET parameter (Try abc123 for an api key)

example: `http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/{endpoint}/?{endpoint-params}`

### Endpoints Currently Available:
**GetTrailById**

This gets a json-encoded trail object by the trail's id in the database. Typically used for debugging.

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/GetTrailById/?id={id}&key={api_key}`

**Register User**

This registers a new user through greenboard registration

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/RegisterUser/?username={username}&password={password}&email={email}`

**Login**(Brock)

This logs a user in and returns their api key

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/Login/?username={username}&password={password}`

Example: http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/Login/?username=Brian&password=securepass

**Register User With Facebook**

This registers a new user through greenboard registration

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/RegisterUserWithFB/?username={username}&email={email}&fbid={fbid}`

**Login With Facebook**

This logs a user in and returns their api key

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/LoginWithFB/?fbid={fbid}`

**WriteTrailToDB**

This writes a new trail to the database

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/WriteTrailToDB/?trailName={trailName}lat={lat}&lng={lng}&trailObj={trailObj}&key={api_key}`

**Get Trail In Area**

This returns a list of trails in a square-shaped region between (minLng,minLat) and (maxLng,maxLat)

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/GetTrailInArea/?minLat={minimum_latitude}&minLng={minimum_longitude}&maxLat={maximum_latitude}&maxLng={maximum_longitude}&key={api_key}`

**PostComment**

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/PostComment/?body={comment}&trailId={trailId}&key={api_key}`

Example: http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/PostComment/?commentBody=This%20is%20a%20comment&trailId=25&key=abc123

**RetrieveCommentsByTrailId**

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/RetrieveCommentsByTrailId/?trailId={trailId}&key={api_key}`

**RetrieveCommentsByTrailName**

`http://greenboard-env.us-west-2.elasticbeanstalk.com/api.php/RetrieveCommentsByTrailName/?trailName={trailName}&key={api_key}`


