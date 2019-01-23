# Installing the project

## Cloning the project

```
git clone https://gitlab.com/slack_lpm/cel2-services.git
```

## Installing depencies

In the project root folder, launch the composer install with the following options:

```
composer install --no-dev --optimize-autoloader
```

## Editing configuration

The default configuration are located in the .env file at root folder of the project. You have to create an '.env.prod' file to override the environment variables which suit your setup.

### Editing elasticsearch instance URL

The communication with elasticsearch is managed by the elastica bundle. You can change the ES instance URL in the config/packages/fos_elastica.yaml file.

### Editing database credentials and mysql instance 

Configure rour database connections:

```
DATABASE_URL=mysql://db_username:db_paswword@db_server_host:db_server_port/db_name
```
### Editing the CORS policy

CORS feature is managed by nelmio CORS bundle (https://github.com/nelmio/NelmioCorsBundle). The configuration file is config/packages/nelmio_cors.yaml. You may want to edit the settings for the '^/api/' path.

## Creating the database

Run the sql script located in the 'sql' folder of the project.

## Dev: launching the app on the test server

You can do so by issuing the following command:

```
php bin/console server:start

```

Or, if the 80 port is already used:

```
php bin/console server:start *:8080

```

Stopping the server is done by issuing:

```
php bin/console server:stop

```

## Prod: Deploying on server


You can then follow the symfony manual depending on the server you want to deploy on: https://symfony.com/doc/current/deployment.html

