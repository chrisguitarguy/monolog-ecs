# Monolog ECS

A set of monolog processors that add some additional context to Monolog's
`extra` key of the `$record`.

More pratically speaking, these allow users to put information in logs about
the servers or ECS tasks from which the log messages were generated.

The values available are pulled from the [Container Metadata File](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/container-metadata.html)
and are all the file keys prefixed with `aws_ecs_`. See below for some examples

## Installation

```
composer require chrisguitarguy/monolog-ecs
```

## Usage

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Chrisguitarguy\MonologEcs\AwsEcsProcessor;

$logger = new Logger('myapp');
// add the processor, `::create()` uses the default dependencies that loads
// metadata from the file and caches it based on the file status
$logger->pushProcessor(AwsEcsProcessor::create());

// now use the various `%extra.aws_ecs_*` values as desired. These are all
// keys from the metadata file lowercased and prefixed with `aws_ecs_`
$handler = new StreamHandler('php://stdout');
$handler->setFormatter(new LineFormatter(
  '[%datetime%] %channel%.%level_name%: %message% (%extra.aws_ecs_taskarn%, %extra.aws_ecs_containerinstancearn%)'
));

$logger->pushHandler($handler);
```

The above example will include the ECS task's ARN as well as the ARN of the
instance on which is the task is running.
