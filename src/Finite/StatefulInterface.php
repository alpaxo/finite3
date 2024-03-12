<?php

declare(strict_types=1);

namespace Finite;

/**
 * Implementing this interface make an object Stateful and
 * able to be handled by the state machine.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface StatefulInterface
{
    public function getFiniteState(): string;

    public function setFiniteState(string $state);
}
