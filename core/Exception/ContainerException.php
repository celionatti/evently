<?php

declare(strict_types=1);

namespace Trees\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Trees\Exception\TreesException;

/**
 * =======================================
 * ***************************************
 * ====== Container Exception Class ======
 * ***************************************
 * =======================================
 */

class ContainerException extends TreesException implements NotFoundExceptionInterface
{

}