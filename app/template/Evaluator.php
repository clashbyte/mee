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
 * Template evaluating class<br>
 * Класс выполнения шаблона
 */
class Evaluator {
	
	/**
	 * Execute instruction bytecode (recursive)<br>
	 * Выполнение байткода (рекурсивно)
	 * 
	 * @param array $bcode Instruction array<br>Список инструкций
	 * @return string
	 */
	public static function Run($bcode) {
		$out = "";
		
		// Iterating instruction list
		// Проход по списку инструкций
		foreach ($bcode as $b) {
			
			switch ($b->type) {
				
				case Instruction::T_OUT:
					// Writing text to output
					// Вывод простого текста
					$out .= $b->content;
					break;

				default:
					$out.="[".$b->GetType()."]<br>";
					break;
			}
			
			
		}
		
		return $out;
	}
	
}
