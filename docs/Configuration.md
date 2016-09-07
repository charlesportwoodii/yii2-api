# Configuration

This document outlines all the configuration options for your application as defined in `config/config.yml`.

> Unless otherwise specified, all configuration options are required
> Note that if a option is not specified, the origin specified in `config/config-default.yml` will be used.

| Configuration Options    | Description                                |
|--------------------------|--------------------------------------------|
| `app:id`                 | The Yii2 application ID of your app        |
| `app:name`               | The Yii2 application name                  |
| `app:debug`              | Set to `true` to enable debugging mode     |
| `app:env`                | The Yii2 application environment           |
| `yii2:database:driver`   | The database driver you want to use. Currently supported values are `mysql` |
| `yii2:database:database` | The database name |
| `yii2:database:host`     | The hostname or IP address of the database |
| `yii2:database:username` | The username to access the database        |
| `yii2:database:password` | The password to access the database        |
| `yii2:redis:host`        | The hostname or IP address of the Redis service |
| `yii2:redis:port`        | The port number of the database            |
| `yii2:redis:database`    | The database number                        |
| `yii2:swiftmailer:host`  | The hostname or IP address of the SMTP server |
| `yii2:swiftmailer:username` | The username to access the SMTP server  |
| `yii2:swiftmailer:password` | The password to access the SMTP server  |
| `yii2:swiftmailer:port`   | The port number of the SMTP server        |
| `yii2:swiftmailer:encryption` | The encryption method to use. `tls` is prefered |
| `yii2:swiftmailer:realSend` | Whether or not the API should send emails or not. Set to `true` for production instances. During debugging or when running tests this should be to set to `false |
| `yii2:swiftmailer:origin_email` | The email address notifications should be sent from |
| `yii2:swiftmailer:origin_email_name` | The name to associate with the origin email|
| `yii2:user` | The class of your user identity model                   |
| `yii2:access_control:header` | The secret global access header                |
| `yii2:access_control:secret` | The secret value for the access header    |