<?php declare(strict_types=1);

/*
 * This file is part of chrisguitarguy/monolog-aws-processors.
 *
 * Copyright (c) Christopher Davis <https://chrisguitarguy.com>. For full
 * license information see the LICENSE file distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Chrisguitarguy\MonologEcs;

/**
 * Loads ECS metadata from a file. The filename is retrieved from the
 * environment variable passed into the constructor.
 *
 * See https://docs.aws.amazon.com/AmazonECS/latest/developerguide/container-metadata.html
 *
 * @since 1.0
 */
final class FileEcsMetadata implements EcsMetadata
{
    const DEFAULT_ENVNAME = 'ECS_CONTAINER_METADATA_FILE';

    /**
     * The environment variable that contains the metadata file name
     *
     * @var string
     */
    private $envname;

    public function __construct(?string $envname=null)
    {
        $this->envname = $envname ?: self::DEFAULT_ENVNAME;
    }

    /**
     * {@inheritdoc}
     */
    public function load() : array
    {
        $fn = getenv($this->envname);
        if (!$fn) {
            throw self::error(sprintf('No %s key in the environment', $this->envname));
        }

        $rawmeta = @file_get_contents($fn);
        if (false === $rawmeta) {
            $err = error_get_last();
            error_clear_last();
            throw self::error(sprintf(
                'could not load metadata file %s: %s',
                $fn,
                $err['message'] ?? 'unknown error'
            ));
        }

        $meta = json_decode($rawmeta, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw self::error(sprintf(
                'could not decode ecs metadata file: %s',
                json_last_error_msg()
            ));
        }

        return $this->normalize($meta);
    }

    /**
     * Adds an `aws_ecs_` prefix to each value in the file as well as makes sure that
     * each key is lowercased for consistency.
     *
     * For example the metadata file contains a key named `TaskARN`, it can be
     * accessed in your formatters with `%extra.aws_ecs_taskarn%`.
     *
     * @param $meta The raw metadata from the file.
     * @return a normalized metadata array.
     */
    private function normalize(array $meta) : array
    {
        foreach ($meta as $key => $val) {
            $out[self::key($key)] = $val;
        }

        return $out;
    }

    private static function key(string $in) : string
    {
        return 'aws_ecs_'.strtolower($in);
    }

    private static function error(string $message) : CouldNotLoadMetadata
    {
        return new CouldNotLoadMetadata($message);
    }
}
