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

final class FileEcsMetadataTest extends TestCase
{
    const ENVVAR = 'CGG_FILE_ECS_METADATA_TEST';

    private $ecs;

    public function testMissingEnvironmentVariableCausesError()
    {
        $this->expectLoadError('in the environment');

        $this->ecs->load();
    }

    public function testUnreadableMetadataFileCausesError()
    {
        $this->expectLoadError('could not load metadata file');
        $this->withEnvVar(__DIR__.'/does/not/exist/at/all.json');

        $this->ecs->load();
    }

    public function testFileWithBadJsonCausesError()
    {
        $this->expectLoadError('could not decode ecs metadata file');
        $this->withEnvVar(__DIR__.'/Fixtures/bad.json');

        $this->ecs->load();
    }

    public function testValidFileReturnsNormalizedMetadata()
    {
        $this->withEnvVar(__DIR__.'/Fixtures/good.json');

        $result = $this->ecs->load();

        $this->assertEquals([
            'aws_ecs_one' => 'example',
            'aws_ecs_two' => 'example2',
        ], $result);
    }

    protected function assertPreConditions(): void
    {
        $this->assertFalse(getenv(self::ENVVAR));
    }

    protected function setUp(): void
    {
        $this->ecs = new FileEcsMetadata(self::ENVVAR);
        // start each test with the environment variable unset
        putenv(self::ENVVAR);
    }

    private function withEnvVar(string $value)
    {
        putenv(sprintf('%s=%s', self::ENVVAR, $value));
    }

    private function expectLoadError(string $message)
    {
        $this->expectException(CouldNotLoadMetadata::class);
        $this->expectExceptionMessage($message);
    }
}
