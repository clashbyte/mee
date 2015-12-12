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

use \Mee\Router;
use \Mee\Template;

Router::Get("/", function (){
	
	return Template::Get("admin.test", array());
	
});
