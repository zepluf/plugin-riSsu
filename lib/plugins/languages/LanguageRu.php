<?php

namespace plugins\riSsu\plugins\languages;

use plugins\riPlugin\Plugin;

use plugins\riSsu\cores\Language;

/**
 * @package Pages
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: default.php 149 2009-03-04 05:23:35Z yellow1912 $
 */
// note: we can later move part of this function into sub-functions, which we can store in the base class.
class LanguageRu extends Language{
	protected $cyrillic = array("ж",  "ё",  "й", "ю",  "ь", "ч",  "щ",  "ц", "у", "к", "е", "н", "г", "ш",  "з", "х", "ъ", "ф", "ы", "в", "а", "п", "р", "о", "л", "д", "э", "я",  "с", "м", "и", "т", "б", "Ё",  "Й", "Ю",  "Ч",  "Ь", "Щ",  "Ц", "У", "К", "Е", "Н", "Г", "Ш",  "З", "Х", "Ъ", "Ф", "Ы", "В", "А", "П", "Р", "О", "Л", "Д", "Ж",  "Э",  "Я",  "С", "М", "И", "Т", "Б"),
	$translit = array("zh", "yo", "i", "yu", "",  "ch", "sh", "c", "u", "k", "e", "n", "g", "sh", "z", "h", "",  "f", "y", "v", "a", "p", "r", "o", "l", "d", "ye","ya", "s", "m", "i", "t", "b", "yo", "I", "YU", "CH", "",  "SH", "C", "U", "K", "E", "N", "G", "SH", "Z", "H", "",  "F", "Y", "V", "A", "P", "R", "O", "L", "D", "Zh", "Ye", "Ya", "S", "M", "I", "T", "B");
}