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
 * Class that represents one action in compiled template<br>
 * Класс одного действия в скомпилированном шаблоне
 */
class Instruction {
	
	/**
	 * Out instruction<br>Инструкция вывода
	 */
	const T_OUT = 0;
	/**
	 * Short IF - <b>cond ? a : b</b><br>Короткое условие - <b>cond ? a : b</b>
	 */
	const T_SHORT_IF = 1;
	/**
	 * IF statement - <b>if cond</b><br>Условие - <b>if cond</b>
	 */
	const T_IF = 2;
	/**
	 * ELSEIF statement - <b>elseif cond</b><br>Условие <i>ИНАЧЕ ЕСЛИ</i> - <b>if cond</b>
	 */
	const T_ELSEIF = 3;
	/**
	 * ELSE statement - <b>else</b><br>Условие <i>ИНАЧЕ</i> - <b>else</b>
	 */
	const T_ELSE = 4;
	/**
	 * FOR loop - <b>for a to b</b><br>Цикл - <b>for a to b</b>
	 */
	const T_FOR = 5;
	/**
	 * FOREACH loop - <b>foreach arr as val</b><br>Цикл FOREACH - <b>foreach arr as val</b>
	 */
	const T_FOREACH = 6;
	/**
	 * FORELSE loop - <b>forelse arr as val</b><br>Цикл FORELSE - <b>forelse arr as val</b>
	 */
	const T_FORELSE = 7;
	/**
	 * WHILE loop - <b>while cond</b><br>Цикл WHILE - <b>WHILE cond</b>
	 */
	const T_WHILE = 8;
	/**
	 * Template section - <b>section name</b><br>Секция шаблона - <b>section name</b>
	 */
	const T_SECTION = 9;
	/**
	 * Template extension - <b>extends name</b><br>Наследование шаблона - <b>extends name</b>
	 */
	const T_EXTENDS = 10;
	/**
	 * Variable assign<br>Присвоение переменной
	 */
	const T_ASSIGN = 11;
	/**
	 * Emit math data<br>Вывод значения выражения
	 */
	const T_MIXEDOUT = 12;
	/**
	 * Include another template<br>Подключение дополнительного шаблона
	 */
	const T_INCLUDE = 13;
	
	/**
	 * @var array List of constant names<br>Список названий констант
	 */
	private static $names;
	
	
	/**
	 * @var int Instruction type<br>Тип инструкции
	 */
	public $type;

	/**
	 * @var array Array of inner instructions if any<br>Массив вложенных инструкций
	 */
	public $content;
	
	/**
	 * @var string Name, if instruction is section<br>Имя, если инструкция является секцией 
	 */
	public $name;
	
	/**
	 * @var array Condition, as math tree<br>Условие, в виде математического дерева
	 */
	public $condition;
	
	/**
	 * @var array Target condition, as math tree<br>Условие завершения, в виде математического дерева
	 */
	public $target;
	
	/**
	 * Creates new instruction<br>Инициализация новой структуры
	 */
	public function __construct() {
		$this->content = array();
	}
	
	/**
	 * Transform into string<br>Преобразование в строку
	 * @return string
	 */
	public function __toString() {
		return "Instruction [". $this->GetType()."] ".  gettype($this->content);
	}
	
	/**
	 * Get readable instruction type<br>
	 * Получение читаемого типа инструкции
	 * 
	 * @return string Type<br>Тип
	 */
	public function GetType() {
		if (!self::$names) {
			$rc = new \ReflectionClass("\\Mee\\Internal\\Instruction");
			self::$names = array_flip($rc->getConstants());
		}
		if(array_key_exists($this->type, self::$names)){
			return self::$names[$this->type];
		}else{
			return "T_UNKNOWN";
		}
	}
	
}
