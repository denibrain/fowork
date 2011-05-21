<?php
/**
	Sample
*/

$rootDir = dirname(__FILE__);
require $rootDir.'/lib/commons/loader.php';
Loader::setAlias('root', $rootDir);
Loader::load(array(
	'root.lib.commons.component',
	'root.lib.commons.*',
	'root.lib.commons.collections.*',
	'root.lib.*',
	'root.lib.nicobjects.abstract.*',
	'root.lib.nicobjects.*',
));

$nm = new NicManager('370/NIC-REG/adm', 'dogovor');

$contract = new ContractIP;
$contract->password = "qwerty";
$contract->techPassword = 'asdfgh';
$contract->person = 'Ioan U Ehjikov';
$contract->personR = 'ИП Иоан Уевич Ёxжиков';
$contract->country = 'RU';
$contract->currencyId = 'RUR';
$contract->passport = "XXX-AB 123456 выдан 123 отделением милиции г.Москвы, 30.01.1990\nзарегистрирован по адресу: Москва, ул.Кошкина, д.15, кв.4";
$contract->addressR = '123456 Москва, ул.Собачкина, д.13а, кв.78';
$contract->code = '121200025218';
$contract->birthDate = '11.11.1980';
$contract->pAddr = '123456, Москва, ул.Кошкина, д.15, кв.4 Сидорову Сидору Сидоровичу';
$contract->phone = "+7 495 1234567\n+7 495 1234569";
$contract->faxNo = '+7 495 1234560';
$contract->email = "sidor@sitef.ru";
$contract->mntNfy = 'adm-group@my-internet-name.ru';

echo $nm->contractCreate($contract); //930272/NIC-D

//echo $nm->contractGet("930272/NIC-D");

//list($found, $contractGroups) = $nm->contractSearch(array('e-mail' => 'sidor@sitef.ru'), 3);
//
//foreach($contractGroups as $contactId => $contactData){
//	echo $contactId."\n";
//	var_dump($contactData->getArray()); //объект класса Group
//}

//$nm->contractDelete($contractId);

//$contr = new ContractIP;
//$contr->email = 'jajajka@sitef.ru';
//$nm->contractUpdate('930272/NIC-D', $contr);

$domain = new DomainRU;
$domain->domain = 'jaja.ru';
$domain->descr = 'blahBlah';
$domain->email = 'adad@asdad.ru';
$domain->nsserver = 'adad@asdad.ru';
$domain->faxNo = '+67 6556 6565 5';
$domain->phone = '+67 6556 6565 5';
$domain->privatePerson = 'ON';



//$nm->domainRuCreate();

/*
$contactData = array();
$contactData['contract-type'] = 'ORG';
$contactData['password'] = 'YTREWQ';
$contactData['tech-password'] = 'WD328D';
$contactData['org'] = "Joint Stock Company\"Novoe vremya\"";
$contactData['org-r'] = "Закрытое Акционерное Общество\n'Новое время'";
$contactData['code'] = '1234567894';
$contactData['kpp'] = '123456789';
$contactData['country'] = 'RU';
$contactData['currency-id'] = 'RUR';
$contactData['address-r'] = "123456, Москва,\nул.Собачкина, д.13а";
$contactData['p-addr'] = "123456, Москва, ул.Собачкина, д.13а, АО \"Новое Время\",\nОтдел телекоммуникаций,\nСидорову Сидору Сидоровичу";
$contactData['d-addr'] = '123456, Москва, ул.Собачкина, д.13а';
$contactData['phone'] = "+7 495 1234567\n+7 495 1234568\n+7 495 1234569";
$contactData['fax-no'] = '+7 495 1234560';
$contactData['e-mail'] = 'adm@site.ru';
$contactData['mnt-nfy'] = "noc@my-internet-name.ru\nivanov@my-internet-name.ru";

$delResponse = $nm->contractDelete('927986/NIC-D');
$delResponse();

*/


//$creResponse = $nm->createContract($contactData);
//$creResponse();


//927986/NIC-D
//$getResponse = $nm->getContract('927986/NIC-D');
//$getResponse();


/*
$searchData = new Group;
$searchData['e-mail'] = 'adm@site.ru';
$searchResponse = $nm->searchContract($searchData);
$searchResponse();
*/

exit();