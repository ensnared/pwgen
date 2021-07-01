<?php

namespace ensnared\pwgen;

use Exception;

class Password {
	/**
	 * Consonants to use. Can include locale specific letters.
	 * @var string[]
	 */
	private static ?array $consonants = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'z');

	/**
	 * Vowels to use. Can include locale specific letters.
	 * @var string[]
	 */
	private static ?array $vowels = array('a', 'e', 'i', 'o', 'u', 'y');

	/**
	 * Number of digits to add to the end of the password.
	 * 0 or NULL = disabled
	 * @var int
	 */
	private static int $digits = 2;

	/**
	 * Number of special characters in generated password.
	 * 0 or NULL = disabled
	 * @var int
	 */
	private static int $numSpecialChars = 2;

	/**
	 * Special characters to use.
	 * @var string[]
	 */
	private static ?array $specialChars = array('!', '@', '#', '$', '%', '&', '*', '-', '+', '?');

	/**
	 * Map of characters that will always be replaced.
	 * Case sensitive.
	 * @var string[]
	 */
	private static ?array $alwaysReplaceChars = array(
		'O' => '0'
	);

	/**
	 * Map of characters that can be replaced.
	 * The percentile chance of each instance being replaced is defined by $warp_chance
	 * @var string[]
	 */
	private static ?array $warpCharactersMap = array(
		'a' => '@',
		'e' => '3',
		'i' => '!',
		'l' => '|',
		'o' => '0',
		's' => '$',
		't' => '+',
		'x' => '%',
		'7' => '/'
	);

	/**
	 * The percentile chance of characters being warped using $character_warp_map
	 * @var int
	 */
	private static int $warpCharactersChance = 75;

	/**
	 * Characters that will have a reduced chance of being used.
	 * The chance of these being used is defined by $rare_characters_chance
	 * Set to NULL or an empty array to disable.
	 * Will also be disabled if $rare_characters_chance is set to 100.
	 * @var string[]
	 */
	private static ?array $rareCharacters = array('l', 'q', 'w', 'x', 'z');

	/**
	 * The percentile chance of rare consonants being used
	 * If set to 0 the characters will never be used.
	 * If set to 100 this functionality will not be used.
	 * @var int
	 */
	private static int $rareCharactersChance = 30;

	/**
	 * Double consonants to use only at the beginning of a password
	 * @var string[]
	 */
	private static ?array $doubleConsonantsFirst = array(
		'bl', 'br',
		'cl', 'cr', 'cv',
		'dr',
		'fl', 'fr', 'fs', 'fj',
		'gl', 'gr',
		'kl', 'kr', 'kj', 'kv',
		'pl', 'pr', 'ps', 'pj',
		'sc', 'sh', 'sk', 'sl', 'sm', 'sn', 'sp', 'st', 'sv', 'sw',
		'tj', 'tr', 'ts', 'tv', 'tw', 'tz',
		'vl', 'vr',
		'wl', 'wr',
		'zk', 'zl', 'zm', 'zn', 'zp', 'zt', 'zv'
	);

	/**
	 * Double consonants to use only if previous letter is a vowel
	 * @var string[]
	 */
	private static ?array $doubleConsonantsAfterVowel = array(
		'ck',
		'dv',
		'fk', 'fp', 'fs', 'ft',
		'gs',
		'lb', 'lc', 'ld', 'lf', 'lg', 'lk', 'lm', 'ln', 'lp', 'ls', 'lv', 'lw', 'lz',
		'md', 'mg', 'ml', 'mn', 'ms', 'mt', 'mx',
		'nd', 'ng', 'nl', 'ns', 'nt', 'nx',
		'pc', 'pk', 'px',
		'rb', 'rd', 'rf', 'rm', 'rn', 'rp', 'rs', 'rv', 'rw', 'rz',
		'tf',
		'vc', 'vd', 'vg', 'vj', 'vl', 'vt', 'vx', 'vz',
		'wd', 'wg', 'wt', 'wx', 'wz',
	);

	/**
	 * Double consonants to use anywhere
	 * If null or empty, this will be populated by all entries from $double_consonants_first and $double_consonants_postvowel
	 * @var string[]
	 */
	private static ?array $doubleConsonantsAnywhere = null;


	/**
	 * Double vowels to use only at the beginning of a password
	 * @var string[]
	 */
	private static ?array $doubleVowelsFirst = array(
		'ai', 'au', 'ay',
		'ei', 'eu', 'ey',
		'io', 'iu',
		'oi',
		'ua', 'uo',
		'ya', 'ye', 'yo', 'yu'
	);

	/**
	 * Double vowels to use only if previous letter is a consonant
	 * @var string[]
	 */
	private static ?array $doubleVowelsAfterConsonant = array(
		'ia', 'ie',
		'oe',
		'ue', 'ui',
	);

	/**
	 * Double vowels to use anywhere
	 * If null or empty, this will be populated by all entries from $double_vowels_first and $double_vowels_postconsonant
	 * @var string[]
	 */
	private static ?array $doubleVowelsAnywhere = null;

	/**
	 * Password minimum length
	 * @var int
	 */
	private static int $minLength = 8;

	/**
	 * Password maximum length
	 * @var int
	 */
	private static int $maxLength = 12;

	/**
	 * Length of generated password, determined from $minLength and $maxLength
	 * @internal
	 * @var int
	 */
	private static int $length = 0;

	/**
	 * The number of words created
	 * @internal
	 * @var int
	 */
	private static int $wordCount = 0;

	/**
	 * Generated password
	 * @internal
	 * @var string
	 */
	private static string $password = '';

	/**
	 * Get list of consonants used in password creation
	 * @return string[]
	 */
	public static function getConsonants(): array {
		return self::$consonants;
	}

	/**
	 * Set list of consonants used in password creation
	 * @param string[]|string $consonants List of letters either as an array or a continuous string (like 'bcdfghjklmnpqrstvwxz')
	 */
	public static function setConsonants($consonants): void {
		if ($consonants && !is_array($consonants)) {
			$consonants = mb_str_split($consonants);
		}
		self::$consonants = $consonants;
	}

	/**
	 * Get list of vowels used in password creation
	 * @return string[]
	 */
	public static function getVowels(): array {
		return self::$vowels;
	}

	/**
	 * Set list of vowels used in password creation
	 * @param string[]|string $vowels List of letters either as an array or a continuous string (like 'aeiouy')
	 */
	public static function setVowels($vowels): void {
		if ($vowels && !is_array($vowels)) {
			$vowels = mb_str_split($vowels);
		}
		self::$vowels = $vowels;
	}

	/**
	 * Get number of digits to add to end of password
	 * @return int
	 */
	public static function getDigits(): int {
		return self::$digits;
	}

	/**
	 * Set number of digits to add to end of password.
	 * Set to 0 to disable.
	 * @param int $digits
	 */
	public static function setDigits(int $digits): void {
		self::$digits = $digits;
	}


	/**
	 * Get number of special characters in a password.
	 * @return int
	 */
	public static function getNumSpecialChars(): int {
		return self::$numSpecialChars;
	}

	/**
	 * Set number of special characters in a password.
	 * Set to 0 to disable.
	 * Note that any special characters defined in alwaysReplaceMap or warpCharactersMap will not be affected by disabling this, but they will counted against the number set here.
	 * @param int $numSpecialChars
	 */
	public static function setNumSpecialChars(int $numSpecialChars): void {
		self::$numSpecialChars = $numSpecialChars;
	}

	/**
	 * Get list of special characters used in password creation
	 * @return string[]
	 */
	public static function getSpecialChars(): array {
		return self::$specialChars;
	}

	/**
	 * Set list of special characters used in password creation
	 * @param string[]|string $specialChars
	 */
	public static function setSpecialChars($specialChars): void {
		if ($specialChars && !is_array($specialChars)) {
			$specialChars = mb_str_split($specialChars);
		}
		self::$specialChars = $specialChars;
	}

	/**
	 * Get map of characters that will always be replaced.
	 * @return string[]
	 */
	public static function getAlwaysReplaceChars(): array {
		return self::$alwaysReplaceChars;
	}

	/**
	 * Set map of characters that will always be replaced.
	 * The default is to always replace O (capital o) with 0 (zero)
	 * @param string[] $alwaysReplaceChars
	 */
	public static function setAlwaysReplaceChars(array $alwaysReplaceChars): void {
		self::$alwaysReplaceChars = $alwaysReplaceChars;
	}

	/**
	 * Get the map of characters being warped (replaced).
	 * @return string[]
	 */
	public static function getWarpCharactersMap(): array {
		return self::$warpCharactersMap;
	}

	/**
	 * Set the map of characters being warped (replaced).
	 * @param string[] $warpCharactersMap
	 */
	public static function setWarpCharactersMap(array $warpCharactersMap): void {
		self::$warpCharactersMap = $warpCharactersMap;
	}

	/**
	 * Get the percentile chance of characters being replaced as defined in $warpCharactersMap
	 * @return int
	 */
	public static function getWarpCharactersChance(): int {
		return self::$warpCharactersChance;
	}

	/**
	 * Set the percentile chance of characters being replaced as defined in $warpCharactersMap.
	 * Set to 0 to disable warping, set to 100 to always warp.
	 * @param int $warpCharactersChance
	 */
	public static function setWarpCharactersChance(int $warpCharactersChance): void {
		self::$warpCharactersChance = $warpCharactersChance;
	}

	/**
	 * Get the list of rare characters that only has a percentage chance of being used.
	 * @return string[]
	 */
	public static function getRareCharacters(): array {
		return self::$rareCharacters;
	}

	/**
	 * Set the list of rare characters that only has a percentage chance of being used.
	 * @param string[]|string $rareCharacters List of letters either as an array or a continuous string (like 'lqwxz')
	 */
	public static function setRareCharacters($rareCharacters): void {
		if ($rareCharacters && !is_array($rareCharacters)) {
			$rareCharacters = mb_str_split($rareCharacters);
		}
		self::$rareCharacters = $rareCharacters;
	}

	/**
	 * Get the chance of rare characters being used
	 * @return int
	 */
	public static function getRareCharactersChance(): int {
		return self::$rareCharactersChance;
	}

	/**
	 * Set the percentile chance of rare characters being used.
	 * Set to 0 to prevent rare characters from ever being used.
	 * Set to 100 to disable the functionality.
	 * @param int $rareCharactersChance
	 */
	public static function setRareCharactersChance(int $rareCharactersChance): void {
		self::$rareCharactersChance = $rareCharactersChance;
	}

	/**
	 * Get the list of double consonants that will only be used in the beginning of a password.
	 * @return string[]
	 */
	public static function getDoubleConsonantsFirst(): array {
		return self::$doubleConsonantsFirst;
	}

	/**
	 * Set the list of double consonants that will only be used in the beginning of a password.
	 * @param string[]|string $doubleConsonantsFirst List of double letters either as an array or a space separated string (like 'bl br cl')
	 */
	public static function setDoubleConsonantsFirst($doubleConsonantsFirst): void {
		if (!is_array($doubleConsonantsFirst)) {
			$doubleConsonantsFirst = explode(' ', $doubleConsonantsFirst);
		}
		self::$doubleConsonantsFirst = $doubleConsonantsFirst;
	}

	/**
	 * Get the list of double consonants that will only be used after a vowel in a password.
	 * @return string[]
	 */
	public static function getDoubleConsonantsAfterVowel(): array {
		return self::$doubleConsonantsAfterVowel;
	}

	/**
	 * Set the list of double consonants that will only be used after a vowel in a password.
	 * @param string[]|string $doubleConsonantsAfterVowel List of double letters either as an array or a space separated string (like 'bl br cl')
	 */
	public static function setDoubleConsonantsAfterVowel($doubleConsonantsAfterVowel): void {
		if (!is_array($doubleConsonantsAfterVowel)) {
			$doubleConsonantsAfterVowel = explode(' ', $doubleConsonantsAfterVowel);
		}
		self::$doubleConsonantsAfterVowel = $doubleConsonantsAfterVowel;
	}

	/**
	 * Get the list of double consonants that can be used anywhere in a password.
	 * @return string[]
	 */
	public static function getDoubleConsonantsAnywhere(): ?array {
		return self::$doubleConsonantsAnywhere;
	}

	/**
	 * Set the list of double consonants that can be used anywhere in a password.
	 * If this is not set, it will be populated by all entries in $doubleConsonantsAfterVowel and $doubleConsonantsAnywhere
	 * @param string[]|string $doubleConsonantsAnywhere List of double letters either as an array or a space separated string (like 'bl br cl')
	 */
	public static function setDoubleConsonantsAnywhere($doubleConsonantsAnywhere): void {
		if (!$doubleConsonantsAnywhere) {
			$doubleConsonantsAnywhere = array_unique(array_merge(self::$doubleConsonantsFirst, self::$doubleConsonantsAfterVowel));
		} else {
			if (!is_array($doubleConsonantsAnywhere)) {
				$doubleConsonantsAnywhere = explode(' ', $doubleConsonantsAnywhere);
			}
		}
		sort($doubleConsonantsAnywhere);
		self::$doubleConsonantsAnywhere = $doubleConsonantsAnywhere;
	}

	/**
	 * Get the list of double vowels that will only be used in the beginning of a password.
	 * @return string[]
	 */
	public static function getDoubleVowelsFirst(): array {
		return self::$doubleVowelsFirst;
	}

	/**
	 * Set the list of double vowels that will only be used in the beginning of a password.
	 * @param string[]|string $doubleVowelsFirst List of double letters either as an array or a space separated string (like 'ai au eu')
	 */
	public static function setDoubleVowelsFirst($doubleVowelsFirst): void {
		if (!is_array($doubleVowelsFirst)) {
			$doubleVowelsFirst = explode(' ', $doubleVowelsFirst);
		}
		self::$doubleVowelsFirst = $doubleVowelsFirst;
	}

	/**
	 * Get the list of double vowels that will only be used after a consonant in a password.
	 * @return string[]
	 */
	public static function getDoubleVowelsAfterConsonant(): array {
		return self::$doubleVowelsAfterConsonant;
	}

	/**
	 * Set the list of double vowels that will only be used after a consonant in a password.
	 * @param string[]|string $doubleVowelsAfterConsonant List of double letters either as an array or a space separated string (like 'ai au eu')
	 */
	public static function setDoubleVowelsAfterConsonant($doubleVowelsAfterConsonant): void {
		if (!is_array($doubleVowelsAfterConsonant)) {
			$doubleVowelsAfterConsonant = explode(' ', $doubleVowelsAfterConsonant);
		}
		self::$doubleVowelsAfterConsonant = $doubleVowelsAfterConsonant;
	}

	/**
	 * Get the list of double vowels that can be used anywhere in a password.
	 * @return string[]
	 */
	public static function getDoubleVowelsAnywhere(): ?array {
		return self::$doubleVowelsAnywhere;
	}

	/**
	 * Set the list of double vowels that can be used anywhere in a password.
	 * If this is not set, it will be populated by all entries in $doubleVowelsAfterConsonant and $doubleVowelsAnywhere
	 * @param string[]|string $doubleVowelsAnywhere List of double letters either as an array or a space separated string (like 'ai au eu')
	 */
	public static function setDoubleVowelsAnywhere($doubleVowelsAnywhere): void {
		if (!$doubleVowelsAnywhere) {
			$doubleVowelsAnywhere = array_unique(array_merge(self::$doubleVowelsFirst, self::$doubleVowelsAfterConsonant));
		} else {
			if (!is_array($doubleVowelsAnywhere)) {
				$doubleVowelsAnywhere = explode(' ', $doubleVowelsAnywhere);
			}
		}
		sort($doubleVowelsAnywhere);
		self::$doubleVowelsAnywhere = $doubleVowelsAnywhere;
	}

	/**
	 * Get the minimum length of generated passwords
	 * @return int
	 */
	public static function getMinLength(): int {
		return self::$minLength;
	}

	/**
	 * Set the minimum length of generated passwords.
	 * Default is 8.
	 * @param int $minLength
	 * @throws Exception
	 */
	public static function setMinLength(int $minLength = 8): void {
		if ($minLength > self::$maxLength) {
			throw new Exception('Minimum length ('.$minLength.') cannot be larger than maximum length ('.self::$maxLength.')');
		}
		self::$minLength = $minLength;
	}

	/**
	 * Set the maximum length of generated passwords.
	 * @return int
	 */
	public static function getMaxLength(): int {
		return self::$maxLength;
	}

	/**
	 * Set the maximum length of generated passwords.
	 * Default is 12.
	 * @param int $maxLength
	 * @throws Exception
	 */
	public static function setMaxLength(int $maxLength = 12): void {
		if ($maxLength < self::$minLength) {
			throw new Exception('Maximum length ('.$maxLength.') cannot be smaller than minimum length ('.self::$minLength.')');
		}
		self::$maxLength = $maxLength;
	}

	/**
	 * Static method to create a password using default configuration.
	 *
	 * @param int $minLength Optional minimum password length, default 8
	 * @param int $maxLength Optional maximum password length, default 12
	 * @return string The generated pronounceable password.
	 */
	public static function create(int $minLength = 8, int $maxLength = 12): string {
		if (self::$doubleConsonantsAnywhere === null) {
			self::$doubleConsonantsAnywhere = array_unique(array_merge(self::$doubleConsonantsFirst, self::$doubleConsonantsAfterVowel));
			sort(self::$doubleConsonantsAnywhere);
		}
		if (self::$doubleVowelsAnywhere === null) {
			self::$doubleVowelsAnywhere = array_unique(array_merge(self::$doubleVowelsFirst, self::$doubleVowelsAfterConsonant));
			sort(self::$doubleVowelsAnywhere);
		}
		srand((double)microtime() * 1000000);
		self::$minLength = $minLength;
		self::$maxLength = $maxLength >= $minLength ? $maxLength : $minLength;
		self::_generate();
		return self::$password;
	}

	/**
	 * The actual generator, used both by the instance and static methods for creation
	 * @internal
	 */
	private static function _generate() {
		self::$length = rand(self::$minLength, self::$maxLength);
		$wordLength = self::$length;
		if (self::$digits) {
			$wordLength -= self::$digits;
		}
		self::$password = '';

		while (mb_strlen(self::$password) < $wordLength) {
			self::$password .= self::word();
			self::$wordCount++;
		}
		self::$password = mb_substr(self::$password, 0, $wordLength);

		if (self::$warpCharactersChance > 0) {
			self::$password = self::warp();
		}

		if (is_array(self::$specialChars) && count(self::$specialChars) > 0) {
			self::special();
		}

		if (self::$digits) {
			self::digits();
		}

		if (mb_strlen(self::$password) > self::$maxLength) {
			if (!self::shorten()) {
				// No way to reliably shorten the password, generate a new one and start over.
				self::_generate();
			}
		}
	}

	/**
	 * Shorten password to $maxLength by removing one letter of a double consonant or double vowel
	 * @internal
	 */
	private static function shorten(): bool {
		$chars = array_reverse(mb_str_split(self::$password));
		$prevType = null;
		foreach ($chars as $n => $char) {
			if (in_array($char, self::$consonants)) {
				if ($prevType === 'c') {
					unset($chars[$n]);
					self::$password = implode('', array_reverse($chars));
					return true;
				}
				$prevType = 'c';
			} elseif (in_array($char, self::$vowels)) {
				if ($prevType === 'v') {
					unset($chars[$n]);
					self::$password = implode('', array_reverse($chars));
					return true;
				}
				$prevType = 'v';
			}
		}
		// No double vowels of consonants to trim, return false to trigger a new password generation.
		return false;
	}

	/**
	 * Create a single word
	 * @return string
	 * @internal
	 */
	private static function word(): string {
		$word = '';
		if (self::$wordCount === 0) {
			// First word, 50/50 starts with vowel or consonant
			if (rand(0, 1) === 1) {
				$word .= self::vowel();
			}
			$word .= self::consonant();
		} else {
			if (in_array(mb_substr(self::$password, -1), self::$vowels)) {
				$word .= self::consonant();
			}
		}
		$word .= self::vowel();

		if (self::$wordCount === 0 || rand(1, 100) > 70) {
			$word = mb_convert_case($word, MB_CASE_TITLE);
		}

		return $word;
	}

	/**
	 * Create a vowel, will randomly return a vowel from the defined list of vowels.
	 * @return string
	 * @internal
	 */
	private static function vowel(): string {
		if (rand(0, 1) === 1) {
			if (self::$wordCount === 0) {
				$vowels = self::$doubleVowelsFirst[rand(0, count(self::$doubleVowelsFirst) - 1)];
			} else {
				$vowels = self::$doubleVowelsAnywhere[rand(0, count(self::$doubleVowelsAnywhere) - 1)];
			}
		} else {
			$vowels = self::$vowels[rand(0, count(self::$vowels) - 1)];
		}

		if (self::$rareCharactersChance < 100 && is_array(self::$rareCharacters) && count(self::$rareCharacters) > 0) {
			foreach (mb_str_split($vowels) as $letter) {
				if (in_array($letter, self::$rareCharacters) && rand(1, 100) > self::$rareCharactersChance) {
					return self::vowel();
				}
			}
		}
		return $vowels;
	}

	/**
	 * Create a consonant, will randomly return a consonant or a double consonant.
	 * @return string
	 * @internal
	 */
	private static function consonant(): string {
		if (rand(0, 1) === 1) {
			if (self::$wordCount === 0) {
				$cons = self::$doubleConsonantsFirst[rand(0, count(self::$doubleConsonantsFirst) - 1)];
			} else {
				$cons = self::$doubleConsonantsAnywhere[rand(0, count(self::$doubleConsonantsAnywhere) - 1)];
			}
		} else {
			$cons = self::$consonants[rand(0, count(self::$consonants) - 1)];
		}

		if (self::$rareCharactersChance < 100 && is_array(self::$rareCharacters) && count(self::$rareCharacters) > 0) {
			foreach (mb_str_split($cons) as $letter) {
				if (in_array($letter, self::$rareCharacters) && rand(1, 100) > self::$rareCharactersChance) {
					return self::consonant();
				}
			}
		}
		return $cons;
	}

	/**
	 * Warp the password using $character_always_replace, $character_warp_map and $warp_chance
	 * @return string The warped password
	 * @internal
	 */
	private static function warp(): string {
		$letters = mb_str_split(self::$password);
		$warpedPw = array();
		foreach ($letters as $letter) {
			if (isset(self::$alwaysReplaceChars[$letter])) {
				$warped = self::$alwaysReplaceChars[$letter];
			} elseif (count($warpedPw) !== 0 && isset(self::$warpCharactersMap[mb_strtolower($letter)]) && rand(0, 100) < self::$warpCharactersChance) {
				$warped = self::$warpCharactersMap[mb_strtolower($letter)];
			} else {
				$warped = $letter;
			}
			$warpedPw[] = $warped;
		}
		return implode('', $warpedPw);
	}

	/**
	 * Add a special character to the password
	 * @internal
	 */
	private static function special() {
		$letters = mb_str_split(self::$password);
		$specialCount = 0;
		foreach ($letters as $letter) {
			if (in_array($letter, self::$specialChars)) {
				$specialCount++;
			}
		}

		while ($specialCount < self::$numSpecialChars) {
			self::$password .= self::$specialChars[rand(0, count(self::$specialChars) - 1)];
			$specialCount++;
		}
	}

	/**
	 * Add digits to the password
	 * @internal
	 */
	private static function digits() {
		for ($i = 1; $i <= self::$digits; $i++) {
			self::$password .= rand(0, 9);
		}
	}
}
