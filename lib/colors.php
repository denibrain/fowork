<?php
# first define colors to use

##############################################
# Output colorized text to terminal run
# php scripts..
##############################################
function termcolored($text, $color="NORMAL", $back=1){
$_colors = array(
        'LIGHT_RED'      => "[1;31m",
        'LIGHT_GREEN'     => "[1;32m",
        'YELLOW'         => "[1;33m",
        'LIGHT_BLUE'     => "[1;34m",
        'MAGENTA'     => "[1;35m",
        'LIGHT_CYAN'     => "[1;36m",
        'WHITE'         => "[1;37m",
        'NORMAL'         => "[0m",
        'BLACK'         => "[0;30m",
        'RED'         => "[0;31m",
        'GREEN'         => "[0;32m",
        'BROWN'         => "[0;33m",
        'BLUE'         => "[0;34m",
        'CYAN'         => "[0;36m",
        'BOLD'         => "[1m",
        'UNDERSCORE'     => "[4m",
        'REVERSE'     => "[7m"

);    $out = $_colors["$color"];
    if($out == ""){ $out = "[0m"; }
    if($back){
        return chr(27)."$out$text".chr(27)."[0m";#.chr(27);
    }else{
        echo chr(27)."$out$text".chr(27).chr(27)."[0m";#.chr(27);
    }//fi
}// end function
##############################################


class Colors {
		private $foreground_colors = array();
		private $background_colors = array();

		public function __construct() {
			// Set up shell colors
			$this->foreground_colors['black'] = '0;30';
			$this->foreground_colors['dark_gray'] = '1;30';
			$this->foreground_colors['blue'] = '0;34';
			$this->foreground_colors['light_blue'] = '1;34';
			$this->foreground_colors['green'] = '0;32';
			$this->foreground_colors['light_green'] = '1;32';
			$this->foreground_colors['cyan'] = '0;36';
			$this->foreground_colors['light_cyan'] = '1;36';
			$this->foreground_colors['red'] = '0;31';
			$this->foreground_colors['light_red'] = '1;31';
			$this->foreground_colors['purple'] = '0;35';
			$this->foreground_colors['light_purple'] = '1;35';
			$this->foreground_colors['brown'] = '0;33';
			$this->foreground_colors['yellow'] = '1;33';
			$this->foreground_colors['light_gray'] = '0;37';
			$this->foreground_colors['white'] = '1;37';

			$this->background_colors['black'] = '40';
			$this->background_colors['red'] = '41';
			$this->background_colors['green'] = '42';
			$this->background_colors['yellow'] = '43';
			$this->background_colors['blue'] = '44';
			$this->background_colors['magenta'] = '45';
			$this->background_colors['cyan'] = '46';
			$this->background_colors['light_gray'] = '47';
		}

		// Returns colored string
		public function getColoredString($string, $foreground_color = null, $background_color = null) {
			$colored_string = "";

			// Check if given foreground color found
			if (isset($this->foreground_colors[$foreground_color])) {
				$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
			}
			// Check if given background color found
			if (isset($this->background_colors[$background_color])) {
				$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
			}

			// Add string and end coloring
			$colored_string .=  $string . "\033[0m";

			return $colored_string;
		}

		// Returns all foreground color names
		public function getForegroundColors() {
			return array_keys($this->foreground_colors);
		}

		// Returns all background color names
		public function getBackgroundColors() {
			return array_keys($this->background_colors);
		}
	}
?>
