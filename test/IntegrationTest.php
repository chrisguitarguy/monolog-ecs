<?php declare(strict_types=1);

/*
 * This file is part of chrisguitarguy/monolog-ecs
 *
 * Copyright (c) Christopher Davis <https://chrisguitarguy.com>. For full
 * license information see the LICENSE file distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Chrisguitarguy\MonologEcs;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Monolog\Formatter\LineFormatter;

final class IntegrationTest extends TestCase
{
    private $logger, $handler;

    public function testProcessorAddsEcsValuesForFormattingWithTheMessage()
    {
        $this->logger->info('hello, world');

        $this->assertTrue($this->handler->hasRecordThatPasses(function (array $record) {
            $msg = $record['formatted'] ?? '';
            print_r($record);
            return stripos($msg, 'arn:aws:ecs:us-west-2:012345678910:task/2b88376d-aba3-4950-9ddf-bcb0f388a40c') !== false
                   && stripos($msg, 'arn:aws:ecs:us-west-2:012345678910:container-instance/1f73d099-b914-411c-a9ff-81633b7741dd') !== false;
        }, Logger::INFO));
    }

    protected function setUp(): void
    {
        $this->handler = new TestHandler(Logger::DEBUG);
        $this->handler->setFormatter(new LineFormatter(
            '%level_name%: %message% (%extra.aws_ecs_taskarn%, %extra.aws_ecs_containerinstancearn%)'
        ));
        $this->logger = new Logger('test_aws_ecs');
        $this->logger->pushHandler($this->handler);
        $this->logger->pushProcessor(AwsEcsProcessor::create());
        putenv(sprintf('%s=%s', FileEcsMetadata::DEFAULT_ENVNAME, __DIR__.'/Fixtures/integration.json'));
    }
}
