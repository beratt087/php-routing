<?php

namespace Luadex\Core;

class App {
	public function __construct() {

	}

	public function loadEnv() {
		$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/supplier');
		$dotenv->load();
	}
}
