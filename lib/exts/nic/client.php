<?php
namespace FW\Exts\Nic;

/**
 * Description of nicclient
 *
 * @author a.garipov
 */
class Client extends Component {
	private $url;
	private $ch;
	
	public function __construct($url = 'https://www.nic.ru/dns/dealer') {
		$this->url = $url;
		$this->ch = curl_init();

		curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	}

	public function __destruct() {
		curl_close($this->ch);
	}

	/**
	 * Executes request
	 * @param Request $req
	 * @return Response
	 */
	public function doRequest(Request $request)	{
		$requestString = (string) $request;
		$requestString = iconv('windows-1251', 'koi8-r', $requestString);

		curl_setopt($this->ch, CURLOPT_POSTFIELDS, array('SimpleRequest'=>$requestString));
		$responseString = curl_exec($this->ch);
		
		if($responseString === false)
			throw new EClient(curl_error($this->ch));
				
		$status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($status !== 200)
			throw new ERequest($responseString, $status);
				
		$responseString = iconv('koi8-r', 'windows-1251', $responseString);
		return new Response($responseString);
	}
}

class EClient extends Exception {}
class ERequest extends Exception {}