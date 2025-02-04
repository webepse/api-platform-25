# installation 

s'assurer d'avoir openssl sur son système d'exploitation
ensuite clone le projet GitHub: 


```
git clone https://github.com/webepse/api-platform-25.git
```
## installation des dépendances
```
composer i
```

### pour assetmapper
```
php bin/console asset-map:compile
```


## base de données
### aller voir le .env pour votre base de données 

sur Windows: 

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/apiplatform25?serverVersion=8.0.32&charset=utf8mb4"
```

sur MacOs: 
```
DATABASE_URL="mysql://root:root@127.0.0.1:8889/apiplatform25?serverVersion=8.0.32&charset=utf8mb4"
```

### création de la bdd
```
symfony console d:d:c
```
### envoyer les migrations
```
symfony console d:m:m
```
### envoyer les fixtures
```
symfony console d:f:l
```

## JWT 
s'assurer que le dossier config/jwt possède les clés __private.pem__ et __public.pem__
sinon :
```
php bin/console lexik:jwt:generate-keypair
```

## lancer le serveur 
```
symfony server:start
```

### si problème 
```
symfony server:stop
```
puis 
```
symfony server:start
```

ou 
```
symfony server:status
```