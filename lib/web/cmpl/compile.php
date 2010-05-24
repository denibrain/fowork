<?php
namespace FW;

class Compiler extends Object {
	
	function parse($text) {
		$state = S_MAIN;
		
		$stops = array(
			S_MAIN => '[{<]',
			S_TEXT => '[{}<]',
			S_TAG => '(?<space>[\n\r\t ]+)|(?<attr>[a-z][a-z0-9-])|>|\x2F>',
			S_TEXTINTAG => '/{|<\x2F|\x2F>',
		);
		
		$rules = array(
			
		);
		
		$main = array(
			'text' => array('tag'=>'+text,tag', 'command'=>'+text,command', 'end'=>'end', 'raw'=>'text')
		);

		$tag = array(
			'tag' => array('space'=>'tag', 'attr'=>'attrname', 'short'=>'-', 'close'=>'+text,tagtext'),
			'attrname' => array('space'=>'attrname', 'eq'=>'eq'),
			'eq' => array('space'=>'eq', 'dbquote'=>'+tag,dbstr', 'squote'=>'+tag,sstr'),
			'text'=> array('closetag'=>'-')
			
		);
		
		$tagtext = array(
			'text' => array('tag'=>'+text,tag', 'command'=>'+text,command', 'closetag'=>'--', 'raw'=>'text')
		);
		
		$dbstr = array(
			'text', array('command'=>'+text,command', 'raw'=>'text', 'dbquote'=>'-', 'escdbquote'=>'text')
		);
		$sstr = array(
			'text', array('command'=>'+text,command', 'raw'=>'text', 'squote'=>'-', 'escsquote'=>'text')
		);
		
		$command = array(
			'command'=> array('css'=>'importcss', 'js'=>'importjs', 'path'=>'path',
				'if'=>'+text,expression', 'else'=>'text', '+text,expression', 'foreach' => 'foreach',
				'enum'=>'enum', 'call'=>'call', 'prop'=>'prop', 'var'=>'var',
				'squote'=>'inlinetext', 'dbquote'=>'dbinlinetext'),
			// inline texts
			'inlinetext'=> array('raw'=>'inlintetext', 'sendtext'=>'-'),
			'dbinlinetext'=> array('raw'=>'dbinlintetext', 'dbendtext'=>'-'),
			
			// if
			'text'=>array('raw'=>'text', 'command'=>'+text,command', 'close'=>'-', 'tag'=>'+text,tag'),
			
			// var
			'var' => array('name'=>'varname'),
			'varname'=> array('cend'=>'-', 'space'=>'varname', 'eq'=>'setvar'),
			'setvar' => array('space' => 'setvar', )
		);
		
		$expression = array(
			'text'=> array('unop'=>'preoperand', 'path'=>'operand','str2'=>'operand','str1'=>'operand','num'=>'operand', 'open'=>'+text,expr2'),
			'preoperand'=> array('path'=>'operand','str2'=>'operand','str1'=>'operand','num'=>'operand', 'open'=>'+text,expr2'),
			'operand'=>array('unop'=>'text', 'op'=>'text', 'semicolon'=>'-')
		);
		
		$expr2 = $expression;
		$expression['operand']['semicolon'] = '-';
		$expr2['operand']['close'] = '-';


		$soft = array(S_MAIN=>1, S_TEXT=>1);
		$new_text = '';
		
		while ($text) {
			$isSoft = isset($soft[$state]);
			$regex = ($isSoft?'/':'/^').$stops[$state].'/';
			if (!preg_match($stops[$state], $text, $matches, PREG_OFFSET_CAPTURE)) {
				if ($isSoft) $curNode->addNode(new nText($text));
				else throw new EApp('Unexpected end of file');

			list($value, $pos) = array_shift($matches);
			if ($soft)	$curNode->addNode(new nText(substr($text, 0, $pos)));
			$text = substr($text, 0, $pos + strlen($key));
			if (count($matches)) {
				$key = key($matches);
			} else $key = $value;
			
			switch ($key) {
				case '{': // only S_MAIN & S_COMMAND & S_ATTRVALUE
					if (!preg_match("css|js|[?ceip@&%$']) ", $text, $matches, PREG_OFFSET_CAPTURE)) {
						throw new EApp("invalid command");
					$state = S_COMMAND;
					list($key, $pos) = array_shift($matches);
					switch ($key) {
						case 'css': // css
							if (!preg_match('"\s*([\x5E/a-z0-9.])\s*}"i', $text, $matches))
								throw new Eapp("Invalid css command");
							$curNode->addNode(new nCss($matches[1]));
							$text = substr($text, strlen($matches[0]));
							break;
						case 'js': // js
							if (!preg_match('"\s*([\x5E/a-z0-9.])\s*}"i', $text, $matches))
								throw new Eapp("Invalid js command");
							$curNode->addNode(new nJavaScript($matches[1]));
							$text = substr($text, strlen($matches[0]));
							break;
						case 'i': // import script
							if (!preg_match('"\s*([\x5E/a-z0-9.])\s*}"i', $text, $matches))
								throw new Eapp("Invalid import command");
							$curNode->addNode(new nImport($matches[1]));
							$text = substr($text, strlen($matches[0]));
							break;
						case '?': // if
						case 'e': // else
						case 'c': // call
						case 'p': // property
						case "'": // text
							if (!preg_match("/'}/", $text, $matches, PREG_OFFSET_CAPTURE))
								throw new Eapp("Invalid import command");
							list($key, $pos) = array_shift($matches);
							$curNode->addNode(new nText(sunstr($text, 0, $pos)));
							$text = substr($text, $pos + 2);
							break;
							
						case '@': // for
						case '&': // enum
						case '$': // set var / var
							if (!preg_match('/^([a-z_][0-9a-z_-]*)(?:\s*(=)s\*)?/i', $text, $matches)) 
								throw new EApp('Ivalid var');
							$text = substr($text, strlen($matches[0]));
							if (isset($matches[2])) {
								$curNode = $curNode->addNode(nSetVar($matches[1]));
								$state = S_EXPRESSION;
							}
							else $curNode->addNode(nUseVar($matches[1]));
					}
					
					//...
					break;
				case 'space':
					switch ($state) {
						case S_TAG: // skip
							break;
					}
				case 'attr':
					if (!preg_match('/^\s*=\s*(["\'])/', $text, $matches))
						throw new EApp('Invalid stag syntax');
					$curNode = $curNode->addNode(nTagAttr($value, $matches[1]));
					$state = S_ATTRVALUE;
					$text = substr($text, strlen($matches[0]));
					break;
				case '/>': // only S_TAG
					$state = S_TEXT;
					if (!($curNode = $curNode->parent))
						throw new EApp('Syntax error');
					break;
				case '>': 
					switch ($state) {
						case S_TAG:
							$state = S_TEXTINTAG;
							break;
						
					break;
				case '</':
					if (!preg_match('/([a-z][a-z0-9-]*(?::[a-z][a-z0-9-]*)?)>/', $text, $matches))
						throw new EApp("Invalid closed tag");
					if ($curNode->name != $matches[1])
						throw new EApp("Unexpected closed tag");
					if (!($curNode = $curNode->parent))
						throw new EApp('Syntax error');
					break;						
				case '<':
					switch ($state) {
						S_MAIN:
						case S_TEXT:
							if (!preg_match('[a-z][a-z0-9-]*(?::[a-z][a-z0-9-]*)?|!--|![', $text, $m, PREG_OFFSET_CAPTURE))
								throw new EApp('Invalid tag');

							list($key, $pos) = $matches[0];
							$text = substr($text, strlen($key));
							switch ($key) {
								case '!--':
									$pos = strpos($text, '-->');
									if (false===$pos) throw new EApp('Unexpected end of comment');
									$text = substr($test, $pos + 3);
									break;
								case '![':
									$pos = strpos($text, ']>');
									if (false===$pos) throw new EApp('Unexpected end of <![');
									$curNode->addNode(new nText(substr($text, 0, $pos + 2)));
									$text = substr($text, $pos + 2);
									break;
								default:
									$state = S_TAG;
									$curNode = $curNode->addNode(nTag($key));
							}
							break;
						case S_EXPRESSION:
							
					}
					
			}
			
		}
		
		while(
			list($key, $pos) = $matches[1];
			
			$new_text .= substr($text, 0, $pos);
							   
							   
		}
	}
}

?>

<img src="{? img: /design/im/{img}.png}{e /design/im/noimg.png}"/>

{? img: {$src=/design/im/{img}.png}
{e $src=/design/im/noimg.png}
<img src="{$src}"/>


{? img: {$src=/design/im/{img}.png}
{e $src=/design/im/noimg.png}
<img src="{$src}">{? img: {a class=big}{e {a class=small}}</img>

{? a>b
	{? z< b
	
	}
}