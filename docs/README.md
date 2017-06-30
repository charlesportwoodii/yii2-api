# Yii2 API Skeleton Documentation

This project was born out of the frustration of starting from scratch them writing new API's, and having to implement the same core logic over and over again. To reduce the overhead necessary to start writing a RESTful JSON API, this skeleton provides a foundation that can be built upon. This foundation provides solutions for the following common API problems:

- Authentication
- Authorization
- Rate Limiting
- CORS Headers
- Registration
- Password Changes
- 2 Factor Authentication
- Logging
- Localization & Translations

This framework follows all existing Yii2 conventions and configurations, which makes it simple to extend it and add new functionality.

## Installation

The following section outlines the steps necessary to install, run, and configure this API so that you can begin extending it. To get started, you first need to fork this project using either `dev-master` or a tagged release.

```
git clone https://github.com/charlesportwoodii/yii2-api project_dir
cd project_dir
```

### Docker Development

An environment can also be provisioned through Docker

```
docker-compose up
docker exec -it yii2api_php_1 /bin/bash -lc "cd /var/www && /root/.bin/composer install -ovn"
```

### Vagrant Development

Development is managed through the provided `Vagrant` box.

```
vagrant up
```

### Service Dependencies

For non-development environments, you'll need to install the following service dependencies:

- Redis Server
- MySQL
- Disque
- MailHog

> Disque and MailCatcher Docker containers can be pulled from the provided `Vagrantfile`, or `Docker`

### Configuring

Before you can start using the API, you'll need to configure it. This project provides a pre-configured `config/web.php` configuration file for the API, however you should avoid changing this file unless you want to add a new core component. The primary options are managed through `config/config.yml`. Your first step in configuring your application is to copy the template config yaml file.

```
cp config/config-default.yml config/config.yml
```

Be sure to configure each section before continuing. For more information see [Configuration.md](Configuration.md).

> For Docker development, reference `docker-compose.yml` for the hostnames to use.

### Migrations

Once you have configured your application, you can migrate the database.

```
./yii migrate/up
```

> Docker migrations can be run by calling the following command. Adjust the generated box name as necessary.
> ```
> docker exec -it yii2api_php_1 /bin/bash -lc "cd /var/www && ./yii migrate/up --interactive=0"
> ```

## Tests

All tests are managed through [Codeception](http://codeception.com/), which is installed as a `Composer` development dependency. The API is both fully unit tested and functional tested. All tests can be run by executing:

```
./vendor/bin/codecept run
```

> Tests within the docker container can be run by calling the following command.  Adjust the generated box name as necessary.
> ```
> docker exec -it yii2api_php_1 /bin/bash -lc "cd /var/www && ./vendor/bin/codecept run"
> ```

> Note the tests perform database and cache interactions, which include deleting data. It is not recommended to run the tests in any environment where you care about your data, as that data may be deleted.