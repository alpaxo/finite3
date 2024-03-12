<?php

namespace Finite\State\Accessor;

use Finite\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException as SymfonyNoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Property path implementation of state accessor.
 *
 * @author Yohan Giarelli <yohan@frequence-web.fr>
 */
class PropertyPathStateAccessor implements StateAccessorInterface
{
    private string $propertyPath;

    private PropertyAccessor|PropertyAccessorInterface $propertyAccessor;

    public function __construct(string $propertyPath = 'finiteState', PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyPath = $propertyPath;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getState(object $object): ?string
    {
        try {
            return $this->propertyAccessor->getValue($object, $this->propertyPath) ?: null;
        } catch (SymfonyNoSuchPropertyException $e) {
            throw new NoSuchPropertyException(
                sprintf(
                    'Property path "%s" on object "%s" does not exist.',
                    $this->propertyPath,
                    get_class($object)
                ), $e->getCode(), $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setState(object $object, string $value): void
    {
        try {
            $this->propertyAccessor->setValue($object, $this->propertyPath, $value);
        } catch (SymfonyNoSuchPropertyException $e) {
            throw new NoSuchPropertyException(
                sprintf(
                    'Property path "%s" on object "%s" does not exist.',
                    $this->propertyPath,
                    get_class($object)
                ), $e->getCode(), $e
            );
        }
    }
}
