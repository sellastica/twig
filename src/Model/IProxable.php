<?php
namespace Sellastica\Twig\Model;

interface IProxable
{
	/**
	 * @return ProxyObject $entity
	 */
	function toProxy();
}
