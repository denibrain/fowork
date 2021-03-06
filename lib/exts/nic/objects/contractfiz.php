<?php
/**
 * ������ ��� ���. ����
 * 
 */
class ContractFIZ extends Contract
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
	 * ���� ����� ��������� ������� � ��������� �����, �����, ����� ���������� � �������.
	 * ������������ ����� - 64 �������.
	 * ������������ ����.
	 * ������������� ����.
	 * @var string
	 */
	public $personR;


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
	
}

