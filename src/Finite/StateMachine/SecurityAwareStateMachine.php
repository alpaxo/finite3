<?php

declare(strict_types=1);

namespace Finite\StateMachine;

use Finite\Transition\TransitionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Security Aware state machine.
 * Use the Symfony Security Component and ACL.
 *
 * Need an ACL implementation available, Doctrine DBAL by default.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class SecurityAwareStateMachine extends StateMachine
{
    protected AuthorizationCheckerInterface $authorizationChecker;

    public function setSecurityContext(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     * @throws \Finite\Exception\TransitionException
     */
    public function can(TransitionInterface|string $transition, array $parameters = []): bool
    {
        $transition = $transition instanceof TransitionInterface ? $transition : $this->getTransition($transition);

        if (!$this->authorizationChecker->isGranted($transition->getName(), $this->getObject())) {
            return false;
        }

        return parent::can($transition, $parameters);
    }
}
