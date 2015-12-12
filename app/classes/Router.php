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
 * Router provides routing for queries
 * Router обрабатывает пути запросов
 */
class Router {
	
	private static $postRoutes = array();
	private static $getRoutes = array();
	private static $routeVars = array();

	// Creates new GET route
	// Создаёт новый GET-маршрут
	public static function Get($path, $controller) {
		if(!array_key_exists($path, self::$getRoutes)){
			self::$getRoutes[$path] = self::ExplodeRoute($path, $controller);
		}else{
			throw new \Exception("GET route already defined: ".$path);
		}
	}
	
	// Creates new POST route
	// Создаёт новый POST-маршрут
	public static function Post($path, $controller) {
		if(!array_key_exists($path, self::$postRoutes)){
			self::$postRoutes[$path] = self::ExplodeRoute($path, $controller);
		}else{
			throw new \Exception("POST route already defined: ".$path);
		}
	}
	
	// Query selected route
	// Обработка маршрута
	public static function Go($route) {
		$routeLib = self::$getRoutes;
		if ($_SERVER['REQUEST_METHOD'] === "POST") {
			$routeLib = self::$postRoutes;
		}
		usort($routeLib, function($a, $b){
			if ($a["size"]==$b["size"]) {
				return 0;
			}
			return ($a["size"]>$b["size"]) ? -1 : 1;
		});
		
		// Query processing
		// Обработка запроса
		$route = array_values(array_filter(explode("/", $route)));
		$routeCount = count($route);
		
		// Searching for route
		// Поиск маршрута
		foreach ($routeLib as $r) {
			if ($routeCount >= $r["size"] && $routeCount <= count($r["route"])) {
				$found = true;
				$vars = array();
				for ($i = 0; $i < $routeCount; $i++) {
					if ($r["route"][$i]["var"]) {
						$vars[$r["route"][$i]["key"]] = $route[$i];
					}else{
						if ($route[$i]!=$r["route"][$i]["key"]) {
							$found = false;
							break;
						}
					}
				}
				if($found){
					self::$routeVars = $vars;
					return self::CallController($r["call"]);
				}
			}
		}
		
		return "404";
		
	}


	// Detecting route hierarchy
	// Разбор иерархии запроса
	private static function ExplodeRoute($p, $caller) {
		$arr = explode("/", $p);
		$r = array();
		$r["size"] = 0;
		$r["call"] = $caller;
		$path = array();
		$optmode = false;
		foreach ($arr as $v){
			if ($v!="") {
				if(substr($v, 0, 1)=="{"){
					$optional = substr($v, 1, 1)=="?";
					if ($optmode!=$optional) {
						if (!$optmode) {
							$optmode = true;
						}else{
							throw new \Exception("Sequence of optional parameters is broken by non-optional parameter: ".$p);
						}
					}
					array_push($path, array(
						'key' => substr($v, $optional ? 2 : 1, -1),
						'opt' => $optional,
						'var' => true
					));
					if (!$optional) {
						$r["size"] ++;
					}
				}else{
					array_push($path, array(
						'key' => $v,
						'opt' => false,
						'var' => false
					));
					$r["size"]++;
				}
			}
		}
		$r["route"] = $path;
		return $r;
	}
	
	// Call controller
	// Вызов необходимого контроллера
	private static function CallController($controller) {
		
		$out = "";
		
		App::InitControllers();
		
		if(is_callable($controller)){
			$out = $controller();
		}else{
			if(strpos($controller, "@")!==false){
				$parts = explode("@", $controller);
				if (count($parts)!=2) {
					throw new \Exception("Illegal controller definition: ".$controller);
				}
				if (class_exists($parts[0])) {
					$obj = new $parts[0];
					if(method_exists($obj, $parts[1])){
						$out = call_user_func(array($obj, $parts[1]));
					}else{
						throw new \Exception("Class \"".$parts[0]."\" doesn't have definition of a controller method: ".$parts[1]);
					}
				}else{
					throw new \Exception("Controller class doesn't exist: ".$parts[0]);
				}
			}else{
				if (function_exists($controller)) {
					$out = call_user_func($controller);
				}else{
					throw new \Exception("Controller function not found: ".$controller);
				}
					
			}
		}
		
		if (is_array($out)) {
			$out = json_encode($out);
		}
		return $out;
		
	}
	
	
}
