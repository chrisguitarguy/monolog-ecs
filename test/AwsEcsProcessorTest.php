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

final class AwsEcsProcessorTest extends TestCase
{
    private $ecs, $processor;

    public function testProcessorLoadsTheEcsMetadataFromTheLoaderAndAddsItToExtra()
    {
        $this->ecs->expects($this->once())
            ->method('load')
            ->willReturn([
                'aws_ecs_test' => 1,
            ]);

        $record = call_user_func($this->processor, [
            'extra' => ['other' => 2],
        ]);

        $this->assertEquals([
            'other' => 2,
            'aws_ecs_test' => 1,
        ], $record['extra']);
    }

    public function testProcessorUsesAnInitialEmptyExtraArrayWhenExtraIsNotInTheRecord()
    {
        $this->ecs->expects($this->once())
            ->method('load')
            ->willReturn([
                'aws_ecs_test' => 1,
            ]);

        $record = call_user_func($this->processor, []);

        $this->assertArrayHasKey('extra', $record);
        $this->assertEquals([
            'aws_ecs_test' => 1,
        ], $record['extra']);
    }

    public function testProcessorEatsErrorsAndAddesAnErrorKeyToTheExtraArray()
    {
        $this->ecs->expects($this->once())
            ->method('load')
            ->willThrowException(new CouldNotLoadMetadata('oh noz'));

        $record = call_user_func($this->processor, [
            'extra' => [],
        ]);

        $this->assertEquals([
            'aws_ecs_error' => 'oh noz',
        ], $record['extra']);
    }

    protected function setUp(): void
    {
        $this->ecs = $this->createMock(EcsMetadata::class);
        $this->processor = new AwsEcsProcessor($this->ecs);
    }
}
