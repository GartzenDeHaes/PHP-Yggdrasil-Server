# PHP-Yggdrasil-Server
A PHP SQLITE Based  Minecraft Yggdrasil Authentication Server for Minecraft Like Client

Ported from [here](https://github.com/Erythrocyte3803/PHP-Yggdrasil-Server).

- Database is stored in data/db.sqlite.
- Generate the encription PEM files by installing OpenSSL and running the commands in keys/genkeys.bat.
- Unless you do some URL rewriting in the webserver, the endpoints are authserver/authentication, /sessionserver/session, etc.
- Manual registration page at /registration

TODO

- Testing.  Only /authenticate and registration have been tested with the SQLITE backend.
- Registraion API.
