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

/**
 * Custom error handler
 * Ручной обработчик ошибок
 */
class ErrorHandler {
	
	// Full error list
	// Полный список ошибок
	private static $errorStack = array();
	
	// Register callback for errors
	// Регистрируем обработчик
	public static function Register() {
		//set_error_handler(array('\Likedar\ErrorHandler', 'HandleError'));
	}

	// Add new error to stack
	// Добавляем ошибку в стек
	public static function HandleError($errno, $errstr, $errfile, $errline) {
		array_push(self::$errorStack, array(
			'level' => $errno,
			'message' => $errstr,
			'file' => $errfile,
			'line' => $errline
		));
		return true;
	}
	
	// Check if errors happened
	// Проверка, произошли ли ошибки
	public static function IsEmpty() {
		return count(self::$errorStack)==0;
	}
	
	// Show errors page
	// Вывод страницы ошибок
	public static function Show() {
		
		
		
	}
	
	
}
