# PkRouter C.R.U.D. Example

This example explores all the features of PkRouter, but is not intended as production code. PkRouter does not implement any security features that would be required to make a system like this safe in the real world.

_Please be sure `./Examples/crud/data/store.json` is writable_

### This example covers

* Extending PkRoutesConfig (MyRoutes.php)
* Creating Middleware (./src/Middleware/)
* Passing data between Middleware
* Using Dynamic Properties
* Extending PkRequest to accept XML (CustomRequest.php)
* Extending PkResponse to send XML (CustomResponse.php)
* Custom Log function (./public/index.php)
* Using route specific 'meta' data 
* Sending custom response headers
* Listening for custom request headers

## How to run this example

```bash
# From ./Examples/crud
composer install
cd public
php -S localhost:8080
```

## Curl Commands

### Read All

> 💡 XML is enabled in ./src/Router/CustomResponse.php

```bash
curl "http://localhost:8080/storage/"
curl "http://localhost:8080/storage/" -H 'Accept: application/xml'
```

### Read One

```bash
curl "http://localhost:8080/storage/abc123/"
curl "http://localhost:8080/storage/abc123/" -H 'Accept: application/xml'

```

### Create

> 💡 XML is enabled in ./src/Router/CustomRequest.php

```bash
curl -X "POST" "http://localhost:8080/storage/" \
     -H 'Authorization: EXAMPLE_KEY' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{"first":"Rick","last":"Sanchez"}'

curl -X "POST" "http://localhost:8080/storage/" \
     -H 'Authorization: EXAMPLE_KEY' \
     -H 'Content-Type: application/xml' \
     -d $'<root><first>Jerry</first><last>Garcia</last></root>'
```

### Update — Replace entire object

```bash
curl -X "POST" "http://localhost:8080/storage/abc123/" \
     -H 'Authorization: EXAMPLE_KEY' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{"first":"Jane","last":"Johnson"}'
```

### Patch — Update 'first', add 'phone' and 'email'

```bash
curl -X "PATCH" "http://localhost:8080/storage/abc124/" \
     -H 'Authorization: EXAMPLE_KEY' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{"first":"Jenny","phone":"888-867-5309","email":"jenny@domain.com"}'
```

### Patch — Remove 'email' with null

```bash
curl -X "PATCH" "http://localhost:8080/storage/abc124/" \
     -H 'Authorization: EXAMPLE_KEY' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{"email":null}'
```

### Delete

```bash
curl -X "DELETE" "http://localhost:8080/storage/abc125/" -H 'Authorization: EXAMPLE_KEY'
```

## Bad Requests

### Requesting a KEY that does not exist

```bash
curl "http://localhost:8080/storage/invalid-key/"
```

### Requesting a route that does not exist

```bash
curl "http://localhost:8080/this-is-404/"
```

### Using a method that is not allowed on the Route

The only methods allowed on this route are GET and POST

```bash
curl -X "PUT" "http://localhost:8080/storage/"
```

### Unauthorized

```bash
curl -X "POST" "http://localhost:8080/storage/" \
     -H 'Authorization: BAD_KEY' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{"first":"Rick","last":"Sanchez"}'
```
### Bad Data

```bash
curl -X "POST" "http://localhost:8080/storage/" \
     -H 'Authorization: EXAMPLE_KEY' \
     -H 'Content-Type: application/json; charset=utf-8' \
     -d $'{"first":"Rick","last":"Sanch--'
```
