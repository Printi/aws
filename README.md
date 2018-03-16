Printi - AWS
==========================

Printi Aws is common aws service for Printi platform.

## Installing AWS as Bundle

The recommended way to install ApiClient is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of AWS:

```bash
php composer.phar require printi/aws
```

You can then later update notify using composer:

 ```bash
composer.phar update printi/aws
```

## User Guide


Basic notify configuration:

For eg:
```yaml
printi_aws:
    s3:
        orders_bucket:
            bucket: alpha-upload-dev

    sqs:
        omega_occurrence:
            enable: true
            queue_name: omega-item-occurrence-dev
            queue_url: arn:aws:sqs:sa-east-1:773571409125:omega-item-occurrence-dev
            wait_time_seconds: 1
    sns:
        omega_occurrence:
            enable: true
            topic_arn: arn:aws:sns:sa-east-1:773571409125:omega-item-occurrence-dev
        omega_status_change:
            enable: true
            topic_arn: arn:aws:sns:sa-east-1:773571409125:omega-item-status-change-dev
        om2_item_import_fail:
            enable: true
            topic_arn: arn:aws:sns:sa-east-1:773571409125:om2-item-import-fail-dev
        om2_invalid_item_import:
            enable: true
            topic_arn: arn:aws:sns:sa-east-1:773571409125:om2-invalid-item-import-dev
        alpha_message:
            enable: true
            topic_arn: arn:aws:sns:sa-east-1:773571409125:om2-invalid-item-import-dev```                        


## How to use

We can inject Notify as a service into our application.

for eg:
```php

namespace App;
use Printi\AwsBundle\Sns;

class HelloClass {

    private $snsClient;
    
    public function __construct(Sns $sns)
    {
        $this->snsClient = $sns;
    }
    
    public function onTransitionUpdate()
    {
        $message = [
            "order_item_id" => 11111,
            "transition"    => 'prepress_reject',
            "reference"     => null,
            "status_id"     => 50,
            "version"       => 2,
        ];
        $this->snsClient->publish('alpha_message', $message);
    }
}
```