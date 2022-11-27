<?php

/**
 * @param string $name
 * @param array  $param
 *
 * @return string
 */
function route(string $name, array $param = []): string {
	return \Luadex\Core\Route::url($name, $param);
}

/**
 * @param string $name
 * @param array  $data
 *
 * @return string
 */
function view(string $name, array $data = []): string {
	return \Luadex\Core\View::view($name, $data);
}