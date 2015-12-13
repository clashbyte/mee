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
	public static function Run($bcode, $vars) {
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
				
				case Instruction::T_MIXEDOUT:
					$out .= (string)self::EvalMath($b->content, $vars);
					break;

				default:
					$out.="[".$b->GetType()."]<br>";
					break;
			}
			
			
		}
		
		return $out;
	}
	
	
	
	
	static function EvalMath($tree, $vars) {
		
		// Copy instructions
		// Копирование инструкций
		$math = array();
		foreach ($tree->content as $t) {
			array_push($math, $t);
		}
		
		// Call functions and execute parents
		for ($i = 0; $i < count($math); $i++) {
			if ($math[$i]->type == MathBranch::T_SUB) {
				$d = self::EvalMath($math[$i]->content, $vars);
				$op = new MathBranch();
				$op->type = MathBranch::T_CONST;
				$op->negate = $math[$i]->negate;
				$op->not = $math[$i]->not;
				$op->content = new Token(is_string($d)?Token::T_STRING:Token::T_NUMBER, $d, 0);
				$math[$i] = $op;
			}elseif($math[$i]->type == MathBranch::T_CALL){
				$call = 0;
				$op = new MathBranch();
				$op->type = MathBranch::T_CONST;
				$op->negate = $math[$i]->negate;
				$op->not = $math[$i]->not;
				$op->content = new Token(is_string($call)?Token::T_STRING:Token::T_NUMBER, $call, 0);
				$math[$i] = $op;
			}elseif($math[$i]->type == MathBranch::T_VARIABLE){
				$var = self::GetVariable($math[$i], $vars);
				$op = new MathBranch();
				$op->type = MathBranch::T_CONST;
				$op->negate = $math[$i]->negate;
				$op->not = $math[$i]->not;
				$op->content = new Token(is_string($var)?Token::T_STRING:Token::T_NUMBER, $var, 0);
				$math[$i] = $op;
			}
		}
		
		$prior = 0;
		
		while (count($math)>1){
			
			$found = false;
			$ops = array();
			
			switch ($prior){
				case 0:
					// Multiply and divide
					// Умножение и деление
					$ops = array(
						Token::T_MULTIPLY,
						Token::T_DIVIDE,
						Token::T_MODULE
					);
					break;
				
				case 1:
					// Plus and minus
					// Сложение и вычитание
					$ops = array(
						Token::T_ADD,
						Token::T_SUBTRACT
					);
					break;
					
				case 2:
					// Comparison
					// Сравнение
					$ops = array(
						Token::T_GREATER,
						Token::T_GREATER_EQUAL,
						Token::T_LOWER,
						Token::T_LOWER_EQUAL
					);
					break;
				
				case 3:
					// Comparison
					// Сравнение
					$ops = array(
						Token::T_GREATER,
						Token::T_GREATER_EQUAL,
						Token::T_LOWER,
						Token::T_LOWER_EQUAL
					);
					break;
				
				case 4:
					// Comparison
					// Сравнение
					$ops = array(
						Token::T_EQUAL,
						Token::T_UNEQUAL
					);
					break;
				
				case 5:
					// Logical AND
					// Логическое И
					$ops = array(
						Token::T_AND
					);
					break;
				
				case 6:
					// Logical OR
					// Логическое ИЛИ
					$ops = array(
						Token::T_OR
					);
					break;
				
			}
			
			
			// Iterate tokens
			// Проходим по токенам
			for ($p = 0; $p < count($math); $p++){
				if ($math[$p]->type==MathBranch::T_OP) {
					
					if (in_array($math[$p]->content->type, $ops)) {
						$v1 = $math[$p-1]->content->content;
						if ($math[$p-1]->not) {
							$v1 = !$v1;
						}
						if ($math[$p-1]->negate) {
							$v1 = -$v1;
						}
						
						$v2 = $math[$p+1]->content->content;
						if ($math[$p+1]->not) {
							$v2 = !$v2;
						}
						if ($math[$p+1]->negate) {
							$v2 = -$v2;
						}
						
						$val = self::EvalOp($math[$p]->content->type, $v1, $v2);
						$op = new MathBranch();
						$op->type = MathBranch::T_CONST;
						$op->negate = $math[$i]->negate;
						$op->not = $math[$i]->not;
						$op->content = new Token(is_string($val)?Token::T_STRING:Token::T_NUMBER, $val, 0);
						
						$math = array_filter(array_merge(array_slice($math, 0, $p-1), array($op), array_slice($math, $p+2)));
						
						
						$found = true;
						break;
						
					}
					
				}
			}
			
			if (!$found) {
				$prior++;
			}
		}
		
		
		return $math[0]->content->content;
	}
	
	/**
	 * Get variable value<br>
	 * Получение значения переменной
	 * 
	 * @param \Mee\Internal\MathBranch $var Math branch<br>Математическая ветвь
	 * @param array $vars Variables<br>Переменные
	 * @return mixed Variable value<br>Значение переменной
	 */
	static function GetVariable($var, $vars) {
		return 0;
	}
	
	/**
	 * Evaluates single operator<br>
	 * Выполняет один оператор
	 * 
	 * @param int $op Operator type<br>Тип оператора
	 * @param mixed $v1 First number<br>Первое число
	 * @param mixed $v2 Second number<br>Второе число
	 * @return mixed Result<br>Результат
	 */
	static function EvalOp($op, $v1, $v2) {
		
		switch ($op) {
			case Token::T_ADD:
				if (is_string($v1) || is_string($v2)) {
					return $v1 . $v2;
				}else{
					return $v1 + $v2;
				}
				break;
				
			case Token::T_SUBTRACT:
				return (int)$v1 - (int)$v2;
				
			case Token::T_MULTIPLY:
				return (int)$v1 * (int)$v2;
			
			case Token::T_DIVIDE:
				return (int)$v1 / (int)$v2;
			
			case Token::T_MODULE:
				return (int)$v1 % (int)$v2;

			case Token::T_EQUAL:
				return $v1 == $v2;
				
			case Token::T_UNEQUAL:
				return $v1 != $v2;
				
			case Token::T_GREATER:
				return $v1 > $v2;
			
			case Token::T_GREATER_EQUAL:
				return $v1 >= $v2;
			
			case Token::T_LOWER:
				return $v1 < $v2;
				
			case Token::T_LOWER_EQUAL:
				return $v1 <= $v2;;
				
			default:
				break;
		}
		
		
		
		
		return 0;
	}
	
	
}
