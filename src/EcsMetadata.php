<?php declare(strict_types=1);

/*
 * This file is part of chrisguitarguy/monolog-ecs.
 *
 * Copyright (c) Christopher Davis <https://chrisguitarguy.com>. For full
 * license information see the LICENSE file distributed with this source code.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Chrisguitarguy\MonologEcs;

/**
 * A service that can load ECS metadata from somewhere.
 *
 * @since 1.0
 */
interface EcsMetadata
{
    /**
     * Load the ECS metadata.
     *
     * @throws CouldNotLoadMetadata if the metadata could not be loaded for any reason
     * @return an an array of metadata from ECS.
     */
    public function load() : array;
}
