<?php
namespace FW\Exts\Nic\Objects;
/**
 * ������ ��� ��
 * 
 */
class ContractIP extends Contract
{

	/**
	 * person
	 * ���, ������ ����� �������� � ������� ������� �� ���������� �����.
	 * ���� ����� ��������� ��������� �����, �����, ����� ���������� � �������.
	 * ������������ ����� - 64 �������.
	 * ������������ ����.
	 * ������������� ����.
	 * @var string
	 */
	public $person;

	/**
	 * person-r
	 * �������, ��� � �������� ������� �� ������� �����.
	 * ����� �������� ������ �������������� ������������ ��.
	 * ���� ����� ��������� ������� � ��������� �����, �����, ����� ���������� � �������.
	 * ������������ ����� - 64 �������.
	 * ������������ ����.
	 * ������������� ����.
	 * @var string
	 */
	public $personR;

	/**
	 * address-r
	 * ����� �������� ������� �� ������� �����.
	 * ����� ��������� ������� � ��������� �����, �����, ����� ���������� � �������.
	 * ����������� ����� - ��� �����, ������������ ����� - 256 ��������.
	 * �������������� ����. 
	 * ������������� ����.
	 * @var string
	 */
	public $addressR;

	/**
	 * p-addr
	 * ����� ��������������� �� ������� �����.
	 * ����� ��������� ������� � ��������� �����, �����, ����� ���������� � �������.
	 * ����������� ����� - ��� �����, ������������ ����� - 256 ��������.
	 * ������������� ����.
	 * @var string
	 */
	public $dAddr;

	/**
	 * code
	 * ��� ���������������.
	 * ���� ������ ��������� ����������������� ����� (��������� ����� - �����������).
	 * �������� 121200025218
	 * ������������ ����.
	 * ������������ ����.
	 * @var string
	 */
	public $code;

	/**
	 * passport
	 * ���������� ������ �������.
	 * ���� ����������� ��-������.
	 * ����� ��������� ������� � ��������� �����, �����, ����� ���������� � �������.
	 * ����������� ����� - ��� �����, ������������ ����� - 256 ��������.
	 * ������������ ����.
	 * ������������� ����.
	 * @var string
	 */
	public $passport;

	/**
	 * birth-date
	 * ���� �������� ������� � ������� DD.MM.YYYY (�����).
	 * ������������ ����.
	 * ������������ ����.
	 * @var string
	 */
	public $birthDate;

	function __construct() {
		$this->contractType = 'PRS';
	}
}

