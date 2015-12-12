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

include_once '../app/App.php';

$engine = new Mee\App();

echo $engine->Query(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

