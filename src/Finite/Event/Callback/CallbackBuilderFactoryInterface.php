<?php

declare(strict_types=1);

namespace Finite\Event\Callback;

use Finite\StateMachine\StateMachineInterface;

/**
 * Base interface for CallbackBuilder factories.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
interface CallbackBuilderFactoryInterface
{
    public function createBuilder(StateMachineInterface $stateMachine): CallbackBuilder;
}
