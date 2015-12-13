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
header("Content-Type: text/plain");
use \Mee\Router;
use \Mee\Template;

Router::Get("/", function (){
	
	return Template::Get("admin.test", array());
	
});
