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
 * Thrown when metadata could not be loaded for some reason.
 *
 * @since 1.0
 */
final class CouldNotLoadMetadata extends \RuntimeException
{
    // noop
}
