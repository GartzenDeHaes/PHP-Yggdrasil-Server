# PHP-Yggdrasil-Server
A PHP SQLITE Based  Minecraft Yggdrasil Authentication Server for Minecraft Like Client

Ported from [here](https://github.com/Erythrocyte3803/PHP-Yggdrasil-Server).

- Database is stored in data/db.sqlite.
- Generate the encription PEM files by installing OpenSSL and running the commands in keys/genkeys.bat.
- Unless you do some URL rewriting in the webserver, the endpoints are authserver/authentication, /sessionserver/session, etc.
- Manual registration page at /registration

API (TODO)

- /authserver/authenticate
- /authserver/invalidate
- /authserver/motd
- /authserver/refresh
- /authserver/registration
- /authserver/registration_test (test web page)
- /authserver/signout (TODO)
- /authserver/validate

-  /sessionserver/hasJoined
-  /sessionserver/join
-  /sessionserver/profile
-  /sessionserver/serverlist
-  /sessionserver/serveronline
-  /sessionserver/session (TODO)
-  /servicelist.php
