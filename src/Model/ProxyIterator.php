<?php
namespace Sellastica\Twig\Model;

use Package\Twig\ConfigurationBuilder;

class ProxyIterator extends TwigObject implements \Iterator
{
	/** @var ConfigurationBuilder */
	private $configurationBuilder;
	/** @var ConfigurationBuilder */
	private $defaultConfigurationBuilder;
	/** @var array */
	protected $items;
	/** @var int */
	protected $counter = 0;
	/** @var callable */
	private $data;

	/**
	 * @param callable $data
	 * @param ConfigurationBuilder $configurationBuilder
	 */
	public function __construct(callable $data, ConfigurationBuilder $configurationBuilder)
	{
		$this->configurationBuilder = $configurationBuilder;
		$this->defaultConfigurationBuilder = clone $configurationBuilder;
		$this->data = $data;
	}

	/**
	 * @return array
	 */
	private function getItems()
	{
		if (!isset($this->items)) {
			$this->items = call_user_func_array($this->data, [$this->configurationBuilder->create()]);
			$this->setDefaultConfigurationBuilder();
		}

		return $this->items;
	}

	/**
	 * Sets default configuration builder as an active configuration builder
	 */
	public function setDefaultConfigurationBuilder()
	{
		$this->configurationBuilder = clone $this->defaultConfigurationBuilder;
	}

	/**
	 * @param int $itemsPerPage
	 * @return \Sellastica\UI\Pagination\Pagination
	 */
	public function createPagination($itemsPerPage)
	{
		//paginate tag is the first tag before outputing items, so we reset configuration, just for sure
		$this->setDefaultConfigurationBuilder();
		return $this->configurationBuilder->createPagination($itemsPerPage);
	}

	/**
	 * @param mixed $sort
	 */
	public function setSort($sort)
	{
		if (null !== $sort) {
			$this->configurationBuilder->addUserArg([ConfigurationBuilder::SORT => $sort]);
		} else {
			$this->configurationBuilder->removeUserArg(ConfigurationBuilder::SORT);
		}
	}

	/**
	 * @param int $limit
	 * @throws \Sellastica\Twig\Exception\LogicException
	 */
	public function setLimit(?int $limit)
	{
		if ($this->configurationBuilder->hasPagination()) {
			throw new \Sellastica\Twig\Exception\LogicException('You cannot set pagination and limit simultaneously');
		}

		if (isset($limit)) {
			$this->configurationBuilder->addUserArg([ConfigurationBuilder::LIMIT => (int)$limit]);
		} else {
			$this->configurationBuilder->removeUserArg(ConfigurationBuilder::LIMIT);
		}
	}

	/**
	 * @param $order
	 */
	public function setOrder($order)
	{
		if (null !== $order) {
			$this->configurationBuilder->addUserArg([ConfigurationBuilder::ORDER => $order]);
		} else {
			$this->configurationBuilder->removeUserArg(ConfigurationBuilder::ORDER);
		}
	}

	public function rewind()
	{
		$this->items = null;
		$this->counter = 0;
	}

	public function next()
	{
		$this->counter++;
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		return array_key_exists($this->counter, $this->getItems());
	}

	/**
	 * @return mixed
	 */
	public function current()
	{
		return $this->getItems()[$this->counter];
	}

	/**
	 * @return int
	 */
	public function key()
	{
		return $this->counter;
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getShortName();
	}

	/**
	 * @return string
	 */
	public function getShortName(): string
	{
		return '[array]';
	}

	/**
	 * @return array
	 */
	public function getAllowedProperties(): array
	{
		return [
			'sorter',
		];
	}
}