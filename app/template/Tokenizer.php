<?php

/*
 * Copyright 2015 Likedar.ru.
 *
 * This is a part of Mee engine. This code is provided as-is,
 * but you are not able to use it outside the Likedar engine.
 *
 * Этот код является частью Mee engine, распространяется как есть,
 * но Вы не можете использовать его вне движка.
 */

namespace Mee\Internal;

/**
 * Internal class, that provides code tokenizing<br>
 * Внутренний класс, разбивающий код на токены
 */
class Tokenizer {
	
	/**
	 * Template beginning marker <br>Символ начала шаблона
	 */
	const TEMPLATE_BEGIN = "[[";
	/**
	 * Template end marker <br>Символ конца шаблона
	 */
	const TEMPLATE_END = "]]";
	
	/**
	 * @var array List of operators and specified token types<br>
	 * Список операторов и связанных с ними токенов
	 */
	static $OPERATORS = array(
		"if" =>			Token::T_IF,
		"elseif" => 	Token::T_ELSEIF,
		"else" =>		Token::T_ELSE,
		"for" =>		Token::T_FOR,
		"foreach" => 	Token::T_FOREACH,
		"forelse" => 	Token::T_FORELSE,
		"while" =>		Token::T_WHILE,
		"to" =>			Token::T_TO,
		"as" =>			Token::T_AS,
		"continue" =>	Token::T_CONTINUE,
		"break" =>		Token::T_BREAK,
		"include" =>	Token::T_INCLUDE,
		"extends" =>	Token::T_EXTENDS,
		"section" =>	Token::T_SECTION,
		"end" =>		Token::T_END
	);
	
	/**
	 * @var array List of symbols and specified token types<br>
	 * Список символов и связанных с ними токенов
	 */
	static $SYMBOLS = array(
		"+" =>	Token::T_ADD,
		"-" =>	Token::T_SUBTRACT,
		"*" =>	Token::T_MULTIPLY,
		"/" =>	Token::T_DIVIDE,
		"!" =>	Token::T_NOT,
		"?" =>	Token::T_QUESTION,
		":" =>	Token::T_COLON,
		";" =>	Token::T_DELIMITER,
		"." =>	Token::T_PERIOD,
		"," =>	Token::T_COMMA,
		"(" =>	Token::T_PARENT_OPEN,
		")" =>	Token::T_PARENT_CLOSE,
		"[" =>	Token::T_BRACKET_OPEN,
		"]" =>	Token::T_BRACKET_CLOSE,
		"<" =>	Token::T_LOWER,
		">" =>	Token::T_GREATER,
		"=" =>	Token::T_ASSIGN,
		"|" =>	Token::T_OR,
		"&" =>	Token::T_AND,
		"%" =>	Token::T_MODULE
	);
	
	/**
	 * @var array List of double symbols and specified token types<br>
	 * Список двойных символов и связанных с ними токенов
	 */
	static $DOUBLESYMBOLS = array(
		">=" =>	Token::T_GREATER_EQUAL,
		"=>" =>	Token::T_GREATER_EQUAL,
		"<=" =>	Token::T_LOWER_EQUAL,
		"=<" =>	Token::T_LOWER_EQUAL,
		"==" =>	Token::T_EQUAL,
		"!=" =>	Token::T_UNEQUAL,
		"||" =>	Token::T_OR,
		"&&" =>	Token::T_AND
	);
	
	
	/**
	 * Tokenize template string, to be compile-ready<br>
	 * Разбивка шаблона в список токенов, готовых для компиляции
	 * 
	 * @param string $code Template code string<br>Строка с кодом шаблона
	 * @return array Token array<br>Массив токенов
	 */
	public static function Tokenize($code) {
		
		// Splitting code list
		// Разбиваем на кодовый список
		$list = self::SplitCode($code);
		
		// Tokenizing list
		// Ищем токены
		$tokens = self::ParseCodeBlocks($list);
		
		
		//echo self::DebugTokenArray($tokens);
		
		// Возвращаем список, готовый к разбору на дерево
		return $tokens;
	}

	/**
	 * Debug output table of tokens<br>
	 * Отладочный вывод списка токенов в виде таблицы
	 * 
	 * @param array $tokens Token array<br>Список токенов
	 * @return string HTML table<br>HTML-таблица
	 */
	public static function DebugTokenArray($tokens) {
		$out = '<table border="1">';
		foreach ($tokens as $t) {
			$out.='<tr><td>'.$t->content.'</td><td><b>'.$t->GetType().'</b></td></tr>';
		}
		return $out.'</table>';
	}

	/**
	 * Transforming text and code to token array<br>
	 * Обработка кода и текста в массив токенов
	 * 
	 * @param array $tree Code regions<br>Части кода
	 * @return array Token array<br>Массив токенов
	 */
	static function ParseCodeBlocks($tree) {
		$out = array();
		
		// Iterating code regions
		// Проходим по разделенному коду
		foreach ($tree as $t) {
			if($t[2]){
				// This is the code
				// Это код - разбиваем
				self::SplitTokens($t[0], $t[1], $out);
			}else{
				// This is plain text
				// Это просто текст
				array_push($out, new Token(Token::T_TEXT, $t[0], $t[1]));
			}
		}
		
		return $out;
	}
	
	/**
	 * Splitting string into tokens<br>
	 * Разбивка строки на токены
	 * 
	 * @param string $code Code string<br>Строка кода
	 * @param int $codePos Position in whole template<br>Позиция строки в общем файле
	 * @param array $tokens Predefined token array<br>Заданный массив токенов
	 */
	static function SplitTokens($code, $codePos, &$tokens) {
		$p = 0;
		// Iterating trough symbols
		// Проходим по символам
		while ($p < strlen($code)) {
			
			if (!ctype_space($code[$p])) {
				
				$s = $code[$p];
				$ds = $s.$code[$p+1];
				
				if(preg_match("/[a-zA-Z\$_]/",$s)){
					// Identifier
					// Идентификатор
					$name = $s;
					for ($i = $p+1; $i < strlen($code); $i++) {
						$s = $code[$i];
						if (preg_match("/[a-zA-Z\$_]/",$s)) {
							$name.=$s;
						}else{
							break;
						}
					}
					if (array_key_exists(strtolower($name), self::$OPERATORS)) {
						array_push($tokens, new Token(
							self::$OPERATORS[strtolower($name)], strtolower($name), $codePos+$p
						));
					}else{
						array_push($tokens, new Token(
							Token::T_VARIABLE, $name, $codePos+$p
						));
					}
					$p+=strlen($name);
				}elseif(preg_match("/[0-9]/",$s)){
					// Number
					// Число
					$num = $s;
					for ($i = $p+1; $i < strlen($code); $i++) {
						$s = $code[$i];
						if (preg_match("/[0-9\.]/",$s)) {
							$num.=$s;
						}else{
							break;
						}
					}
					$adv = strlen($num);
					if (substr($num, 0, 1)==".") {
						$num = "0".$num;
					}
					array_push($tokens, new Token(
						Token::T_NUMBER, intval($num), $codePos+$p
					));
					$p+=$adv;
				}elseif($code[$p]=="\""){
					// String
					// Строка
					$txt = "";
					for ($i = $p+1; $i < strlen($code); $i++) {
						$s = $code[$i];
						if ($s=="\"") {
							break;
						}else{
							$txt.=$s;
						}
					}
					array_push($tokens, new Token(
						Token::T_STRING, $txt, $codePos+$p
					));
					$p+=strlen($txt)+2;
				}elseif($code[$p]=="'"){
					// String
					// Строка
					$txt = "";
					for ($i = $p+1; $i < strlen($code); $i++) {
						$s = $code[$i];
						if ($s=="'") {
							break;
						}else{
							$txt.=$s;
						}
					}
					array_push($tokens, new Token(
						Token::T_STRING, $txt, $codePos+$p
					));
					$p+=strlen($txt)+2;
				}elseif(array_key_exists($ds, self::$DOUBLESYMBOLS)){
					// Double symbol
					// Двойной символ
					array_push($tokens, new Token(
						self::$DOUBLESYMBOLS[$ds], $ds, $codePos+$p
					));
					$p+=2;
				}elseif(array_key_exists($s, self::$SYMBOLS)){
					// Symbol
					// Символ
					array_push($tokens, new Token(
						self::$SYMBOLS[$s], $s, $codePos+$p
					));
					$p++;
				}else{	
					// Unknown symbol
					// Неизвестный символ
					$p++;
				}
			}else{
				$p++;
			}
		}
	}
	
	/**
	 * Splits raw template string to code and plain text<br>
	 * Разбивает строку с шаблоном на код и текст
	 * 
	 * @param string $code Template text<br>Строка шаблона
	 * @return array Code regions<br>Части кода
	 * @throws Exception Parsing error<br>Ошибка при разборе шаблона
	 */
	static function SplitCode($code) {
		$out = array();
		
		// Check for empty code
		// Проверяем на пустой код
		if (strlen($code)==0) {
			return $out;
		}
		
		// Checking brackets mismatch
		// Проверяем несовпадение тегов
		if (substr_count($code, self::TEMPLATE_BEGIN) != substr_count($code, self::TEMPLATE_END)) {
			throw new Exception("Template code markers mismatch");
		}
		
		// Code only contains text
		// Код содержит только текст
		if (substr_count($code, self::TEMPLATE_BEGIN)==0) {
			array_push($out,array(
				$code,
				0,
				false
			));
			return $out;
		}
		
		$pos = 0;
		$slen = strlen(self::TEMPLATE_BEGIN);
		$elen = strlen(self::TEMPLATE_END);
		$s = strpos($code, self::TEMPLATE_BEGIN);
		
		// Iterate trough code
		// Проходим через код
		while (strpos($code, self::TEMPLATE_BEGIN,$pos)!==false){
			$s = strpos($code, self::TEMPLATE_BEGIN, $pos);
			$e = strpos($code, self::TEMPLATE_END, $s);
			
			// We met a text (don't add if null-length)
			// Встречен текст (не добавляем если пустая строка)
			if($s-$pos>0){
				array_push($out, array(
					substr($code, $pos, $s-$pos),
					$pos,
					false
				));
			}
			
			// We met a code piece (same - don't add empty)
			// Мы встретили код - так же, пропускаем если пустой
			if ($e-$s>$slen) {
				array_push($out, array(
					substr($code, $s+$slen, $e-$s-$slen),
					$s+$slen,
					true
				));
			}
			
			// Skip to the next position
			// Идём на следующую позицию
			$pos = $e+$elen;
		}
		
		// Trailing text
		// Не забываем про текст в конце 
		if ($pos<strlen($code)-1) {
			array_push($out, array(
				substr($code, $pos),
				$pos,
				false
			));
		}
		
		// Return our splitted text
		// Возвращаем разбитый текст
		return $out;
	}
	
}

