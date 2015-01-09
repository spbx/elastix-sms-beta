<?php
	// Unicode Character Class
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
	// $Id: Character.php,v 1.15 2009/01/19 10:43:59 gavin Exp $

	/**
	* @package php-Unicode
	*/

	/**
	* Unicode provides a unique number for every character. So a character
	* in Unicode is really an integer. The Unicode_Character class
	* encapsulates Unicode code points and provides some useful methods for
	* manipulating them.
	* @package php-Unicode
	* @copyright (c) 2009 CentralNic Ltd.
	* @link http://labs.centralnic.com/Unicode.php
	*/
	class Unicode_Character {

		var $codePoint;

		/**
		* constructor. takes an integer code point as an argument
		* @param integer $cp
		*/
		function __construct($cp) {
			$this->codePoint = $cp;
		}

		/**
		* This method returns the character's Unicode code point (ie the integer passed in the constructor).
		* @return integer
		*/
		function ord() {
			return $this->codePoint;
		}

		/**
		* This method returns the character in UTF-8 encoding.
		* @return string
		*/
		function toUTF8() {
			if (!isset($GLOBALS['Unicode_Character_UTF8Cache'][$this->ord()])) {
				if ($this->ord() < 128) {
					$GLOBALS['Unicode_Character_UTF8Cache'][$this->ord()] = chr($this->ord());

				} elseif ($this->ord() < 2048) {
					$GLOBALS['Unicode_Character_UTF8Cache'][$this->ord()] =	chr(192 + (($this->ord() - ($this->ord() % 64)) / 64)) .
												chr(128 + ($this->ord() % 64));
						
				} else {
					$GLOBALS['Unicode_Character_UTF8Cache'][$this->ord()] =	chr(224 + (($this->ord() - ($this->ord() % 4096)) / 4096)) .
												chr(128 + ((($this->ord() % 4096) - ($this->ord() % 64)) / 64)) .
												chr(128 + ($this->ord() % 64));

				}
			}
			return $GLOBALS['Unicode_Character_UTF8Cache'][$this->ord()];
		}

		/**
		* This method returns the character in ASCII.
		*
		* Characters with code points outside of the ASCII range
		* (0-127) are represented as question marks ('?', U+003F).
		* @return string
		*/
		function toASCII() {
			return ($this->ord() < 128 ? chr($this->ord()) : '?');
		}

		/**
		* This method returns a Unicode_String object containing the character.
		* @return Unicode_String
		*/
		function toString() {
			require_once(dirname(__FILE__).'/String.php');
			$string = new Unicode_String(array($this));
			return $string;
		}

		/**
		* This method returns a string containing the name of the character itself.
		*
		* This is an English sentence in uppercase, eg 'LATIN SMALL
		* LETTER C WITH CEDILLA'.
		*
		* The database that contains this information is quite large
		* and is stored in a separate file (Unicode/Character/NameDB.php)
		* that is only loaded when required.
		* @return string
		*/
		function name() {
			require_once(dirname(__FILE__).'/Character/NameDB.php');
			return $GLOBALS['Unicode_Character_NameDB'][$this->ord()];
		}

		/**
		* This method returns a string containing the name of the code block to which the character belongs.
		*
		* This is an English sentence, eg 'Latin-1 Supplement'. If the
		* character's block cannot be found for some reason, this
		* method will return false.
		*
		* The database that contains this information is quite large
		* and is stored in a separate file (Unicode/Character/BlockDB.php)
		* that is only loaded when required.
		* @return string
		*/
		function block() {
			if (!isset($GLOBALS['Unicode_Character_BlockCache'][$this->ord()])) {
				require_once(dirname(__FILE__).'/Character/BlockDB.php');
				foreach ($GLOBALS['Unicode_Character_BlockDB'] as $block) {
					if ($this->ord() >= $block['start'] && $this->ord() <= $block['end']) {
						$GLOBALS['Unicode_Character_BlockCache'][$this->ord()] = $block['name'];
						break;
					}
				}
			}
			return $GLOBALS['Unicode_Character_BlockCache'][$this->ord()];
		}

		/**
		* This method returns a string containing the script to which the character is assigned.
		*
		* This is an English word eg Latin. If the character's script
		* cannot be found for some reason, this method will return false.
		*
		* The concept of a "script" is distinct from a block in that a
		* script can contain characters from multiple blocks: eg, the
		* "Latin" script includes characters from the "Basic Latin",
		* "Latin-1 Supplement", "Latin Extended-A" and "Latin
		* Extended-B" blocks.
		*
		* The database that contains this information is quite large
		* and is stored in a separate file (Unicode/Character/ScriptDB.php)
		* that is only loaded when required.
		* @return string
		*/
		function script() {
			if (!isset($GLOBALS['Unicode_Character_ScriptCache'][$this->ord()])) {
				require_once(dirname(__FILE__).'/Character/ScriptDB.php');
				foreach ($GLOBALS['Unicode_Character_ScriptDB_Map'] as $row) {
					if ($this->ord() >= $row['start'] && $this->ord() <= $row['end']) {
						$GLOBALS['Unicode_Character_ScriptCache'][$this->ord()] = $GLOBALS['Unicode_Character_ScriptDB_Names'][$row['script']];
						break;
					}
				}
			}
			return $GLOBALS['Unicode_Character_ScriptCache'][$this->ord()];
		}

		/**
		* This method returns the lowercase representation of $string.
		*
		* This may be either a single Unicode_Character (eg E =gt; e)
		* or it may be a Unicode_String (eg &#223; =gt; ss).
		*
		* NB: This class does not implement toUpper() because to do so
		* requires knowledge of the string in which the character
		* appears.
		* @return Unicode_String|Unicode_Character
		*/
		function toLower() {
			if (!isset($GLOBALS['Unicode_Character_CaseCache'][$this->ord()])) {
				require_once(dirname(__FILE__).'/String/CaseDB.php');
				foreach ($GLOBALS['Unicode_String_CaseDB'] as $row) {
					if ($row['upper'] == $this->ord()) {
						if (count($row['lower']) > 1) {
							require_once(dirname(__FILE__).'/String.php');
							$lower = new Unicode_String();
							foreach ($row['lower'] as $char) $lower->append(new Unicode_Character($char));

						} else {
							$lower = new Unicode_Character($row['lower'][0]);

						}
						$GLOBALS['Unicode_Character_CaseCache'][$this->ord()] = $lower;
						break;
					}
				}

				// if nothing is found in the DB, use the original character:
				if (!isset($GLOBALS['Unicode_Character_CaseCache'][$this->ord()])) $GLOBALS['Unicode_Character_CaseCache'][$this->ord()] = $this;

			}
			return $GLOBALS['Unicode_Character_CaseCache'][$this->ord()];
		}

		/**
		* This method returns an array of Unicode_Character objects that may be considered homoglyphs of the character.
		*
		* That is, in some typefaces the character may be
		* indistinguishable from other characters.
		*
		* Note that when checking an IDN domain name for the presence
		* of homoglyphs you should convert to lowercase before looking
		* for homoglyphs.
		*
		* The database that contains this information is quite large
		* and is stored in a separate file (Unicode/Character/IDNHomoglyhMap.php)
		* that is only loaded when required. This database should not
		* be considered comprehensive and you may find a number of
		* false negatives.
		* @return array an array of similar characters
		*/
		function homoglyphs() {
			if (!isset($GLOBALS['Unicode_Character_HomoglyphCache'][$this->ord()])) {
				$GLOBALS['Unicode_Character_HomoglyphCache'][$this->ord()] = array();
				require_once(dirname(__FILE__).'/Character/IDNHomoglyphMap.php');
				foreach ($GLOBALS['Unicode_Character_HomoglyphMap'] as $row) {
					if ($row[0] == $this->ord()) {
						$GLOBALS['Unicode_Character_HomoglyphCache'][$this->ord()][] = new Unicode_Character($row[1]);

					} elseif ($row[1] == $this->ord()) {
						$GLOBALS['Unicode_Character_HomoglyphCache'][$this->ord()][] = new Unicode_Character($row[0]);

					}
				}
			}
			return $GLOBALS['Unicode_Character_HomoglyphCache'][$this->ord()];
		}
	}

?>
