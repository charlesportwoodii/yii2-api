# API Conventions

To simplify development of API's, this skeleton implements several conventions:

## API Versioning

API versioning is implemented by creating a new folder in the `controllers/api` directory representing the version you want (`v1`, `v2` ... etc), then creating a new controller inside of it. For instance, to create the API endpoint `/api/v1/new`, we would create the following controller in `controllers/api/v1/NewController.php`.

```php
namespace app\controllers\api\v1;

use yrc\rest\Controller;
use Yii;

class NewController extends Controller {}
```

By extending `yrc\rest\Controller`, our new controller gets several features for free, namely:

- CORS headers
- HTTP Verb filtering based upon controller actions (see the "Actions" section)
- Rate Limiting for authenticated users

## RESTful Actions

Instead of writing actions inline in our controller, our controller should implement the Yii2 `actions` method, which maps new actions to a class that extends `yrc\rest\Action`. For instance to create a new API endpoint `/api/v1/new/example` we would implement the following `actions` method in our controller class.

```php
public function actions()
{
    return [
        'example' => 'app\actions\v1\ExampleAction'
    ];
}
```

All actions implemented in the `actions` method should extends `yrc\rest\Action`, and it provides a framework for coupling HTTP verbs to specify actions. Our example action would then be implemented as follows:

```php
namespace yrc\api\actions;

use yrc\rest\Action as RestAction;

use yii\web\HttpException;
use Yii;

class ExampleActions extends RestAction {}
```

Within our action we can then implement static methods of the HTTP verbs we want to handle. For instance to support `POST` requests, we would implement the following method inside our action.

```php
public static function post($params) {}
```

The method name should correlate to the HTTP verb you want to support inside the action. The following are all valid method names which correlate to the HTTP verb associate with your action.

```php
public static function post($params) {}
public static function delete($params) {}
public static function head($params) {}
public static function put($params) {}
public static function patch($params) {}
public static function options($params) {}
```

This powerful framework allows you to group API endpoints in a single action and easily separate logic based upon the HTTP verb requested.

## Authentication

API authentication is handled through the inclusion of an `authenticator` behavior. The bundled behavior includes a HMAC signature authentication method, which is vasty superior to many common API authentication methods. To use this authentication, simply specify it in your `authenticator` behavior and specify the actions you wish it to apply to.

```php
use yrc\filters\auth\HMACSignatureAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();

    $behaviors['authenticator'] = [
        'class'     => HMACSignatureAuth::className(),
        'only'      => ['refresh', 'authenticate', 'otp', 'reset_password'],
        'optional'  => ['authenticate', 'reset_password']
    ];

    return $behaviors;
}
```

> Note that `HMACSignatureAuth` is the default authenticator, and is strongly coupled to both the default `Authenticate` and `Refresh` API actions. If you desire to use a different authenticator class these Actions will still work, but will provide less value.