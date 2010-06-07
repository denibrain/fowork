<?php
namespace FW\Net;

class Whois extends \FW\Object {

	static $whoisservers = array(
		"biz"	=>array("whois.biz", "Not found: "),
		"cc"	=>array("whois.nic.cc", "No match for "),
		"cn"	=>array("whois.cnnic.net.cn", "no matching record"),
		"com"	=>array("whois.verisign-grs.com", "No match for "),
		"de"	=>array("whois.denic.de", "status:      free"),
		"in"	=>array("whois.inregistry.net", "NOT FOUND"),
		"info"	=>array("whois.afilias.info", "NOT FOUND"),
		"kz"	=>array("whois.nic.kz", "Nothing found for this query"),
		"net"	=>array("whois.verisign-grs.net", "No match for "),
		"mobi"	=>array("whois.dotmobiregistry.net", "NOT FOUND"),
		"name"	=>array("whois.nic.name", "No match"),
		"org"	=>array("whois.pir.org", "NOT FOUND"),
		"ru"	=>array("whois.ripn.net", "No entries found"),
		"su"	=>array("whois.ripn.net", "No entries found"),
		"tc"	=>array("whois.adamsnames.tc", " is not registered."),
		"tel"	=>array("whois.nic.tel", "Not found: "),
		"tv"	=>array("tvwhois.verisign-grs.com", "No match for "),
		"vg"	=>array("whois.adamsnames.tc", "is not registered."),
		"ws"	=>array("whois.website.ws", "No match for ")
	);

	private static function QueryWhoisServer($whoisserver, $domain) {
		$port = 43;
		$timeout = 10;
		if (($fp = @fsockopen($whoisserver, $port, $errno, $errstr, $timeout)) == false) return false;
	
		fputs($fp, $domain . "\r\n");
		$out = "";
		while(!feof($fp)){
			$out .= fgets($fp);
		}
		fclose($fp);
	
		return $out;
	}

	static function GetDomainInfo($domain) {
		\FW\Util\Validator::validate($domain, 'domain2level');
		list($name, $tld) = explode('.', $domain);

		if (!isset(self::$whoisservers[$tld])) throw new ENoTld($tld);
		list($whoisserver, $notfound) = self::$whoisservers[$tld];
		$domain = "$name.$tld";
		$result = self::QueryWhoisServer($whoisserver, $domain);

		if ($result) {
			if (preg_match("/Whois Server: (.*)/", $result, $matches)) {
				$secondary = $matches[1];
				if($secondary) $result = self::QueryWhoisServer($secondary, $domain);
			}
			if (false===strpos($result, $notfound))	return $result;
		} else {
			throw new Exception("service not availble");
		}
		return false;
	}
}

class ENoTld extends \Exception {
	function __construct($tld) {
		parent::__construct("No $tld");
	}
}

?>