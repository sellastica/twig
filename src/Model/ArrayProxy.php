<?php
namespace Sellastica\Twig\Model;

use Nette;
use Nette\Utils\Strings;

class ArrayProxy extends TwigObject implements \Iterator, \Countable, \ArrayAccess
{
	/** @var string */
	protected $shortName;
	/** @var int */
	protected $counter = 0;
	/** @var array */
	protected $items;


	/**
	 * @param string $shortName
	 * @param array $items
	 */
	public function __construct($shortName = '', $items = [])
	{
		$this->shortName = $shortName;
		$this->items = $items;
		$this->counter = 0;
	}

	public function createSubarray()
	{
		$args = func_get_args();
		if (!sizeof($args)) {
			throw new \InvalidArgumentException('ArrayProxy::createSubarray() needs array keys as parameter(s)');
		}

		$this->items = $this->createSubarrayLevel($this->items, $args);
	}

	/**
	 * @param $array
	 * @param array $args
	 * @param int $level
	 * @return mixed
	 * @throws \UnexpectedValueException
	 */
	private function createSubarrayLevel($array, array $args, int $level = 0)
	{
		if (sizeof($args)) {
			$current = array_shift($args);
			if (!is_array($array) && (!$array instanceof \ArrayAccess)) {
				//if something item on this level exists and is not array, we
				throw new \UnexpectedValueException(
					"Could not create subarray. Some item on level $level already exists and is not an array type"
				);
			}

			if (!isset($array[$current])) {
				$array[$current] = new self();
			}

			$this->createSubarrayLevel($array[$current], $args, ++$level);
		}

		return $array;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return sizeof($this->items);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @throws Nette\MemberAccessException
	 */
	public function offsetSet($offset, $value)
	{
		if (null === $offset) {
			$this->items[] = $value;
		} else {
			$this->items[$this->sanitizeOffset($offset)] = $value;
		}
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->items[$this->sanitizeOffset($offset)]);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->items[$this->sanitizeOffset($offset)]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed|null
	 */
	public function offsetGet($offset)
	{
		$item = $this->items[$this->sanitizeOffset($offset)] ?? null;
		return $item instanceof IProxable ? $item->toProxy() : $item;
	}

	public function rewind()
	{
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
		return isset($this->getKeys()[$this->counter]);
	}

	/**
	 * @return mixed
	 */
	public function current()
	{
		$item = $this->items[$this->key()];
		return $item instanceof IProxable ? $item->toProxy() : $item;
	}

	/**
	 * @return int
	 */
	public function key()
	{
		return $this->getKeys()[$this->counter];
	}

	/**
	 * @return mixed
	 */
	private function getKeys()
	{
		return array_keys($this->items);
	}

	/**
	 * @param $offset
	 * @return string
	 */
	private function sanitizeOffset($offset)
	{
		return is_string($offset) ? Strings::lower($offset) : $offset;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return $this->items;
	}

	/**
	 * @return string
	 */
	public function getShortName()
	{
		return $this->shortName;
	}

	/**
	 * @return array
	 */
	public function getAllowedProperties()
	{
		return [];
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return '[' . ($this->shortName ?: 'array') . ']';
	}

	public function unique()
	{
		$this->items = array_unique($this->items);
	}
}