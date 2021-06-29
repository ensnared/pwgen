<?php
namespace Ensnared;

class Password {

	/**
	 * Single consonants to use
	 */
	private static $consonants = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x','z');

	/**
	 * Vowels to use
	 */
	private static $vowels = array('a', 'e', 'i', 'o', 'u', 'y');

	/**
	 * Special characters to use
	 */
	private static $special = array('!', '@', '#', '$', '%', '*', '&', '*', '-', '+', '?');

	/**
	 * These characters will always be replaced. Case sensitive.
	 */
	private static $character_always_replace = array(
		'O'=>'0'
	);

	/**
	 * There's a self::$warp_chance % chance that these letters will be changed to the corresponding character
	 */
	private static $character_warp_map = array(
		'a'=>'@',
		'e'=>'3',
		'i'=>'!',
		'l'=>'|',
		'o'=>'0',
		's'=>'$',
		't'=>'+',
		'x'=>'%',
		'7'=>'/'
	);

	/**
	 * The percentile chance of characters being warped with self::$character_warp_map
	 */
	private static $warp_chance = 75;

	/**
	 * Consonants that will have a self::$rare_consonants_chance % chance of being used
	 */
	private static $rare_consonants = array('l', 'w', 'x', 'z');

	/**
	 * The percentile chance of rare consonants being used
	 */
	private static $rare_consonants_chance = 20;

	/**
	 * Double consonants to use only if at the beginning of the password
	 */
	private static $double_consonants = array(
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
		'zk','zl', 'zm', 'zn', 'zp', 'zt', 'zv'
	);

	/**
	 * Double consonants to use only if previous letter is a vowel
	 */
	private static $double_consonants_postvowel = array(
		'ck',
		'dv',
		'fk', 'fp', 'fs', 'ft',
		'gs',
		'lb', 'lc',  'ld', 'lf', 'lg', 'lk', 'lm', 'ln', 'lp', 'ls', 'lv', 'lw', 'lz',
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
	 * This simply combines self::$double_consonants and self::$double_consonants_postvowel
	 */
	private static $double_consonants_any = array();

	/**
	 * @var int The number of words created
	 */
	private static $wordCount = 0;

	/**
	 * @var string Generated password
	 */
	private static $password = '';

	/**
	 * Create a password
	 */
	public static function create($minLength = 8, $maxLength = 12) {
		self::$double_consonants_any = array_unique(array_merge(self::$double_consonants, self::$double_consonants_postvowel));
		srand((double)microtime() * 1000000);

		$length = rand($minLength, $maxLength);

		while (mb_strlen(self::$password) < $length-2) {
			self::$password .= self::word();
			self::$wordCount++;
		}

		$letters = mb_str_split(self::$password);
//		echo self::$password.PHP_EOL;
		$warpedPw = array();
		$numcount = 0;
		$specialcount = 0;
		foreach ($letters as $letter) {
			if (isset(self::$character_always_replace[$letter])) {
				$warped = self::$character_always_replace[$letter];
			} elseif (count($warpedPw) !== 0 && isset(self::$character_warp_map[mb_strtolower($letter)]) && rand(1, 100) > self::$warp_chance) {
				$warped = self::$character_warp_map[mb_strtolower($letter)];
			} else {
				$warped = $letter;
			}
			if (is_numeric($warped)) {
				$numcount++;
			} elseif (in_array($warped, self::$special)) {
				$specialcount++;
			}
			$warpedPw[] = $warped;
		}
		self::$password = implode('', $warpedPw);

		if ($specialcount < 1) {
			self::$password .= self::$special[rand(0, count(self::$special)-1)];
		}
		self::$password .= rand(10, 99);

		return self::$password;
	}

	/**
	 * Create a single word
	 *
	 * @return string
	 */
	private static function word() {
		$word = '';
		if (self::$wordCount === 0) {
			// First word, 50/50 starts with vowel or consonant
			if (rand(0, 1) === 1) {
				$word .= self::vowel();
			}
			$word .= self::consonant();
			$word .= self::vowel();
		} else {
			if (in_array(mb_substr(self::$password, -1), self::$vowels)) {
				$word .= self::consonant();
			}
			$word .= self::vowel();
		}

		if (self::$wordCount === 0 || rand(1, 100) > 70) {
			$word = mb_convert_case($word, MB_CASE_TITLE);
		}

		return $word;
	}

	private static function vowel() {
		return self::$vowels[rand(0, count(self::$vowels)-1)];
	}

	private static function consonant() {
		if (rand(0, 1) === 1) {
			if (self::$wordCount === 0) {
				$cons = self::$double_consonants[rand(0, count(self::$double_consonants)-1)];
			} else {
				$cons = self::$double_consonants_any[rand(0, count(self::$double_consonants_any)-1)];
			}
		} else {
			$cons = self::$consonants[rand(0, count(self::$consonants)-1)];
		}

		foreach (mb_str_split($cons) as $letter) {
			if (in_array($letter, self::$rare_consonants)) {
				if (rand(1, 100) > self::$rare_consonants_chance) {
					return self::consonant();
				}
			}
		}
		return $cons;
	}

}
