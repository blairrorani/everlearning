<?php
namespace Craft;

/**
 * Class ArrayHelper
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2014, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com
 * @package   craft.app.helpers
 * @since     1.0
 */
class ArrayHelper
{
	// Public Methods
	// =========================================================================

	/**
	 * Flattens a multi-dimensional array into a single-dimensional array.
	 *
	 * @param        $arr
	 * @param string $prefix
	 *
	 * @return array
	 */
	public static function flattenArray($arr, $prefix = null)
	{
		$flattened = array();

		foreach ($arr as $key => $value)
		{
			if ($prefix !== null)
			{
				$key = "{$prefix}[{$key}]";
			}

			if (is_array($value))
			{
				$flattened = array_merge($flattened, static::flattenArray($value, $key));
			}
			else
			{
				$flattened[$key] = $value;
			}
		}

		return $flattened;
	}

	/**
	 * Expands a flattened array back into its original form
	 *
	 * @param array $arr
	 *
	 * @return array
	 */
	public static function expandArray($arr)
	{
		$expanded = array();

		foreach ($arr as $key => $value)
		{
			// is this an array element?
			if (preg_match('/^(\w+)(\[.*)/', $key, $m))
			{
				$key = '$expanded["'.$m[1].'"]'.preg_replace('/\[([a-zA-Z]\w*?)\]/', "[\"$1\"]", $m[2]);
				eval($key.' = "'.addslashes($value).'";');
			}
			else
			{
				$expanded[$key] = $value;
			}
		}

		return $expanded;
	}

	/**
	 * @param $settings
	 *
	 * @return array
	 */
	public static function expandSettingsArray($settings)
	{
		$arr = array();

		foreach ($settings as $setting)
		{
			$arr[$setting->name] = $setting->value;
		}

		return static::expandArray($arr);
	}

	/**
	 * Converts a comma-delimited string into a trimmed array, ex:
	 *
	 *     ArrayHelper::stringToArray('one, two, three') => array('one', 'two', 'three')
	 *
	 * @param mixed $str The string to convert to an array
	 *
	 * @return array The trimmed array
	 */
	public static function stringToArray($str)
	{
		if (is_array($str))
		{
			return $str;
		}
		else if ($str instanceof \ArrayObject)
		{
			return (array) $str;
		}
		else if (empty($str))
		{
			return array();
		}
		else if (is_string($str))
		{
			return array_merge(array_filter(array_map('trim', preg_split('/(?<!\\\),/', $str))));
		}
		else
		{
			return array($str);
		}
	}

	/**
	 * Prepends or appends a value to an array.
	 *
	 * @param array &$arr
	 * @param mixed $value
	 *
	 * @param bool  $prepend
	 */
	public static function prependOrAppend(&$arr, $value, $prepend)
	{
		if ($prepend)
		{
			array_unshift($arr, $value);
		}
		else
		{
			array_push($arr, $value);
		}
	}

	/**
	 * Filters empty strings from an array.
	 *
	 * @param array $arr
	 *
	 * @return array
	 */
	public static function filterEmptyStringsFromArray($arr)
	{
		return array_filter($arr, array('\Craft\ArrayHelper', '_isNotAnEmptyString'));
	}

	/**
	 * Returns the first value in a given array.
	 *
	 * @param array $arr
	 *
	 * @return mixed|null
	 */
	public static function getFirstValue($arr)
	{
		if (count($arr))
		{
			if (isset($arr[0]))
			{
				return $arr[0];
			}
			else
			{
				return $arr[array_shift(array_keys($arr))];
			}
		}
	}

	// Private Methods
	// =========================================================================

	/**
	 * The array_filter() callback function for filterEmptyStringsFromArray().
	 *
	 * @param string $val
	 *
	 * @return bool
	 */
	private function _isNotAnEmptyString($val)
	{
		return (mb_strlen($val) != 0);
	}
}
