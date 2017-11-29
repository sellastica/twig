<?php
namespace Sellastica\Twig\Model;

use Sellastica\Entity\Entity\IEntity;

/**
 * Why do we use getter methods like getCameled_foo() except of getCameledFoo()?
 * The reason is, that in first case, method is callable with attribute cameled_foo only, and that is what we want
 * In the second option, attribute would be callable also with attribute cameledFoo - we dont want these ambiguities
 *
 * @method IEntity getParent()
 * @property IEntity $parent
 */
abstract class ProxyEntity extends ProxyObject
{
}