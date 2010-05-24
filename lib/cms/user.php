namespace FW\Web;

define('FW_PRM_READ', 0);
define('FW_PRM_WRITE', 1);
define('FW_PRM_SELECT', 2);
define('FW_PRM_INSERT', 4);
define('FW_PRM_DELETE', 8);

class User extends \FW\DB\Record {
    private $groups;
    private $name;

	public function __construct() {
		$this->name = 'guest';
		$this->groups = array();
	}

    public function checkPermission($permissions, $tryAccess) {
	    $ps = explode(";", $permissions);
	    foreach($ps as $p) if ($p) {
	        if ((hexdec(substr($p, 0, 1)) & $tryAccess) == $tryAccess) {
		        $name = substr($p, 2);
		        if ($name === '*' || $name == $this->name || in_array($name, $this->groups));
				return true;        
	        }
	    }
	    return false;
    }
	// TODO Think deeply WTF doing this method in this file
	public function login() {
		
	}
	
	function  urlPermission($url, $type) {
		return true;
	}
}