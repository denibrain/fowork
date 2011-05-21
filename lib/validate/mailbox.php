<?php
namespace FW\Validate;

class MailBox extends Validator {
	private $domainValidator;
	private $nameValidator;

	function __construct(
		$domainValidator = false,
		$nameValidator = false
	) {
		$this->domainValidator = $domainValidator ? $domainValidator : new Domain();
		$this->nameValidator = $nameValidator ? $nameValidator : new Mask('/^[a-z0-9](?:[._-]?[a-z0-9])*$/i');
	}

	function validate($value) {
		if (false === ($pos = strpos($value, '@')))
			throw new EValidate('Mailbox.part');
		try {
			$this->domainValidator->validate(substr($value, $pos + 1));
			$this->nameValidator->validate(substr($value, 0, $pos));
		}
		catch(EValidate $e) {
			throw new EValidate("Mail.domain/$e->code");
		}
	}
}