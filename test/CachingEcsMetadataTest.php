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

final class CachingEcsMetadataTest extends TestCase
{
    private $wrapped, $ecs;

    public function testWrappedMetadataIsOnlyCalledOnceWhenTheMetadataIsCacheable()
    {
        $this->wrapped->expects($this->once())
            ->method('load')
            ->willReturn([
                'aws_ecs_metadatafilestatus' => 'READY',
                'aws_ecs_taskarn' => 'arn:aws:ecs:us-east-1:1112223333:task/c5cba4eb-5dad-405e-96db-71ef8eefe6a8',
            ]);

        // make sure we only call the wrapped thing once!
        $this->ecs->load();
        $result = $this->ecs->load();

        $this->assertEquals([
            'aws_ecs_metadatafilestatus' => 'READY',
            'aws_ecs_taskarn' => 'arn:aws:ecs:us-east-1:1112223333:task/c5cba4eb-5dad-405e-96db-71ef8eefe6a8',
        ], $result);
    }

    public function testWrappedMetadataDoesNotCacheWhenTheMetadataFileStatusIsMissing()
    {
        $this->wrapped->expects($this->exactly(2))
            ->method('load')
            ->willReturn([
                'aws_ecs_taskarn' => 'arn:aws:ecs:us-east-1:1112223333:task/c5cba4eb-5dad-405e-96db-71ef8eefe6a8',
            ]);

        $this->ecs->load();
        $result = $this->ecs->load();

        $this->assertEquals([
            'aws_ecs_taskarn' => 'arn:aws:ecs:us-east-1:1112223333:task/c5cba4eb-5dad-405e-96db-71ef8eefe6a8',
        ], $result);
    }

    public function testWrappedMetadataDoesNotCacheWhenTheFileStatusIsNotReady()
    {
        $this->wrapped->expects($this->exactly(2))
            ->method('load')
            ->willReturn([
                'aws_ecs_metadatafilestatus' => 'notready',
                'aws_ecs_taskarn' => 'arn:aws:ecs:us-east-1:1112223333:task/c5cba4eb-5dad-405e-96db-71ef8eefe6a8',
            ]);

        $this->ecs->load();
        $result = $this->ecs->load();

        $this->assertEquals([
            'aws_ecs_metadatafilestatus' => 'notready',
            'aws_ecs_taskarn' => 'arn:aws:ecs:us-east-1:1112223333:task/c5cba4eb-5dad-405e-96db-71ef8eefe6a8',
        ], $result);
    }

    protected function setUp(): void
    {
        $this->wrapped = $this->createMock(EcsMetadata::class);
        $this->ecs = new CachingEcsMetadata($this->wrapped);
    }
}
