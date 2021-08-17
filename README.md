
## recrypt-coding-test

Clone project, run composer install, run migrations.

Test with the following routes in Postman:

#### To store data:

http://localhost:8000/api/store-data/1

- send `value` in post request
- send encryption key as bearer token in authorization header

 



#### To retrieve data:
http://localhost:8000/api/get-data/1
- send decryption key as bearer token in authorization header
