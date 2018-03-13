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
 * A EcsMedata implementation that wraps another and caches the result.
 *
 * @since 1.0
 */
final class CachingEcsMetadata implements EcsMetadata
{
    /**
     * The wrapped metadata service
     *
     * @var EcsMetadata
     */
    private $wrapped;

    /**
     * The actual cache
     *
     * @var array|null
     */
    private $cache = null;

    public function __construct(EcsMetadata $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function load() : array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        $meta = $this->wrapped->load();
        if ($this->isCacheable($meta)) {
            $this->cache = $meta;
        }

        return $meta;
    }

    /**
     * We only really want to cache metadata files that are in a "ready" state.
     *
     * Aws sets a `MetadataFileStatus` in the container metadata file if it's
     * ready and complete (usually happens about ~1s after container startup).
     * If the file is not ready, we don't cache.
     *
     * This isn't a huge deal for a web request where caching an unready file only
     * matter for a single request, but for a long running process, it means we might
     * not have a lot of info available that we desire.
     *
     * @param $meta The metadata from the wrapped EcsMetadata
     * @return True if the file is ready and cacheable
     */
    private function isCacheable(array $meta)
    {
        $status = $meta['aws_ecs_metadatafilestatus'] ?? false;

        return $status && strtolower((string) $status) === 'ready';
    }
}
