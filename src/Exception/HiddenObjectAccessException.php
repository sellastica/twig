<?php
namespace Sellastica\Twig\Exception;

/**
 * Thrown when customer tries to access to hidden object in the $context (instance of Twig\ContextWrapper)
 */
class HiddenObjectAccessException extends \Twig_Error_Runtime
{
}
