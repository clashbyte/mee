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
use Mee\Exceptions\CompilerException;


/**
 * Compiler class builds executable instruction tree<br>
 * Класс компилятора строит дерево инструкций
 */
class Compiler {
	
	/**
	 * Compiling token list into instruction tree<br>
	 * Компиляция списка токенов в дерево инструкций
	 * 
	 * @param array $tokenList List of tokens<br>Список токенов
	 * @return \Mee\Internal\Instruction Root instruction<br>Базовая инструкция
	 * @throws CompilerException Compilation produced an error<br>Произошла ошибка при компиляции
	 */
	public static function Compile($tokenList) {
		
		$tokens = self::CopyTokens($tokenList);
		
		$stateStack = array();
		$instrStack = array();
		$depth = 0;
		$instr = new Instruction();
		$state = self::GetCleanState();
		$extends = false;
		$p = 0;
		
		while ($p < count($tokens)) {
			
			switch ($tokens[$p]->type) {
				
				case Token::T_DELIMITER:
					// Legacy - skip it
					// Обратная совместимость - пропускаем
					$p++;
					break;
				
				case Token::T_TEXT:
					// Output instruction
					// Инструкция вывода
					$t = $tokens[$p];
					$in = new Instruction();
					$in->type = Instruction::T_OUT;
					$in->content = $t->content;
					array_push($instr->content, $in);
					
					$p++;
					break;
				
				case Token::T_VARIABLE:
					// Variable or math output
					// Переменная или математическое выражение
					$g = $p;
					$var = self::GetVariable($g, $tokens);
					
					if ($g < count($tokens) && ($tokens[$g+1]->type == Token::T_ASSIGN)) {
						// Variable assign
						// Присвоение переменной
						$p = $g+2;
						$assign = self::GetMath($p, $tokens);
						$p++;
						$op = new Instruction();
						$op->type = Instruction::T_ASSIGN;
						$op->content = array(
							$var,
							$assign
						);
						array_push($instr->content, $op);
					}else{
						// Emitting variable
						// Вывод переменной
						$op = new Instruction();
						$op->type = Instruction::T_MIXEDOUT;
						$op->content = self::GetMath($p, $tokens);
						array_push($instr->content, $op);
						$p++;
					}
					break;
				
				case Token::T_NUMBER:
				case Token::T_STRING:
				case Token::T_NOT:
				case Token::T_SUBTRACT:
				case Token::T_BRACKET_OPEN:
					// Math output
					// Вывод переменной
					$op = new Instruction();
					$op->type = Instruction::T_MIXEDOUT;
					$op->content = self::GetMath($p, $tokens);
					array_push($instr->content, $op);
					$p++;
					break;
				
				case Token::T_EXTENDS:
					// Template extension
					// Расширение шаблона
					
					// Unexpected EXTENDS
					// Неожиданный EXTENDS
					if ($depth>0) {
						throw new CompilerException("Unexpected token EXTENDS, not allowed in any scopes");
					}
					if ($extends) {
						throw new CompilerException("Unexpected token EXTENDS, allready called before");
					}
					$p++;
					if ($tokens[$p]->type != Token::T_STRING) {
						throw new CompilerException("Unexpected token ".$tokens[$p]->GetType().", STRING expected");
					}
					
					// Register instruction
					// Сохраняем инструкцию
					$op = new Instruction();
					$op->type = Instruction::T_EXTENDS;
					$op->content = $tokens[$p]->content;
					array_push($instr->content, $op);
					$extends = true;
					
					$p++;
					break;
				
				case Token::T_SECTION:
					// Template section
					// Секция шаблона
					
					// Unexpected SECTION
					// Неожиданный SECTION
					if ($depth>0) {
						throw new CompilerException("Unexpected token SECTION, not allowed in any scopes");
					}
					$p++;
					if ($tokens[$p]->type != Token::T_STRING) {
						throw new CompilerException("Unexpected token ".$tokens[$p]->GetType().", STRING expected");
					}
					
					$in = new Instruction();
					$in->type = Instruction::T_SECTION;
					$in->name = $tokens[$p]->content;
					array_push($instr->content, $in);
					
					// State stack
					// Стек состояния
					array_push($stateStack, $state);
					$state = self::GetCleanState();
					array_push($instrStack, $instr);
					$depth++;
					$instr = $in;
					$p++;
					break;
					
				case Token::T_INCLUDE:
					// Including template file
					// Подключение шаблона
					
					// Unexpected SECTION
					// Неожиданный SECTION
					$p++;
					if ($tokens[$p]->type != Token::T_STRING) {
						throw new CompilerException("Unexpected token ".$tokens[$p]->GetType().", STRING expected");
					}
					
					$in = new Instruction();
					$in->type = Instruction::T_INCLUDE;
					$in->name = $tokens[$p]->content;
					array_push($instr->content, $in);
					
					$p++;
					break;
				
				/* =========================================== */
				case Token::T_IF:
					// IF - statement
					// Условие IF
					$p++;
					
					$in = new Instruction();
					$in->condition = self::GetMath($p, $tokens);
					$in->type = Instruction::T_IF;
					array_push($instr->content, $in);
					
					// State stack
					// Стек состояния
					$state["if"] = true;
					array_push($stateStack, $state);
					$state = self::GetCleanState();
					array_push($instrStack, $instr);
					$depth++;
					$instr = $in;
					
					$p++;
					break;
				
				case Token::T_ELSEIF:
					// ElseIf - statement
					// Условие ElseIf 
					
					$depth--;
					$instr = array_pop($instrStack);
					$state = array_pop($stateStack);
					$p++;
					
					// Unexpected END
					// Неожиданный END
					if (!$state["if"]) {
						throw new CompilerException("Unexpected token ELSEIF, no IF opened");
					}
					if ($state["else"]) {
						throw new CompilerException("Unexpected token ELSEIF, already met ELSE before");
					}
					
					// New sub-instruction
					// Новая подынструкция
					$in = new Instruction();
					$in->condition = self::GetMath($p, $tokens);
					$in->type = Instruction::T_ELSEIF;
					array_push($instr->content, $in);
					
					// State stack
					// Стек состояния
					array_push($stateStack, $state);
					$state = self::GetCleanState();
					array_push($instrStack, $instr);
					$depth++;
					$instr = $in;
					
					$p++;
					break;
				
				case Token::T_ELSE:
					// Else - statement
					// Условие Else 
					
					$depth--;
					$instr = array_pop($instrStack);
					$state = array_pop($stateStack);
					$p++;
					
					// Unexpected END
					// Неожиданный END
					if (!$state["if"]) {
						throw new CompilerException("Unexpected token ELSE, no IF opened");
					}
					
					// New sub-instruction
					// Новая подынструкция
					$in = new Instruction();
					$in->type = Instruction::T_ELSE;
					array_push($instr->content, $in);
					
					// State stack
					// Стек состояния
					array_push($stateStack, $state);
					$state = self::GetCleanState();
					array_push($instrStack, $instr);
					$depth++;
					$instr = $in;
					break;
				
				case Token::T_END:
					// Unexpected END
					// Неожиданный END
					if ($depth==0) {
						throw new CompilerException("Unexpected token END, no states opened");
					}
					
					// Pop states and previous tokens
					$depth--;
					$instr = array_pop($instrStack);
					$state = array_pop($stateStack);
					$state["if"] = false;
					$state["else"] = false;
					$state["forelse"] = false;
					$p++;
					break;
				

				default:
					// Unknown token
					// Неизвестный символ
					throw new CompilerException("Unexpected token: ".$tokens[$p]->GetType()." (".$tokens[$p]->content.")");
			}
			
		}
		return $instr;
	}
	
	/**
	 * Take variable from tokens list<br>
	 * Выборка переменной из списка токенов
	 * 
	 * @param int $pos Current position in list<br>Текущая позиция в списке
	 * @param array $tokens List of tokens<br>Список токенов
	 */
	static function GetVariable(&$pos, &$tokens) {
		$p = $pos;
		if ($tokens[$p]->type != Token::T_VARIABLE) {
			throw new CompilerException("Unexpected token: ".$tokens[$p]->GetType());
		}
		
		$var = array();
		array_push($var, $tokens[$p]);
		$p++;
		
		while ($p<count($tokens)) {
			switch ($tokens[$p]->type) {
				
				// Period dot - variable.field
				// Точка - variable.field
				case Token::T_PERIOD:
					array_push($var, $tokens[$p]);
					$p++;
					if ($tokens[$p]->type != Token::T_VARIABLE) {
						throw new CompilerException("Unexpected token, expected var: ".$tokens[$p]->GetType());
					}
					array_push($var, $tokens[$p]);
					$p++;
					break;
					
				// Array access - variable[field]
				// Доступ как к массиву - variable[field]
				case Token::T_BRACKET_OPEN:
					array_push($var, $tokens[$p]);
					$p++;
					
					array_push($var, self::GetMath($p, $tokens));
					$p++;
					if ($tokens[$p]->type != Token::T_BRACKET_CLOSE) {
						throw new CompilerException("Unexpected token, expected ']': ".$tokens[$p]->GetType()." ".$tokens[$p]->content);
					}
					array_push($var, $tokens[$p]);
					$p++;
					break;

				default:
					$p--;
					break 2;
			}
		}
		
		$pos = $p;
		return $var;
	}

	/**
	 * Take math expression from token list<br>
	 * Выборка математического выражения из списка токенов
	 * 
	 * @param int $pos Current position in list<br>Текущая позиция в списке
	 * @param array $tokens List of tokens<br>Список токенов
	 */
	static function GetMath(&$pos, &$tokens) {
		$state = array(
			"minus" => false,
			"not" => false,
			"mode" => false
		);
		$out = new MathBranch();
		$out->type = MathBranch::T_SUB;
		$p = $pos;
		
		while ($p<count($tokens)){
			
			switch ($tokens[$p]->type) {
				
				// Number and string
				// Строки и числа
				case Token::T_STRING:
				case Token::T_NUMBER:
					if ($state["mode"]) {
						$p--;
						break 2;
					}
					$br = new MathBranch();
					$br->content = $tokens[$p];
					$br->type = MathBranch::T_CONST;
					$br->not = $state["not"];
					$br->negate = $state["minus"];
					array_push($out->content, $br);
					
					$state["mode"] = true;
					$state["minus"] = false;
					$state["not"] = false;
					$p++;
					break;
				
				// Variable or function
				// Переменная или функция
				case Token::T_VARIABLE:
					if ($state["mode"]) {
						$p--;
						break 2;
					}
					
					
					if ($tokens[$p+1]->type == Token::T_PARENT_OPEN) {
						// Parse function
						// Разбор функции
						$var = $tokens[$p];
						$p+=2;
						
						$br = new MathBranch();
						$br->type = MathBranch::T_CALL;
						$br->content = array(
							"func" => $var,
							"p" => array()
						);
						
						// Parsing parameters
						// Разбор параметров
						if ($tokens[$p]->type != Token::T_PARENT_CLOSE) {
							while ($p < count($tokens)) {
								
								// Parameter
								// Параметр
								$math = self::GetMath($p, $tokens);
								array_push($br->content["p"], $math);
								$p++;
								
								// Separator or )
								// Разделитель или )
								switch ($tokens[$p]->type) {
									case Token::T_COMMA:
										$p++;
										break;
									
									case Token::T_PARENT_CLOSE:
										break 2;

									default:
										throw new CompilerException("Unexpected token, expected ',' or ')': ".$tokens[$p]->GetType());
								}
								
							}
						}
						
						if ($tokens[$p]->type != Token::T_PARENT_CLOSE) {
							throw new CompilerException("Unexpected token, expected ')': ".$tokens[$p]->GetType());
						}
						array_push($out->content, $br);
						$p++;
						
					}else{
						// Default variable
						// Обычная переменная
						$state["mode"] = true;
						
						$br = new MathBranch();
						$br->type = MathBranch::T_VARIABLE;
						$br->content = self::GetVariable($p, $tokens);
						$br->negate = $state["minus"];
						$br->not = $state["not"];
						array_push($out->content, $br);
						$p++;
					}
					$state["minus"] = false;
					$state["not"] = false;
					break;
				
				// All operators
				// Операторы
				case Token::T_ADD:
				case Token::T_AND:
				case Token::T_DIVIDE:
				case Token::T_EQUAL:
				case Token::T_GREATER:
				case Token::T_GREATER_EQUAL:
				case Token::T_LOWER:
				case Token::T_LOWER_EQUAL:
				case Token::T_MODULE:
				case Token::T_MULTIPLY:
				case Token::T_OR:
				case Token::T_UNEQUAL:
					if (!$state["mode"]) {
						$p--;
						break 2;
					}
					$br = new MathBranch();
					$br->type = MathBranch::T_OP;
					$br->content = $tokens[$p];
					array_push($out->content, $br);
					$state["mode"] = false;
					$p++;
					break;
					
				// Negate or subtract
				// Минус
				case Token::T_SUBTRACT:
					if ($state["mode"]) {
						$br = new MathBranch();
						$br->type = MathBranch::T_OP;
						$br->content = $tokens[$p];
						array_push($out->content, $br);
						$state["mode"] = false;
						$p++;
					}else{
						if ($state["minus"]) {
							$p--;
							break 2;
						}
						$state["minus"] = true;
						$p++;
					}
					break;
					
				// Not operator
				// Оператор НЕ
				case Token::T_NOT:
					if ($state["mode"] || $state["not"]) {
						$p--;
						break 2;
					}
					$p++;
					break;
					
				// Parenthesis
				// Открывающая скобка
				case Token::T_PARENT_OPEN:
					if ($state["mode"]) {
						$p--;
						break 2;
					}
					$p++;
					
					$br = new MathBranch();
					$br->content = self::GetMath($p, $tokens);
					$br->negate = $state["minus"];
					$br->not = $state["not"];
					$br->type = MathBranch::T_SUB;
					
					array_push($out->content, $br);
					$p++;
					if ($tokens[$p]->type != Token::T_PARENT_CLOSE) {
						throw new CompilerException("Unexpected token, expected ')': ".$tokens[$p]->GetType());
					}
					$p++;
					
					$state["mode"] = true;
					$state["minus"] = false;
					$state["not"] = false;
				default:
					$p--;
					break 2;
			}
		}
		
		$pos = $p;
		return $out;
	}


	
	
	
	
	
	
	public static function DebugCodeTree($tree) {
		$out = "<code>";
		$out.=self::RecursiveDebugTree($tree, 0);
		return $out."</code>";
	}
	static function RecursiveDebugTree($tree, $depth) {
		
		$out = str_repeat("&nbsp;", $depth)."+-- ".(string)$tree."<br>";
		if ($tree->content) {
			if (is_array($tree->content)) {
				foreach ($tree->content as $t) {
					$out.=self::RecursiveDebugTree($t, $depth+2);
				}
			}else{
				if (get_class($tree)!="Mee\\Internal\\Token") {
					$out.=self::RecursiveDebugTree($tree->content, $depth+2);
				}
				
			}
		}
			
		return $out;
	}

	
	
	
	
	
	static function CopyTokens($array) {
		$result = array();
		foreach( $array as $key => $val ) {
			$result[$key] = $val;
		}
		return $result;
	}
	
	static function GetCleanState() {
		return array(
			"if" => false,
			"forelse" => false,
			"else" => false
		);
	}
}
