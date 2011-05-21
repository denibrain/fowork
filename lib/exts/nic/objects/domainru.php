<?php
namespace FW\Exts\Nic\Objects;
/**
 * Description of contact
 *
 */
class DomainRU extends Domain {

	/**
	 * �������� ������ � ������������ ��������� ������� �� ���������� �����.
	 * ��������� ����� Whois-������.
	 * ���� ����� ��������� ��������� �����, �����, ����� ���������� � �������.
	 * ������������ ������ ���� - 300 ��������. �������������� ����. ������������� ����.
	 * @var string
	 */
	public $descr;
	/**
	 * ����, ������������ �������� ���� "person" � ���������� Whois-�������.
	 * ON - � ���� "person" ��������� "Private Person", OFF - � ���� "person" ��������� ��� �������������� ������.
	 * ��� �������, ��������������� ������� �������� ����������� ����, �������� ����� �� ������ �� ���������� Whois-�������.
	 * �������������� ���� (�� ��������� ��� �������� OFF). ������������ ����.
	 * @var <type>
	 */
	public $privatePerson;
	/**
	 * @var string
	 */
	public $eMail;
	/**
	 * @var string
	 */
	public $phone;
	/**
	 * @var string
	 */
	public $faxNo;
	/**
	 * ����, ����������� �������� ������������ DNS-��������
	 * ��� ����������� ������ .RU � .SU
	 * ��� ��� ����������� ������ ���� UPDATE �� ��������� ������ DNS-��������
	 * ��� ������������������� ������.
	 * ON - ������������ ����������� ��������� ������������ DNS-��������,
	 * OFF - �� ��������� ����������� ��������� ������������ DNS-��������.
	 * ������������ ����, �������������� ��� ���������� (�� ��������� ��� �������� OFF).
	 */
	public $checkNs;

}
