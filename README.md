# PHP Pronounceable Password Generator
## Description
This is a simple class for creating random pronounceable passwords. By default it also uses special characters and numbers, but this can be disabled. One can also customize the letter combinations that are allowed, as well as character replacements.

**Note:** This class is not based on, nor does it have anything to do with, other similar projects with the same name.

## Installation
Install with composer:
```bash
composer require ensnared/pwgen
```

## Usage
To create a password with default settings, simply use `Password::create();`.

## Configuration
The behaviour can be configured with the following methods:
- `Password::setConsonants()`
    - Set list of consonants used in password creation.
    - Default is all consonants in the english alphabet.
- `Password::setDigits()`
    - Set number of digits to add to end of password.
    - Default is 2.
- `Password::setDoubleConsonantsAfterVowel()`
    - Set the list of double consonants that will only be used after a vowel in a password.
- `Password::setDoubleConsonantsAnywhere()`
    - Set the list of double consonants that can be used anywhere in a password.
- `Password::setDoubleConsonantsFirst()`
    - Set the list of double consonants that will only be used in the beginning of a password.
- `Password::setDoubleVowelsAfterConsonant()`
    - Set the list of double vowels that will only be used after a consonant in a password.
- `Password::setDoubleVowelsAnywhere()`
    - Set the list of double vowels that can be used anywhere in a password.
- `Password::setDoubleVowelsFirst()`
    - Set the list of double vowels that will only be used in the beginning of a password.
- `Password::setMaxLength()`
    - Set the maximum length of generated passwords.
    - Default is 12
    - Note that this is not a hard limit, in some cases a password might be 1-2 characters longer, depending on wether warping and special characters are in use.
- `Password::setMinLength()`
    - Set the minimum length of generated passwords.
    - Default is 8
    - Note that this is not a hard limit, in some cases a password might be 1-2 characters shorter, depending on wether warping and special characters are in use.
- `Password::setNumSpecialChars()`
    - Set number of special characters in a password.
    - Default is 1.
    - Set to 0 to disable.
    - If warping is used, warped characters that result in special characters will still be warped even if you set this to 0. If not 0, warped special characters will count towards this number.
- `Password::setRareCharacters()`
    - Set the list of rare characters that only has a percentage chance of being used.
    - Default is l, q, w, x, z
- `Password::setRareCharactersChance()`
    - Set the percentile chance of rare characters being used.
    - Default is 30.
    - Set to 0 to prevent rare characters from ever being used.
    - Set to 100 to disable the functionality.
- `Password::setSpecialChars()`
    - Set list of special characters used in password creation.
    - Default is !@#$%&*-+?
- `Password::setVowels()`
    - Set list of vowels used in password creation
    - Default is all vowels in the english alphabet.
- `Password::setWarpCharactersChance()`
    - Set the percentile chance of characters being replaced as defined in $warpCharactersMap.
    - Default is 75.
    - Set to 0 to disable warping.
    - Set to 100 to always warp.
- `Password::setWarpCharactersMap()`
    - Set the map of characters to be warped (replaced).
   
## LICENSE
MIT
