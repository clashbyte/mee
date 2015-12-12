<?php

/*
 * Copyright 2015 Mikhail Popov.
 *
 * This is a part of Mee framework. This code is provided as-is,
 * but you are not able to use it outside the framework.
 *
 * Этот код является частью Mee framework, распространяется как есть,
 * но Вы не можете использовать его вне фреймворка.
 */

namespace Mee\Internal;

/**
 * Base token class<br>
 * Базовый класс токена
 */
class Token {
	
	/**
	 * Code end <br>Конец кода
	 */
	const T_EOF = -1;
	/**
	 * Plain text<br>Текст
	 */
	const T_TEXT = 0;
	/**
	 * Any number <br>Любое число
	 */
	const T_NUMBER = 1;
	/**
	 * String <br>Строка
	 */
	const T_STRING = 2;
	/**
	 * Template variable <br>Шаблонная переменная
	 */
	const T_VARIABLE = 3;
	/**
	 * Function <br>Функция
	 */
	const T_FUNCTION = 4;
	/**
	 * Symbol <b>;</b><br>Символ разделения <b>;</b>
	 */
	const T_DELIMITER = 5;
	
	/**
	 * Assign - <b>=</b><br>Присвоение - <b>=</b>
	 */
	const T_ASSIGN = 6;
	
	/**
	 * Operator NOT - <b>!</b><br>Оператор НЕ - <b>!</b>
	 */
	const T_NOT = 10;
	/**
	 * Module operator - <b>%</b><br>Оператор остатка - <b>%</b>
	 */
	const T_MODULE = 11;
	/**
	 * Operator AND - <b>AND</b>, <b>&</b>, <b>&&</b><br>Оператор И - <b>AND</b>, <b>&</b>, <b>&&</b>
	 */
	const T_AND = 12;
	/**
	 * Operator OR - <b>OR</b>, <b>|</b>, <b>||</b><br>Оператор ИЛИ - <b>OR</b>, <b>|</b>, <b>||</b>
	 */
	const T_OR = 13;
	/**
	 * Operator EQUAL - <b>==</b><br>Оператор РАВНО - <b>==</b>
	 */
	const T_EQUAL = 14;
	/**
	 * Operator NOT EQUAL - <b>!=</b><br>Оператор НЕ РАВНО - <b>!=</b>
	 */
	const T_UNEQUAL = 15;
	/**
	 * Operator GREATER - <b>&gt;</b><br>Оператор БОЛЬШЕ - <b>&gt;</b>
	 */
	const T_GREATER = 16;
	/**
	 * Operator LOWER - <b>&lt;</b><br>Оператор МЕНЬШЕ - <b>&lt;</b>
	 */
	const T_LOWER = 17;
	/**
	 * Operator GREATER OR EQUAL - <b>&gt;=</b>, <b>=&gt;</b><br>Оператор БОЛЬШЕ ИЛИ РАВНО - <b>&gt;=</b>, <b>=&gt;</b>
	 */
	const T_GREATER_EQUAL = 18;
	/**
	 * Operator LOWER OR EQUAL - <b>&lt;=</b>, <b>=&lt;</b><br>Оператор МЕНЬШЕ ИЛИ РАВНО - <b>&lt;=</b>, <b>=&lt;</b>
	 */
	const T_LOWER_EQUAL = 19;
	
	/**
	 * Sum operator - <b>+</b><br>Оператор сложения - <b>+</b>
	 */
	const T_ADD = 20;
	/**
	 * Subtraction operator - <b>-</b><br>Оператор вычитания - <b>-</b>
	 */
	const T_SUBTRACT = 21;
	/**
	 * Multiplying operator - <b>*</b><br>Оператор умножения - <b>*</b>
	 */
	const T_MULTIPLY = 22;
	/**
	 * Division operator - <b>/</b><br>Оператор деления - <b>/</b>
	 */
	const T_DIVIDE = 23;
	
	/**
	 * Question mark - <b>?</b><br>Знак вопроса - <b>?</b>
	 */
	const T_QUESTION = 30;
	/**
	 * Comma - <b>,</b><br>Запятая - <b>,</b>
	 */
	const T_COMMA = 31;
	/**
	 * Period - <b>.</b><br>Точка - <b>.</b>
	 */
	const T_PERIOD = 32;
	/**
	 * Colon - <b>:</b><br>Двоеточие - <b>:</b>
	 */
	const T_COLON = 33;
	/**
	 * Opening parenthesis - <b>(</b><br>Открытая круглая скобка - <b>(</b>
	 */
	const T_PARENT_OPEN = 34;
	/**
	 * Closing parenthesis - <b>)</b><br>Закрытая круглая скобка - <b>)</b>
	 */
	const T_PARENT_CLOSE = 35;
	/**
	 * Opening bracket - <b>[</b><br>Открытая квадратная скобка - <b>[</b>
	 */
	const T_BRACKET_OPEN = 36;
	/**
	 * Closing bracket - <b>]</b><br>Закрытая квадратная скобка - <b>]</b>
	 */
	const T_BRACKET_CLOSE = 37;
	
	/**
	 * IF statement - <b>if</b><br>Операция ЕСЛИ - <b>if</b>
	 */
	const T_IF = 100;
	/**
	 * ELSE IF statement - <b>elseif</b><br>Операция ИНАЧЕ ЕСЛИ - <b>elseif</b>
	 */
	const T_ELSEIF = 101;
	/**
	 * ELSE statement - <b>else</b><br>Операция ИНАЧЕ - <b>else</b>
	 */
	const T_ELSE = 102;
	/**
	 * FOR loop - <b>for</b><br>Цикл FOR - <b>for</b>
	 */
	const T_FOR = 103;
	/**
	 * FOREACH loop - <b>foreach</b><br>Цикл FOREACH - <b>foreach</b>
	 */
	const T_FOREACH = 104;
	/**
	 * FORELSE loop - <b>forelse</b><br>Цикл FORELSE - <b>forelse</b>
	 */
	const T_FORELSE = 105;
	/**
	 * WHILE loop - <b>while</b><br>Цикл WHILE - <b>while</b>
	 */
	const T_WHILE = 106;
	/**
	 * TO loop op - <b>to</b><br>Операция TO - <b>to</b>
	 */
	const T_TO = 107;
	/**
	 * AS array op - <b>as</b><br>Операция AS - <b>as</b>
	 */
	const T_AS = 108;
	/**
	 * CONTINUE loop op - <b>continue</b><br>Операция CONTINUE - <b>continue</b>
	 */
	const T_CONTINUE = 110;
	/**
	 * BREAK loop - <b>break</b><br>Операция BREAK - <b>break</b>
	 */
	const T_BREAK = 110;
	/**
	 * Include file - <b>include</b><br>Подключение файла - <b>include</b>
	 */
	const T_INCLUDE = 111;
	/**
	 * Section - <b>section</b><br>Секция файла - <b>section</b>
	 */
	const T_SECTION = 112;
	/**
	 * Template extension - <b>extends</b><br>Расширение шаблона - <b>extends</b>
	 */
	const T_EXTENDS = 113;
	/**
	 * Block ending - <b>end</b><br>Конец блока - <b>end</b>
	 */
	const T_END = 200;
	
	
	/**
	 * @var array List of constant names<br>Список названий констант
	 */
	private static $names;
	
	
	
	/**
	 * @var mixed Token content<br>Содержимое токена
	 */
	public $content;
	/**
	 * @var int Token type<br>Тип токена
	 */
	public $type;
	/**
	 * @var int Position in file<br>Позиция в файле
	 */
	public $position;
	
	/**
	 * Initialize new token<br>
	 * Создание нового токена
	 * 
	 * @param string $type Token type<br>Тип токена
	 * @param int $data Token data<br>Содержимое токена
	 * @param int $pos Token position in file<br>Место токена в файле
	 */
	public function __construct($type, $data, $pos) {
		$this->content = $data;
		$this->position = $pos;
		$this->type = $type;
	}
	
	/**
	 * Transform into string<br>Преобразование в строку
	 * @return string
	 */
	public function __toString() {
		return "Token [". $this->GetType()."] ".  substr($this->content, 0, 50);
	}
	
	/**
	 * Get readable token type<br>
	 * Получение читаемого типа токена
	 * 
	 * @return string Type<br>Тип
	 */
	public function GetType() {
		if (!self::$names) {
			$rc = new \ReflectionClass("\\Mee\\Internal\\Token");
			self::$names = array_flip($rc->getConstants());
		}
		if(array_key_exists($this->type, self::$names)){
			return self::$names[$this->type];
		}else{
			return "T_UNKNOWN";
		}
	}
	
}


