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

namespace Mee;

/**
 * Main engine class
 * Основной класс движка
 */
class App {
	
	// Initialize application
	// Создаём ядро
	public function __construct() {
		
		// Firstly, error handler
		// Сначала обработчик ошибок
		include_once 'classes/ErrorHandler.php';
		ErrorHandler::Register();
		
		// Second, classes for engine
		// После - классы движка
		self::IncludeAll(__DIR__.'/exceptions');
		self::CheckErrors();
		self::IncludeAll(__DIR__.'/classes');
		self::CheckErrors();
		self::IncludeAll(__DIR__.'/template');
		self::CheckErrors();
		
		// User autoloads
		// Автозагрузка пользователя
		self::IncludeAll(__DIR__.'/../user/autoload');
		self::CheckErrors();
		
		// User routes
		// Пути запросов
		self::IncludeAll(__DIR__.'/../user/routes');
		self::CheckErrors();
		
		// Done! Waiting for query...
		// Готово! Ждём запроса...
	}









	// Main query callback
	// Главный обработчик запроса
	public function Query($q) {
		$out = Router::Go($q);
		self::CheckErrors();
		return $out;
	}
	
	// Create all controllers
	// Загрузка всех обработчиков
	public static function InitControllers() {
		self::IncludeAll(__DIR__.'/../user/controllers');
		self::CheckErrors();
	}

	// Auto-include all files in directory
	// Автоинклуд всех файлов в папке
	private static function IncludeAll($dir) {
		foreach (glob($dir."/*.php") as $scr) {
			include_once $scr;
		}
	}
	
	// Error-check macross for load-time
	// Макрос проверки ошибок на время загрузки
	private static function CheckErrors() {
		if (!ErrorHandler::IsEmpty()) {
			ErrorHandler::Show(); die();
		}
	}
	
}
