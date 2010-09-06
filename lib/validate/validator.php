<?php
namespace FW\Validate;

abstract class Validator extends \FW\Object {
	public abstract function validate($value);
}