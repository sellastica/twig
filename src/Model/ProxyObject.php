<?php
namespace Sellastica\Twig\Model;

abstract class ProxyObject extends TwigObject
{
	/** @var IProxable */
	protected $parent;


	/**
	 * @param IProxable $parent
	 */
	public function __construct(IProxable $parent)
	{
		$this->parent = $parent;
	}

	/**
	 * @return IProxable
	 */
	public function getParent(): IProxable
	{
		return $this->parent;
	}
}