<?php
namespace Sellastica\Twig\Model;

use Sellastica\Core\Collection;
use Sellastica\Entity\Entity\EntityCollection;
use Sellastica\Entity\Entity\IEntity;
use Sellastica\Localization\Model\Localization;

class ProxyConverter
{
	/** @var array */
	private static $entityCache = [];


	/**
	 * @param \Sellastica\Entity\Entity\IEntity|\Sellastica\Twig\Model\IProxable|\Sellastica\Entity\Entity\EntityCollection $what
	 * @param string $proxyClass
	 * @param Localization|null $localization
	 * @return ArrayProxy|\Sellastica\Twig\Model\ProxyEntity|mixed
	 * @throws \InvalidArgumentException
	 */
	public static function convert($what, string $proxyClass = null, Localization $localization = null)
	{
		if ($what instanceof IEntity) {
			return self::convertEntity($what, $proxyClass, $localization);
		} elseif ($what instanceof EntityCollection) {
			return self::convertCollection($what);
		} elseif ($what instanceof IProxable) {
			return self::convertIProxable($what, $proxyClass, $localization);
		} elseif (is_object($what)) {
			throw new \InvalidArgumentException('Could not convert object ' . get_class($what));
		} else {
			throw new \InvalidArgumentException('Could not convert argument');
		}
	}

	/**
	 * @param IProxable $proxable
	 * @param string $proxyClass
	 * @param \Sellastica\Localization\Model\Localization|null $localization
	 * @return \Sellastica\Twig\Model\ProxyObject
	 * @throws \Exception
	 */
	private static function convertIProxable(
		IProxable $proxable,
		string $proxyClass,
		Localization $localization = null
	): ProxyObject
	{
		if (!class_exists($proxyClass)) {
			throw new \Exception("Class $proxyClass does not exist");
		}

		return new $proxyClass($proxable, $localization);
	}

	/**
	 * @param \Sellastica\Entity\Entity\IEntity $entity
	 * @param string $proxyClass
	 * @param \Sellastica\Localization\Model\Localization|null $localization
	 * @return \Sellastica\Twig\Model\ProxyEntity
	 * @throws \InvalidArgumentException
	 */
	private static function convertEntity(
		IEntity $entity,
		string $proxyClass,
		Localization $localization = null
	): ProxyEntity
	{
		if (!$entity instanceof IProxable) {
			throw new \InvalidArgumentException('Entity must implement IProxable');
		}

		if (!isset(self::$entityCache[get_class($entity)][$entity->getId()])) {
			self::$entityCache[get_class($entity)][$entity->getId()] = self::convertIProxable($entity, $proxyClass, $localization);
		}

		return self::$entityCache[get_class($entity)][$entity->getId()];
	}

	/**
	 * @param \Sellastica\Core\Collection $collection
	 * @param string $name
	 * @return ArrayProxy
	 */
	public static function convertCollection(Collection $collection, string $name = ''): ArrayProxy
	{
		return new ArrayProxy($name, $collection->toArray());
	}
}
