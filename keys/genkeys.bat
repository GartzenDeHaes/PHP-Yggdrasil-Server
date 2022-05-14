openssl genrsa -out yggdrasil-private-key.pem 512
openssl rsa -in yggdrasil-private-key.pem -outform PEM -pubout -out yggdrasil-public-key.pem
