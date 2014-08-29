<?php

namespace Liquid;

/**
 * A selection of standard filters.
 */
class StandardFilters
{
	/**
	 * Return the size of an array or of an string
	 *
	 * @param mixed $input
	 *
	 * @return int
	 */
	public static function size($input) {
		if (is_string($input) || is_numeric($input)) {
			return strlen($input);
		} elseif (is_array($input)) {
			return count($input);
		} elseif (is_object($input)) {
			if (method_exists($input, 'size')) {
				return $input->size();
			}
		}

		return $input;
	}

	/**
	 * Convert an input to lowercase
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function downcase($input) {
		return is_string($input) ? strtolower($input) : $input;
	}

	/**
	 * Convert an input to uppercase
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function upcase($input) {
		return is_string($input) ? strtoupper($input) : $input;
	}

	/**
	 * Capitalize words in the input sentence
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function capitalize($input) {
		// todo: deprecated "e"
		return preg_replace("!(^|[^\p{L}'])([\p{Ll}])!ue", "'\\1'.ucfirst('\\2')", ucwords($input));
	}

	/**
	 * Escape a string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function escape($input) {
		return is_string($input) ? addslashes($input) : $input;
	}

	/**
	 * Strip all newlines (\n, \r) from string
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function strip_newlines($input) {
		return is_string($input) ? str_replace(array(
			"\n", "\r"
		), '', $input) : $input;
	}

	/**
	 * Replace each newline (\n) with html break
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function newline_to_br($input) {
		return is_string($input) ? str_replace(array(
			"\n", "\r"
		), '<br />', $input) : $input;
	}

	/**
	 * Truncate a string down to x characters
	 *
	 * @param string $input
	 * @param int $characters
	 *
	 * @return string
	 */
	public static function truncate($input, $characters = 100) {
		if (is_string($input) || is_numeric($input)) {
			if (strlen($input) > $characters) {
				return substr($input, 0, $characters) . '&hellip;';
			}
		}

		return $input;
	}

	/**
	 * Truncate string down to x words
	 *
	 * @param string $input
	 * @param int $words
	 *
	 * @return string
	 */
	public static function truncatewords($input, $words = 3) {
		if (is_string($input)) {
			$wordlist = explode(" ", $input);

			if (count($wordlist) > $words) {
				return implode(" ", array_slice($wordlist, 0, $words)) . '&hellip;';
			}
		}

		return $input;
	}

	/**
	 * Removes html tags from text
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public static function strip_html($input) {
		return is_string($input) ? strip_tags($input) : $input;
	}

	/**
	 * Joins elements of an array with a given character between them
	 *
	 * @param array $input
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function join($input, $glue = ' ') {
		return is_array($input) ? implode($glue, $input) : $input;
	}

	/**
	 * Formats a date using strftime
	 *
	 * @param mixed $input
	 * @param string $format
	 *
	 * @return string
	 */
	public static function date($input, $format) {
		if (!is_numeric($input)) {
			$input = strtotime($input);
		}

		if ($format == 'r')
			return date($format, $input);

		return strftime($format, $input);

	}

	/**
	 * Returns the first element of an array
	 *
	 * @param array $input
	 *
	 * @return mixed
	 */
	public static function first($input) {
		return is_array($input) ? reset($input) : $input;
	}

	/**
	 * Returns the last element of an array
	 *
	 * @param array $input
	 *
	 * @return mixed
	 */
	public static function last($input) {
		return is_array($input) ? end($input) : $input;
	}

	/**
	 * Split input string into an array of substrings separated by given pattern.
	 *
	 * @param string $input
	 * @param string $pattern
	 *
	 * @return array
	 */
	public static function split($input, $pattern) {
		return explode($pattern, $input);
	}

	/**
	 * addition
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function plus($input, $operand) {
		return (int)$input + (int)$operand;
	}

	/**
	 * subtraction
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function minus($input, $operand) {
		return (int)$input - (int)$operand;
	}

	/**
	 * multiplication
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function times($input, $operand) {
		return (int)$input * (int)$operand;
	}

	/**
	 * division
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function divided_by($input, $operand) {
		return (int)$input / (int)$operand;
	}

	/**
	 * modulo
	 *
	 * @param int $input
	 * @param int $operand
	 *
	 * @return int
	 */
	public static function modulo($input, $operand) {
		return (int)$input % (int)$operand;
	}
}
