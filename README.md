# PHPValidate
Simple library for basic validation of data.

---

## Getting started

To start using the validator, simply construct it with the data you want to validate:

    $validate = new Validate(<data array>);
	
All of the data will be saved internaly.
Hint: to validate POST or GET values, use the classes `ValidatePost` and `ValidateGet`.

## Available methods

These are all the methods in the `Validate` class:

 - `validate(array $rules = array())`  
	Returns the validation result for each and every single method in the `$rules`.
   
 - `success(array $rules = array())`  
 	Returns `true` if all rules returned **`true`**.
	
 - `failed(array $rules = array())`  
 	Returns `true` if **ANY** rule returns **`false`**.
	
 - `add_rule($rule, $fn)`  
 	Creates a rule with name `$rule`.  
	The `$fn` must have the signature `function($value, array $args = array())`.  
	Reurns `true` if succeeded, `false` if already was added before.
  
 - `rule_exists($rule)`  
 	Verifies if the `$rule` already exists.
	
 - `validate_rule($rule, $value, array $args = array())`
 	Verifies if the `$value` valudates with the `$rule` with the optional `$args`.

	
## Important information

The `$rules` are always an array and can have the following structure:

 - `array('key' => 'single method')`
 - `array('key' => array('method1', 'method2', ...))`
 - `array('key' => array('method1' => array('arg1' => 'value1', ... ), ...)`
 
 Array values can be refered with the dot notation (`value1.value2.[...]`).
 
### Available rules

 - `integer`
 	Verifies if the value is an integer number (duh)
	
 - `number`
 	Verifies if the value is a number (duh).  
	Either integer or floating-point numbers validate
	
 - `numeric`
 	Verifies if the number can be evaluated as a `number` when converting as such.  
	Arguments:
	 - `max` - Maximum value to validate
	 - `min` - Minimum value to validate
	 - `decimals` - Number of decimal places accepted (default: 0)
 
 - `string`
	Verifies if the value is a string.  
	Arguments:
	 - `encoding` - Specific encoding to use to validate (Default: `mb_internal_encoding()`)
	 - `max_length` - Maximum length for the string (relies on `mb_strlen()`)
	 - `min_length` - Minimum length for the string (relies on `mb_strlen()`)
	 - `length` - Exact length of the string (relies on `mb_strlen()`)
	 - `matches` - Matches against the specified regular expression (relies on `preg_match()`)
 
 - `array`
 	Verifies that the value is an array.  
	Arguments:
	 - `has` - Verifies if the array has the specific key or keys (can be a simple string, number or an array of keys)
	 - `empty` - When set to `true`, verifies if the array is empty
	 - `max_count` - Maximum number of elements in the array
	 - `min_count` - Minimum number of elements in the array
	 - `count` - Exactly this many elements in the array
	 
 - `in`
 	Verifies if the given value exists in the array
	
 - `required`
 	Verifies if the value exists (`null` values will fail this verification)
	
 - `optional`
 	Always returns `true`
	
 - `ip`
 	Verifies that the value is a valid IPv4 address.  
	Arguments:
	 - `ipv6` - Tries to verify as an IPv6 address
	 
 - `date`
 	Verifies if it is a valid date.  
	Arguments:
	 - `format` - **This argument is required**. Takes a format used by the `date()` function. (Default: `'Y-m-d\TH:i:sP'` - DATE_ATOM)
