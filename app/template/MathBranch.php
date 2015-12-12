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
 * Class that describes math action<br>
 * Класс, описывающий ветвь вычислений
 */
class MathBranch {
	/**
	 * Variable<br>Переменная
	 */
	const T_VARIABLE = 1;
	/**
	 * Number or string<br>Число или строка
	 */
	const T_CONST = 2;
	/**
	 * Sub-branch<br>Под-ветвь
	 */
	const T_SUB = 3;
	/**
	 * Function call<br>Вызов функции
	 */
	const T_CALL = 4;
	/**
	 * Operator<br>Оператор
	 */
	const T_OP = 5;
	
	/**
	 * @var array List of constant names<br>Список названий констант
	 */
	private static $names;
	
	/**
	 * @var array Variable content<br>Данные переменной 
	 */
	public $content;
	/**
	 * @var int Branch type<br>Тип ветви 
	 */
	public $type;
	/**
	 * @var bool Branch should be inverted?<br>Значение должно пройти булево НЕ?
	 */
	public $not;
	/**
	 * @var bool Branch should be negotiated?<br>Значение должно пройти знак минуса?
	 */
	public $negate;


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
		return "MathBranch [". $this->GetType()."] ".  gettype($this->content);
	}

		/**
	 * Get readable instruction type<br>
	 * Получение читаемого типа инструкции
	 * 
	 * @return string Type<br>Тип
	 */
	public function GetType() {
		if (!self::$names) {
			$rc = new \ReflectionClass("\\Mee\\Internal\\MathBranch");
			self::$names = array_flip($rc->getConstants());
		}
		if(array_key_exists($this->type, self::$names)){
			return self::$names[$this->type];
		}else{
			return "T_UNKNOWN";
		}
	}
}
