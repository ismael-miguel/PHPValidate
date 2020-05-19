<?php

final class ValidateBaseRules {
	static private function fn_integer($value, $args)
	{
		if(!is_integer($value))
		{
			return false;
		}
		
		return self::fn_numeric($value, array_merge($args, array('decimals' => 0)));
	}
	static private function fn_number($value, $args)
	{
		if(!is_integer($value) && !is_float($value))
		{
			return false;
		}
		
		return self::fn_numeric($value, $args);
	}
	static private function fn_numeric($value, $args)
	{
		if($value === null || !is_numeric($value))
		{
			return false;
		}
		
		$parts = explode('.', $value);
		$value = $value / 1;
		
		if(isset($args['max']) && $value > $args['max'])
		{
			return false;
		}
		
		if(isset($args['min']) && $args['max'] > $value)
		{
			return false;
		}
		
		if(isset($args['min']) && $args['max'] > $value)
		{
			return false;
		}
		
		if(isset($args['decimals']) && $args['decimals'] > strlen($parts[1]))
		{
			return false;
		}
		
		return true;
	}
	
	static private function fn_string($value, $args)
	{
		if($value === null || !is_string($value))
		{
			return false;
		}
		
		$length = mb_strlen(
			$value,
			isset($args['encoding']) && $args['encoding']
				? $args['max_length']
				: mb_internal_encoding()
		);
		
		if(
			isset($args['max_length'])
			&& $args['max_length'] > 0
			&& $length > $args['max_length']
		)
		{
			return false;
		}
		
		if(
			isset($args['min_length'])
			&& $args['min_length'] > 0
			&& $args['min_length'] > $length
		)
		{
			return false;
		}
		
		if(
			isset($args['length'])
			&& $args['length'] > 0
			&& $args['length'] != $length
		)
		{
			return false;
		}
		
		if(
			isset($args['matches'])
			&& is_string($args['matches'])
		)
		{
			$matches = @preg_match($args['matches'], $value);
			if(!$matches)
			{
				return $matches === false ? null : false;
			}
			unset($matches);
		}
		
		return true;
	}
	
	static private function fn_array($value, $args)
	{
		if($value === null || !is_array($value))
		{
			return false;
		}
		
		if(isset($args['has']))
		{
			if(!is_array($args['has']))
			{
				return null;
			}
			
			$has = false;
			foreach($args['has'] as $key)
			{
				if(!$has && isset($value[$key]))
				{
					$has = true;
				}
			}
			
			if(!$has)
			{
				return false;
			}
		}
		
		if(isset($args['has']))
		{
			if(!is_array($args['has']))
			{
				return null;
			}
			
			$has = false;
			foreach($args['has'] as $key)
			{
				if(!$has && isset($value[$key]))
				{
					$has = true;
				}
			}
			
			if(!$has)
			{
				return false;
			}
			unset($has);
		}
		
		$count = count($value);
		
		if(
			isset($args['empty'])
			&& (!$args['empty'] && $count === 0)
		)
		{
			return false;
		}
		
		if(
			isset($args['max_count'])
			&& $args['max_count'] > 0
			&& $count > $args['max_count']
		)
		{
			return false;
		}
		
		if(
			isset($args['min_count'])
			&& $args['min_count'] > 0
			&& $args['min_count'] > $count
		)
		{
			return false;
		}
		
		if(
			isset($args['count'])
			&& $args['count'] > 0
			&& $args['count'] != $count
		)
		{
			return false;
		}
		
		return true;
	}
	
	static private function fn_in($value, $args)
	{
		return in_array($value, $args);
	}
	
	static private function fn_required($value, $args)
	{
		return $value !== null;
	}
	
	static private function fn_optional($value, $args)
	{
		return true;
	}
	
	// *******************************************************************************
	// *********************************** SPECIAL ***********************************
	// *******************************************************************************
	
	static private function fn_ip($value, $args)
	{
		if($value === null || !is_string($value))
		{
			return false;
		}
		
		static $ipv4_regex = '';
		static $ipv6_regex = '';
		
		if(!$ipv4_regex && !$ipv6_regex)
		{
			// https://stackoverflow.com/questions/53497/regular-expression-that-matches-valid-ipv6-addresses
			$ipv4_seg = '(?:25[0-5]|(?:2[0-4]|1[0-9]|[1-9])?[0-9])';
			$ipv4_addr = "(?:{$ipv4_seg}\.){3}{$ipv4_seg}";
			$ipv4_regex = "@^{$ipv4_addr}$@";
		
			$ipv6_seg = '[0-9a-fA-F]{1,4}';
			$ipv6_regex = <<<REGEX
				@^(?:
					(?:{$ipv6_seg}:){7}{$ipv6_seg}					# 1:2:3:4:5:6:7:8
					|(?:{$ipv6_seg}:){1,7}:							# 1::									1:2:3:4:5:6:7::
					|(?:{$ipv6_seg}:){1,6}:{$ipv6_seg}				# 1::8				1:2:3:4:5:6::8		1:2:3:4:5:6::8
					|(?:{$ipv6_seg}:){1,5}(?::{$ipv6_seg}){1,2}		# 1::7:8			1:2:3:4:5::7:8		1:2:3:4:5::8
					|(?:{$ipv6_seg}:){1,4}(?::{$ipv6_seg}){1,3}		# 1::6:7:8			1:2:3:4::6:7:8		1:2:3:4::8
					|(?:{$ipv6_seg}:){1,3}(?::{$ipv6_seg}){1,4}		# 1::5:6:7:8		1:2:3::5:6:7:8		1:2:3::8
					|(?:{$ipv6_seg}:){1,2}(?::{$ipv6_seg}){1,5}		# 1::4:5:6:7:8		1:2::4:5:6:7:8		1:2::8
					|{$ipv6_seg}:(?:(?::{$ipv6_seg}){1,6})			# 1::3:4:5:6:7:8	1::3:4:5:6:7:8		1::8
					|:(
						(?::{$ipv6_seg}){1,7}
						|:
					)												# ::2:3:4:5:6:7:8    ::2:3:4:5:6:7:8  ::8       ::       
					|fe80:(?::{$ipv6_seg}){0,4}%[0-9a-zA-Z]{1,}		# fe80::7:8%eth0     fe80::7:8%1  (link-local IPv6 addresses with zone index)
					|::(?:ffff(?::0{1,4})?:)?{$ipv4_addr}			# ::255.255.255.255  ::ffff:255.255.255.255  ::ffff:0:255.255.255.255 (IPv4-mapped IPv6 addresses and IPv4-translated addresses)
					|(?:{$ipv6_seg}:){1,4}:{$ipv4_addr}				# 2001:db8:3:4::192.0.2.33  64:ff9b::192.0.2.33 (IPv4-Embedded IPv6 Address)
				)$@x
REGEX;
		}
		
		$result = !!preg_match($ipv4_regex, $value);
		
		if(isset($args['ipv6']) && $args['ipv6'] && !$result)
		{
			$result = !!preg_match($ipv6_regex, $value);
		}
		
		return $result;
	}
	static private function fn_date($value, $args)
	{
		/*
			TODO - args:
			format - set standard format if not string
			before - $value must be before
			after - $value must be after
		*/
		
		static $parts = array(
			// day
			'd' => '3[01]|[12]\d|0[1-9]',
			'D' => 'Mon|Tue|Wed|Thu|Fri|Sat|Sun',
			'j' => '3[01]|[12]\d|[1-9]',
			'l' => '(?:Mon|Tues|Wednes|Thurs|Fri|Satur|Sun)day',
			'N' => '[1-7]',
			'S' => 'st|[nr]d|th',
			'w' => '[0-6]',
			'z' => '3(?:[0-5]\d|6[0-5])|[12]\d{2}|[1-9]?\d',
			
			// week
			'W' => '5[0-3]|[1-4]\d|[1-9]',
			
			// month
			'F' => 'January|February|March|April|May|Ju(?:ne|ly)|August|(?:Septem|Octo|Novem|Decem)ber',
			'm' => '1[012]|0[1-9]',
			'M' => 'Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec',
			'n' => '1[012]|[1-9]',
			't' => '2[89]|3[01]',
			
			// year
			'L' => '[01]',
			'o' => '\d{4}',
			'Y' => '\d{4}',
			'y' => '\d{2}',
			
			// time
			'a' => '[ap]m',
			'A' => '[AP]M',
			'B' => '\d{3}',
			'g' => '1[012]|[1-9]',
			'G' => '2[0-3]|1\d|[1-9]',
			'h' => '1[012]|0[1-9]',
			'H' => '2[0-3]|1\d|0[1-9]',
			'i' => '[0-5]\d',
			's' => '[0-5]\d',
			'u' => '\d{6}',
			'v' => '\d{3}',
			
			// timezone
			// 'e' => '\p{Titlecase_Letter}(?:[\p{Titlecase_Letter}]{2}|[\p{Lowercase_Letter}\p{Other_Letter}\p{Modifier_Letter}]+\/\p{Titlecase_Letter}[\p{Lowercase_Letter}\p{Other_Letter}\p{Modifier_Letter}]+)',
			'e' => '\p{Lu}(?:[\p{L}]+\/\p{Lu}[\p{L}]+|[\p{Lu}]+)?',
			'I' => '[01]',
			'O' => '[\+\-](?:2[0-3]|1\d|0[1-9])(?:[0-5]\d)',
			'P' => '[\+\-](?:2[0-3]|1\d|0[1-9]):(?:[0-5]\d)',
			// 'T' => '[\p{Titlecase_Letter}]{3}',
			'T' => '\p{Lu}{3}',
			'Z' => '-43200|-43[01]\d{2}|-4[012]\d{3}|-[1-3]\d{4}|-?[1-9]\d{0,3}|-?0|[1-4]\d{4}|50[0-3]\d{2}|50400',
			
			// full date/time
			// Y-m-d\TH:i:sP
			'c' => '(?:\d{4})-(?:1[012]|0[1-9])-(?:3[01]|[12]\d|0[1-9])T(?:2[0-3]|1\d|0[1-9]):(?:[0-5]\d):(?:[0-5]\d)(?:[\+\-](?:2[0-3]|1\d|0[1-9]):(?:[0-5]\d))', // 2004-02-12T15:19:21+00:00
			// D, j M Y H:i:s O
			'r' => '(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun), (?:3[01]|[12]\d|[1-9]) (?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (?:\d{4}) (?:2[0-3]|1\d|0[1-9]):(?:[0-5]\d):(?:[0-5]\d) (?:[\+\-](?:2[0-3]|1\d|0[1-9])(?:[0-5]\d))', // Thu, 21 Dec 2000 16:01:07 +0200
			'U' => '-?(?:\d|[1-9]\d*)'
		);
		
		if(!isset($args['format']))
		{
			// 2005-08-15T15:52:01+00:00 - DATE_ATOM
			$args['format'] = 'Y-m-d\TH:i:sP';
		}
		
		$regex = preg_replace_callback('@(\\\\+)?[dDjlNSwzWFmMntLoYyaABgGhHisuveIOPTZcrU]@', function($letter)use(&$parts){
			return $letter[1] ? preg_quote($letter[2], '@') : '(?:' . $parts[$letter[2]] . ')';
		}, preg_quote($args['format'], '@'));
		
		return !!preg_match('@^' . $regex . '$@', $value);
	}
	
	// *******************************************************************************
	
	static function validate_rule($rule, $value, array $args = array()){
		if(!self::rule_exists($rule))
		{
			return null;
		}
		
		return call_user_func_array(array(__CLASS__, 'fn_' . $rule), array($value, $args));
	}
	
	static function rule_list(){
		static $list = array();
		
		if(!$list)
		{
			foreach(get_class_methods(__CLASS__) as $rule)
			{
				if(strpos($rule, 'fn_') === 0)
				{
					$list[] = preg_replace('@^fn_@', '', $rule);
				}
			}
		}
		
		return $list;
	}
	
	static function rule_exists($rule){
		static $exist = array();
		
		return isset($exist[$rule])
			? $exist[$rule]
			: (
				is_null($exist[$rule] = @method_exists(__CLASS__, 'fn_' . $rule))
					? in_array($rule, self::rule_list())
					: $exist[$rule]
			);
	}
}

abstract class acValidable {
	protected $data = array();
	
	public function success(array $rules = array())
	{
		foreach($this->validate($rules) as $result)
		{
			if($result !== true)
			{
				return false;
			}
		}
		
		return true;
	}
	
	public function failed(array $rules = array())
	{
		return !$this->success($rules);
	}
	
	public function validate(array $rules = array()){
		if(!$rules)
		{
			$rules = $this->rules;
		}
		$results = array();
		
		foreach($rules as $name => $rule)
		{
			if(is_string($rule))
			{
				$results[$name] = $this->_validate_rule(
					$rule,
					$this->__get($name)
				);
			}
			else if(is_array($rule))
			{
				$results[$name] = array();
				foreach($rule as $rule_name => $rule_args)
				{
					if(is_string($rule_args))
					{
						$results[$name][$rule_args] = $this->_validate_rule(
							$rule_args,
							$this->__get($name)
						);

					}
					else
					{
						$results[$name][$rule_name] = $this->_validate_rule(
							$rule_name,
							$this->__get($name),
							$rule_args
						);
					}
				}
			}
			else
			{
				$results[$name] = null;
			}
		}
		
		return $results;
	}
	
	protected function _validate_rule($rule, $value, array $args = array()){
		if($this->rule_exists($rule))
		{
			return $this->validate_rule($rule, $value, $args);
		}
		else if(ValidateBaseRules::rule_exists($rule))
		{
			return ValidateBaseRules::validate_rule($rule, $value, $args);
		}
		else
		{
			return null;
		}
	}
	
	abstract public function add_rule($rule, $fn);
	abstract public function rule_exists($rule);
	abstract protected function validate_rule($rule, $value, array $args = array());
	
	/* * * magic methods * * */
	
	public function __get($name){
		$data = &$this->data;
		foreach(explode('.', $name) as $key)
		{
			if(!isset($data[$key]))
			{
				return null;
			}
			else
			{
			    $data = &$data[$key];
			}
		}
		
		// clears the reference
		$return = $data;
		return $return;
	}
	
	public function __set($name, $value){
		$data = &$this->data;
		foreach(explode('.', $name) as $key)
		{
			if(!isset($data[$key]))
			{
				$data = array("$key" => null);
			}
			else
			{
			    $data = &$data[$key];
			}
		}
		
		$data = $value;
	}
	
	public function __isset($name){
		$data = &$this->data;
		foreach(explode('.', $name) as $key)
		{
			if(!isset($data[$key]))
			{
				return false;
			}
			else
			{
			    $data = &$data[$key];
			}
		}
		
		return true;
	}
}


final class Validate extends acValidable {
	private $rules = array();
	
	public function __construct(array $data){
		$this->data = $data;
	}
	
	public function add_rule($rule, $fn){
		if(!is_callable($fn) || $this->rule_exists($rule))
		{
			return false;
		}
		
		$this->rules[$rule] = $fn;
		return true;
	}
	
	public function rule_exists($rule){
		return isset($this->rules[$rule]);
	}
	
	protected function validate_rule($rule, $value, array $args = array())
	{
		return is_string($this->rules[$rule]) && !$args
			? $this->rules[$rule]($value)
			: $this->rules[$rule]($value, $args);
	}
}

final class ValidatePost {
	static private $validator = null;
	
	public static function __callStatic($name, $arguments)
	{
		if(!self::$validator)
		{
			self::$validator = new Validate($_POST);
			self::$validator->add_rule('string', function($value, array $args = array()){
				return ValidateBaseRules::validate_rule('string', $value, array_merge(
					$args,
					array(
						'encoding' => isset($args['encoding'])
							? $args['encoding']
							: mb_http_input()
					)
				));
			});
		}
		
		return call_user_func_array(array(self::$validator, $name), $arguments);
	}
}

final class ValidateGet {
	static private $validator = null;
	
	public static function __callStatic($name, $arguments)
	{
		if(!self::$validator)
		{
			self::$validator = new Validate($_GET);
			self::$validator->add_rule('string', function($value, array $args = array()){
				return ValidateBaseRules::validate_rule('string', $value, array_merge(
					$args,
					array(
						'encoding' => isset($args['encoding'])
							? $args['encoding']
							: mb_http_input()
					)
				));
			});
		}
		
		return call_user_func_array(array(self::$validator, $name), $arguments);
	}
}
