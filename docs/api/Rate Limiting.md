# Rate Limiting

The API supports rate limiting for all authenticated API endpoints via the standard Yii2 API rate limiting. The default values are 150 requests every 900 seconds, and can by tweaked by adjusting the following values in `app/models/User`

```php
<?php

namespace app\models;

final class User extends \yrc\api\models\User
{
    private $rateLimit = 150;
    private $rateLimitWindow = 900;
}
```

### Headers

For authenticated requests, the following rate limit headers are sent

```
X-Rate-Limit-Limit: $rateLimit
X-Rate-Limit-Remaining: $rateLimit-n
X-Rate-Limit-Reset: <y_seconds>
```