<?php
namespace formslib\Utility;

final class Format
{
	/**
	 * Parsed in order, be careful if updating!
	 * (0)11 must be before (0)1 or it will have matched (0)1 already.
	 * Key is the regex pattern, value is an array of space locations
	 */
	private static $_uk_phone_spacing = [
		'^11' => [4, 7],
		'^1[0-9]1' => [4, 7],
		'^13873' => [6],
		'^15242' => [6],
		'^1539[4-6]' => [6],
		'^1697[3|4|7]' => [6],
		'^1768[3|4|7]' => [6],
		'^19467' => [6],
		'^1' => [5],
		'^2' => [3, 7],
		'^3' => [4, 7],
		'^500' => [4, 7],
		'^5' => [5],
		'^7' => [5],
		'^8001111$' => [4],
		'^800[0-9]{6}$' => [4, 7],
		'^8454647$' => [4, 6],
		'^8' => [4, 7],
		'^9' => [4, 7],
	];

	/**
	 * Force casing of names to initial capitals, taking into account McXxx and O'Xxx
	 *
	 * @param string $names
	 * @return string
	 */
	public static function nameCasing($names)
	{
		$n = explode(' ', $names);

		foreach ($n as $index => $name)
		{
			$n2 = explode('-', $name);

			foreach ($n2 as $index2 => $name2)
			{
				$n2[$index2] = ucfirst(strtolower($name2));

				// Handle the clans and the O's
				if (substr($n2[$index2], 0, 2) == 'Mc' || substr($n2[$index2], 0, 2) == "O'")
				{
					$n2[$index2] = substr($n2[$index2], 0, 2) . strtoupper(substr($n2[$index2], 2, 1)) . substr($n2[$index2], 3);
				}
			}

			$n[$index] = implode('-', $n2);
		}

		return implode(' ', $n);
	}

	/**
	 * Strip spacing of phone numbers and replace in line with OFCOM specifications
	 *
	 * @param string $number
	 * @return string
	 */
	public static function phoneUK($number)
	{
		$input = str_replace(' ', '', trim($number));

		if (substr($input, 0, 1) === '0')
		{
			$prefix = '0';
			$uk = substr($input, 1);
		}
		elseif (substr($input, 0, 3) == '+44')
		{
			$prefix = '+44 ';
			$uk = substr($input, 3);
		}
		else
		{
			return $input;
		}

		foreach (self::$_uk_phone_spacing as $key => $value)
		{
			if (preg_match('/'.$key.'/i', $uk))
			{
				foreach (array_reverse($value) as $spacing)
				{
					$uk = substr($uk, 0, $spacing-1).' '.substr($uk, $spacing-1);
				}

				return $prefix.$uk;
			}
		}

		// No matches
		return $prefix.$uk;
	}
}