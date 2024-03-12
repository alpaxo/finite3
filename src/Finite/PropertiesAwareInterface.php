<?php

declare(strict_types=1);

namespace Finite;

/**
 * Interface that all class that have properties must implement
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface PropertiesAwareInterface
{
    /**
     * Checks for a property existance
     */
    public function has(string $property): bool;

    /**
     * Returns property value
     */
    public function get(string $property, mixed $default = null): mixed;

    /**
     * Returns optional state properties.
     */
    public function getProperties(): array;
}
