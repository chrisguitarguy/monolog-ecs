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

/**
 * Loads ECS metadata from a file and makes it available in the `extra` key of
 * the log record.
 *
 * See https://docs.aws.amazon.com/AmazonECS/latest/developerguide/container-metadata.html
 *
 * @since 1.0
 * @api
 */
final class AwsEcsProcessor
{
    private $loader;

    public function __construct(EcsMetadata $loader)
    {
        $this->loader = $loader;
    }

    public static function create() : self
    {
        return new self(new FileEcsMetadata());
    }

    public function __invoke(array $record) : array
    {
        $record['extra'] = array_merge($record['extra'] ?? [], $this->getEcsMetadata());
        return $record;
    }

    private function getEcsMetadata() : array
    {
        try {
            return $this->loader->load();
        } catch (CouldNotLoadMetadata $e) {
            return [
                'aws_ecs_error' => $e->getMessage(),
            ];
        }
    }
}
