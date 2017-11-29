<?php
namespace Sellastica\Twig\Model;

use Nette;
use Sellastica\Utils\Strings;

abstract class TwigObject
{
	const METHOD_PREFIX = 'get';
	/** @var Nette\Reflection\ClassType */
	protected $reflection;


	/**
	 * Returns attribute name used in template, e.g. getTitle() => title
	 * @param string $method
	 * @param bool $class If false, it returns title. If true, it returns product.title
	 * @return string
	 */
	protected function getAttributeName($method, $class = false)
	{
		$attribute = strtolower(Strings::removeFromBeginning($method, self::METHOD_PREFIX));
		return true === $class ? $this->getShortName() . '.' . $attribute : $attribute;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getShortName();
	}

	/**
	 * @param string $var
	 * @param mixed $val
	 * @throws \Exception
	 */
	public function __set($var, $val)
	{
		if ($var === '') {
			throw new Nette\MemberAccessException(sprintf('Cannot overwrite readable object %s.', $var, $this->getShortName()));
		}

		throw new Nette\MemberAccessException(sprintf('Cannot set attribute %s to readable object %s.', $var, $this->getShortName()));
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @throws Nette\MemberAccessException
	 */
	public function __get($name)
	{
		$method = self::METHOD_PREFIX . $name;
		return call_user_func([$this, $method]);
	}

	/**
	 * Twig engine tries at first to call __isset method. If returns true, it calls __get
	 * If false, it calls __call
	 * @param string $name
	 * @return bool
	 * @throws Nette\MemberAccessException
	 */
	public function __isset($name)
	{
		if (method_exists($this, self::METHOD_PREFIX . $name)) {
			return true;
		} elseif ($this->isMagicMethod($name) && method_exists($this, $name)) {
			return true;
		}

		return false;
	}

	/**
	 * Call to undefined method
	 * @param string $name
	 * @param mixed $args
	 * @return mixed
	 * @throws Nette\MemberAccessException
	 */
	public function __call($name, $args)
	{
		$this->throwMemberAccessException($name);
	}

	/**
	 * @param string $name
	 * @return NULL|string
	 */
	private function getHint($name)
	{
		return Nette\Utils\ObjectMixin::getSuggestion($this->getAllowedProperties(), $name);
	}

	/**
	 *
	 * @param string $method
	 * @throws Nette\MemberAccessException
	 */
	private function throwMemberAccessException($method)
	{
		$hint = $this->getHint($method);
		throw new Nette\MemberAccessException(
			sprintf('Unknown property "%s" in "%s" object', $method, $this->getShortName()) . ($hint ? ", did you mean $hint?" : '.')
		);
	}

	/**
	 * Call to undefined static method.
	 * @param string $name method name
	 * @param array $args
	 * @return void
	 * @throws Nette\MemberAccessException
	 */
	public static function __callStatic($name, $args)
	{
		throw new Nette\MemberAccessException(sprintf('Cannot call unknown attribute %s on object.', $name));
	}

	/**
	 * @param string $name
	 * @throws Nette\MemberAccessException
	 */
	public function __unset($name)
	{
		throw new Nette\MemberAccessException(sprintf('Cannot unset attribute %s on object %s.', $name, $this->getShortName()));
	}

	/**
	 * Access to reflection.
	 * @return Nette\Reflection\ClassType|\ReflectionClass
	 */
	protected function getReflection()
	{
		if (!isset($this->reflection)) {
			$this->reflection = new Nette\Reflection\ClassType(get_called_class());
		}

		return $this->reflection;
	}

	/**
	 * Not all magic method, but only those used in twig objects and callable from outside
	 * @visibility public Method is called also from Package\Twig\SecurityPolicy object
	 * @param string $name
	 * @return bool
	 */
	public function isMagicMethod(string $name): bool
	{
		return in_array(strtolower($name), ['__tostring']);
	}

	/**
	 * @return string
	 */
	abstract public function getShortName();

	/**
	 * @return array
	 */
	abstract public function getAllowedProperties();
}
