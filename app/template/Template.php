<?php

/*
 * Copyright 2015 Likedar.ru.
 *
 * This is a part of Likedar engine. This code is provided as-is,
 * but you are not able to use it outside the Mee engine.
 *
 * Этот код является частью Mee engine, распространяется как есть,
 * но Вы не можете использовать его вне движка.
 */


namespace Mee;

use Mee\Internal\Tokenizer;
use Mee\Internal\Compiler;
use Mee\Internal\Evaluator;
use Mee\Internal\Instruction;
use Mee\Exceptions\TemplateException;

/**
 * Template engine
 * Движок шаблонов
 */
class Template {
	
	/**
	 * Cached templates<br>Кешированые шаблоны
	 */
	private static $cached = array();
	
	// Get template source
	// Получение текста шаблона
	
	/**
	 * Get result from template execution<br>
	 * Получение результата выполнения шаблона
	 * 
	 * @param string $name Path to template<br>Путь к шаблону
	 * @param array $vars Associative arrays with variables<br>Ассоциативный массив со значениями переменных
	 * @return string Executed template<br>Выполненый шаблон
	 */
	public static function Get($name, $vars) {
		
		// Get cached bytecode
		// Получение байткода
		$bcode = self::GetBytecode($name);
		
		// Evaluate it
		// Выполнение
		return self::Execute($bcode);
	}

	/**
	 * Get bytecode for template file<br>
	 * Получение байткода для файла шаблона
	 * 
	 * @param string $name Template path<br>Путь к шаблону
	 * @return \Mee\Internal\Instruction Root bytecode instruction<br>Корневая инструкция файла
	 * @throws TemplateException File not found<br>Файл не найден
	 */
	static function GetBytecode($name) {
		
		// Check cache
		// Проверка кеша
		if (!array_key_exists($name, self::$cached)) {
			$path = realpath(__DIR__."/../../view/".implode("/", explode(".", $name)).".tpl");
			
			// Get file
			// Получение файла
			if (!is_file($path)) {
				throw new TemplateException("Template not found: ".$name." ".$path);
			}
			$txt = file_get_contents($path);
			
			// Tokenize it
			// Разбор на токены
			$tokens = Tokenizer::Tokenize($txt);
			
			// Compiling
			// Компиляция
			$bcode = Compiler::Compile($tokens);
			
			// Cache bytecode
			// Кешируем байткод
			self::$cached[$name] = $bcode;
		}
		return self::$cached[$name];
	}
	
	/**
	 * Executing root instruction<br>
	 * Исполнение корневой инструкции
	 * 
	 * @param array $bytecode Bytecode<br>Байткод
	 */
	static function Execute($bytecode) {
		
		// Preprocess template
		// Препроцессинг кода
		$sections = array();
		$b = $bytecode;
		
		while (true) {
			
			$ext = false;
			$exfile = "";
			
			// Search for EXTENDS
			// Поиск EXTENDS
			foreach ($b->content as $in) {
				if ($in->type == Instruction::T_EXTENDS) {
					$exfile = $in->content;
					$ext = true;
					break;
				}
			}
			
			if ($ext) {
				// File needs parent template
				// Файл нуждается в родительском шаблоне
				foreach ($b->content as $in) {
					if ($in->type == Instruction::T_SECTION) {
						$sections[$in->name] = $in->content;
					}
				}
				$b = self::GetBytecode($exfile);
				
			}else{
				// File is standalone
				// Файл самодостаточен
				$bytecode = array();
				foreach ($b->content as $in) {
					if ($in->type == Instruction::T_SECTION) {
						// Copy section variables
						// Копирование секций
						$data = $in->content;
						if (array_key_exists($in->name, $sections)) {
							$data = $sections[$in->name];
						}
						foreach ($data as $cp) {
							array_push($bytecode, $cp);
						}
					}else{
						array_push($bytecode, $in);
					}
				}
				break;
			}
		}
		
		return Evaluator::Run($bytecode);
		
	}
	
}




