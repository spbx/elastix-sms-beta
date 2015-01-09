<?php
	// Unicode String Class
	// Copyright (C) 2009 CentralNic Ltd
	//
	// This library is free software; you can redistribute it and/or
	// modify it under the terms of the GNU Library General Public
	// License as published by the Free Software Foundation; either
	// version 2 of the License, or (at your option) any later version.
	// 
	// This library is distributed in the hope that it will be useful,
	// but WITHOUT ANY WARRANTY; without even the implied warranty of
	// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	// Library General Public License for more details.
	// 
	// You should have received a copy of the GNU Library General Public
	// License along with this library; if not, write to the 
	// Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
	// Boston, MA  02111-1307  USA.
	//
	// $Id: String.php,v 1.21 2009/01/19 10:43:59 gavin Exp $

	/**
	* @package php-Unicode
	*/

	/**
	* A string in PHP is a sequence of bytes. In ASCII, each byte
	* corresponds to a single character. In Unicode, a character
	* is represented by a <em>code point</em>, which is just an 
	* integer: therefore any Unicode string is a sequence of integers
	* representing characters. The Unicode_String class encapsulates
	* such sequences and provides some string-like methods for manipulating
	* them.
	* @package php-Unicode
	* @copyright (c) 2009 CentralNic Ltd.
	* @link http://labs.centralnic.com/Unicode.php
	*/
	class Unicode_String {

		var $codePoints = array();

		/**
		* constructor.
		* @param array $chars an array of Unicode_Character objects
		*/
		function __construct($chars=array()) {
			if (count($chars) > 0) {
				foreach ($chars as $char) {
					if (!is_a($char, 'Unicode_Character')) die("not a Unicode_Character");
					$this->codePoints[] = $char;
				}
			}
		}

		/**
		* @access private
		*/
		private function _reset() {
			$this->codePoints	= array();
		}

		/**
		* This method treats $str as UTF8-encoded text and populates the object with the corresponding Unicode code points.
		*
		* Since UTF-8 is a superset of ASCII, this method will also work on ASCII strings.
		* @param string $str a string in UTF-8 encoding
		*/
		function fromUTF8($str) {
			$this->_reset();
			$values			= array();
			$lookingFor		= 1;

			require_once(dirname(__FILE__).'/Character.php');

			for ($i = 0 ; $i < strlen($str) ; $i++) {

				$thisValue = ord(substr($str, $i, 1));

				if ($thisValue < 128) {
					$this->codePoints[] = new Unicode_Character($thisValue);

				} else {

					if (count($values) == 0) $lookingFor = ($thisValue < 224 ? 2 : 3);

					$values[] = $thisValue;

					if (count($values) == $lookingFor) {
						if ($lookingFor == 3) {
							$number = ($values[0] % 16) * 4096 + ($values[1] % 64) * 64 + $values[2] % 64;

						} else {
							$number = ($values[0] % 32) * 64 + $values[1] % 64;

						}

						$this->codePoints[]	= new Unicode_Character($number);
						$values			= array();
						$lookingFor		= 1;

					}

				}

			}

			return $this;
		}

		/**
		* This method treats $str as ASCII-encoded text and populates the object with the corresponding Unicode code points.
		*
		* This method is faster than fromUTF8() since it doesn't check
		* the value of each byte in $str, however if any non-ASCII or
		* multi-byte characters are present in $data, they will be
		* broken.
		* @param string $str a string in ASCII encoding
		*/
		function fromASCII($str) {
			$this->_reset();
			for ($i = 0 ; $i < strlen($str) ; $i++) $this->codePoints[] = new Unicode_Character(ord(substr($str, $i, 1)));
			return $this;
		}

		/**
		* @access private
		*/
		private function _findIDN() {
			putenv('PATH='.getenv('PATH').':/usr/local/bin');
			foreach (explode(':', getenv('PATH')) as $dir) {
				$file = sprintf('%s/idn', $dir);
				if (!is_dir($file) && is_executable($file)) {
					$this->idn_binary = $file;
					return true;
				}
			}
			return false;
		}

		/**
		* @access private
		*/
		private function _IDNCmd($cmd, $str) {
			if (!is_executable($this->idn_binary)) if (!$this->_findIDN()) die("Cannot find the idn program in ".getenv('PATH'));
			$cmd = sprintf("CHARSET='UTF-8' %s --quiet --%s '%s'", $this->idn_binary, $cmd, $str);
			return trim(`$cmd`);
		}

		/**
		* This method treats $host as a Punycode encoded domain name (eg xn--xample-hva.eu.com), converts it to UTF-8 and populates $string with the corresponding Unicode code points.
		*
		* This function requires that GNU libidn be installed on the system.
		*
		* Note that the Punycode system requires all encoded hostnames
		* to be lowercase: if $hostname contains any uppercase ASCII
		* characters then they will be automatically lowercased before
		* conversion to Unicode.
		* @param string $host a string in PunyCode encoding
		*/
		function fromPunyCode($host) {
			return $this->fromUTF8($this->_IDNCmd('idna-to-unicode', strToLower($host)));
		}

		/**
		* This method returns an array of Unicode_Character objects. This array may be empty if $string hasn't been populated.
		* @return array
		*/
		function chars() {
			return $this->codePoints;
		}

		/**
		* Returns the Unicode_Character character at the $nth position in the string.
		* @param integer the position in the string
		* @return Unicode_Character
		*/
		function getChar($n) {
			return $this->codePoints[$n];
		}

		/**
		* @param Unicode_Character $char
		* @param integer $offset
		*/
		function setChar($char, $offset) {
			if (!is_a($char, 'Unicode_Character')) die("char must be a Unicode_Character");
			$this->codePoints[intval($offset)] = $char;
			return true;
		}

		/**
		* This method returns the number of characters in the string.
		* @return integer
		*/
		function length() {
			return count($this->chars());
		}

		/**
		* This method returns a a Unicode_String object that represents a subset of the string between location $offset and $offset + $length. The first character is a position 0.
		* @param integer $offset
		* @param integer $length
		* @return Unicode_String
		*/
		function substr($offset, $length) {
			$string = new Unicode_String(array_slice($this->chars(), $offset, $length));
			return $string;
		}

		/**
		* This method returns the string in UTF-8 encoding.
		* @return string
		*/
		function toUTF8() {
			$str = '';
			foreach ($this->chars() as $char) $str .= $char->toUTF8();
			return $str;
		}

		/**
		* This method returns the string in ASCII. Characters with code points outside of the ASCII range (0-127) are represented as question marks ('?', U+003F).
		* @return string
		*/
		function toASCII() {
			$str = '';
			foreach ($this->chars() as $char) $str .= $char->toASCII();
			return $str;
		}

		/**
		* This method returns the string in Punycode. This function requires that GNU libidn be installed on the system.
		*
		* Note that the Punycode system automatically converts hostnames
		* to lowercase, so calling this method on a Unicode_String that
		* contains uppercase characters will produce an all lowercase
		* return value.
		* @return string
		*/
		function toPunyCode() {
			return $this->_IDNCmd('idna-to-ascii', $this->toUTF8());
		}

		/**
		* This method returns the string with all characters encoded in XML (and HTML) entity notation (eg &#1234;).
		*
		* If the (optional) $notASCII is false, then all characters,
		* including those in the ASCII range (0-127) will be encoded.
		* If it's true, then these ASCII characters will remain
		* unencoded.
		* @param boolean $notASCII
		* @return string
		*/
		function toXML($notASCII=true) {
			$str = '';
			foreach ($this->chars() as $char) {
				if (in_array($char->ord(), array(38, 60, 62))) {
					// always encode these:
					$str .= sprintf('&#%d;', $char->ord());

				} elseif ($notASCII == true && $char->ord() < 127) {
					// optionally encode ASCII:
					$str .= $char->toASCII();

				} else {
					// automatically encode everything else:
					$str .= sprintf('&#%d;', $char->ord());

				}
			}
			return $str;
		}

		/**
		* This method appends $arg to the end of $string.
		* @param Unicode_String|Unicode_Character $arg
		*/
		function append($arg) {
			if (is_a($arg, 'Unicode_String')) {
				foreach ($arg->chars() as $char) $this->codePoints[] = $char;

			} elseif (is_a($arg, 'Unicode_Character')) {
				$this->codePoints[] = $arg;

			} else {
				die("Argument to append() must be Unicode_String or Unicode_Character");

			}
		}

		/**
		* This method returns an array containing strings describing all the blocks to which the characters in the string belong. Some of the characters may have no assigned block, in which case the array will contain a member whose value is false.
		* @return array
		*/
		function blocks() {
			$blocks = array();
			foreach ($this->chars() as $char) $blocks[$char->block()]++;
			ksort($blocks, SORT_NUMERIC);
			return array_keys($blocks);
		}

		/**
		* This method returns an array containing strings describing all the scripts to which the characters in the string belong. Some of the characters may have no assigned script, in which case the array will contain a member whose value is false.
		* @return array
		*/
		function scripts() {
			$scripts = array();
			foreach ($this->chars() as $char) $scripts[] = $char->script();
			$scripts = array_unique($scripts);
			sort($scripts, SORT_STRING);
			return $scripts;
		}

		/**
		* This method returns an array of Unicode_String objects created by splitting $string against the character $char.
		*
		* If $limit is non-zero, then the returned array will contain a
		* maximum of $limit elements with the last element containing
		* the rest of $string.
		* @param Unicode_Character $delimiter
		* @param integer $limit
		* @return array
		*/
		function explode($delimiter, $limit=0) {
			if (!is_a($delimiter, 'Unicode_Character')) die("char must be a Unicode_Character");
			$count = 0;
			$parts = array();
			$strings = array();
			foreach ($this->chars() as $this_char) {
				if ($this_char->ord() == $delimiter->ord()) {
					if ($limit == 0) {
						$count++;
						$parts[$count] = array();

					} elseif ($count + 1 < $limit) {
						$count++;

					} else {
						$parts[$count][] = $this_char;

					}

				} else {
					$parts[$count][] = $this_char;

				}
			}
			foreach ($parts as $part) $strings[] = new Unicode_String($part);
			return $strings;
		}

		/**
		* This method returns a new Unicode_String object that represents $string with all its characters converted to upper case.
		*
		* Note that for some scripts converting to uppercase may change
		* the length of the string.
		*
		* The database that contains this information is quite large
		* and is stored in a separate file (Unicode/String/CaseDB.php)
		* that is only loaded when required.
		* @return Unicode_String
		*/
		function toUpper() {
			$upper = new Unicode_String();
			for ($i = 0 ; $i < $this->length() ; $i++) {
				$char = $this->getChar($i);
				if ($char->ord() < 128) {
					$uc = new Unicode_String();
					$uc->fromASCII(strToUpper(chr($char->ord())));
					$upper->append($uc);

				} else {
					require_once(dirname(__FILE__).'/String/CaseDB.php');

					foreach ($GLOBALS['Unicode_String_CaseDB'] as $row) {
						if ($row['lower'][0] == $char->ord()) {
							if (count($row['lower']) == 1) {
								$uc = New Unicode_Character($row['upper']);
								$upper->append($uc);
								break 1;

							} else {
								$replace = 0;
								for ($j = 0 ; $j < count($row['lower']) ; $j++) {
									$tc = $this->getChar($i+$j);
									if (isset($tc) && ($tc->ord() == $row['lower'][$i+$j])) $replace++;
								}

								if ($replace == count($row['lower'])) {
									$upper->append(new Unicode_Character($row['upper']));
									break 1;
								}

							}
						}
					}
				}
			}
			return $upper;
		}

		/**
		* This method returns a new Unicode_String object that represents $string with all its characters converted to lower case.
		*
		* Note that for some languages converting to lowercase may
		* change the length of the string.
		*
		* The database that contains this information is quite large
		* and is stored in a separate file (Unicode/String/CaseDB.php)
		* that is only loaded when required.
		* @return Unicode_String
		*/
		function toLower() {
			$lower = new Unicode_String();
			foreach ($this->chars() as $char) $lower->append($char->toLower());
			return $lower;
		}

		/**
		* This method returns an integer indicating the position of the Unicode_String or Unicode_Character $thing in $string.
		*
		* If $thing does not occur in $string, then false is returned.
		* @param Unicode_String|Unicode_Character $thing
		* @return false|integer
		*/
		function pos($thing) {
			for ($i = 0 ; $i < $this->length() ; $i++) {
				if (is_a($thing, 'Unicode_Character')) {
					$char = $this->getChar($i);
					if ($thing->ord() == $char->ord()) return $i;

				} elseif (is_a($thing, 'Unicode_String')) {
					for ($j = 0 ; $j < $thing->length() ; $j++) {
						$jchar = $thing->getChar($j);
						$ijchar = $this->getChar($i+$j);
						if ($jchar->ord() !== $ijchar->ord()) {
							continue 2;
						}
					}
					return $i;

				} else {
					die("argument to pos() is not a Unicode_Character or a Unicode_String");

				}
			}
			return false;
		}

		/**
		* This method returns a new Unicode_String object with any whitespace characters at the beginning of the string removed.
		* @return Unicode_String
		*/
		function ltrim() {
			$whitespace = array(32, 9, 10, 13, 0, 11);
			for ($i = 0 ; $i < $this->length() ; $i++) {
				$char = $this->getChar($i);
				if (!in_array($char->ord(), $whitespace)) {
					return $this->substr($i, $this->length() - $i);
				}
			}
		}

		/**
		* This method returns a new Unicode_String object with any whitespace characters at the end of the string removed.
		* @return Unicode_String
		*/
		function rtrim() {
			$r = $this->reverse();
			$lt = $r->ltrim();
			return $lt->reverse();
		}

		/**
		* This method returns a new Unicode_String object with any whitespace characters at the beginning and end of the string removed.
		* @return Unicode_String
		*/
		function trim() {
			$lr = $this->ltrim();
			return $lr->rtrim();
		}

		/**
		* This method returns a new Unicode_String object that represents the characters of $string in reverse order.
		* @return Unicode_String
		*/
		function reverse() {
			return new Unicode_String(array_reverse($this->chars()));
		}

		/**
		* @see strcmp()
		* @param Unicode_String $that
		* @return integer
		*/
		function cmp($that) {
			return strcmp($this->toUTF8(), $that->toUTF8());
		}

		/**
		* This method does a comparison like that in $cmp(), except that it is case-insensitive.
		* @see strcmp()
		* @param Unicode_String $that
		* @return integer
		*/
		function caseCmp($that) {
			$s1 = $this->toLower();
			$s2 = $that->toLower();
			return strcmp($s1->toUTF8(), $s2->toUTF8());
		}

		/**
		* this method returns the string in 7-bit safe quoted-printable encoding
		* @see RFC 2045
		* @return string
		*/
		function toQuotedPrintable() {
			// 1. get this string as a sequence of bytes:
			$string = $this->toUTF8();
			// 2. iterate through the bytes:
			$str = '';
			for ($i = 0 ; $i < strlen($string) ; $i++) {
				$chr = substr($string, $i, 1);
				$ord = ord($chr);
				if ($cord >= 33 && $ord <= 126 && $ord !== 61) {
					$str .= $chr;

				} else {
					$str .= sprintf('=%02X', $ord);

				}
			}
			return '=?utf-8?q?'.$str.'?=';
		}
	}

?>
