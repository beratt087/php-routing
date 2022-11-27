<?php

namespace Luadex\Core;

use Luadex\Core\Utility\Redirect;

class Route {
	public static array  $patterns = [":id[0-9]?" => "([0-9]+)", ":text[0-9]?" => "([0-9a-zA-Z-_]+)"];
	public static bool   $hasRoute = false;
	public static array  $routes   = [];
	public static string $prefix   = '';

	/**
	 * @param string          $target
	 * @param callable|string $callback
	 *
	 * @return Route
	 */
	public static function get(string $target, callable|string $callback): Route {
		self::$routes['get'][self::$prefix . $target] = ["callback" => $callback];
		return new self();
	}

	/**
	 * @param string          $target
	 * @param callable|string $callback
	 *
	 * @return void
	 */
	public static function post(string $target, callable|string $callback): void {
		self::$routes['get'][self::$prefix . $target] = ["callback" => $callback];
	}

	/**
	 * @return void
	 */
	public static function dispatch(): void {

		$uri    = self::getUri();
		$method = self::getMethod();
		foreach (self::$routes[$method] as $target => $props) {
			foreach (self::$patterns as $key => $value) {
				$target = preg_replace("@" . $key . "@", $value, $target);
			}
			$pattern = '@^' . $target . '$@';
			if (preg_match($pattern, $uri, $param)) {
				array_shift($param);
				self::$hasRoute = true;
				if (isset($props['redirect'])) {
					Redirect::to($props['redirect'], $props['status']);
				} else {
					$callback = $props['callback'];
					if (is_callable($callback)) {
						echo call_user_func_array($callback, $param);
					}
					else if (is_string($callback)) {
						[$controllerName, $method] = explode('@', $callback);
						$controllerName = '\Luadex\Http\Controllers\\' . $controllerName;
						$controller     = new $controllerName();
						echo call_user_func_array([$controller, $method], $param);
					}
				}
			}
		}
		self::checkIfPageExists();
	}

	/**
	 * @return void
	 */
	public static function checkIfPageExists(): void {
		if (self::$hasRoute === false) {
			die('404 - Page not found.');
		}
	}

	/**
	 * @return string
	 */
	public static function getMethod(): string {
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * @return string
	 */
	public static function getUri(): string {
		return str_replace($_ENV['BASE_DIR'], null, $_SERVER['REQUEST_URI']);
	}

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function name(string $name): void {
		$key                               = array_key_last(self::$routes['get']);
		self::$routes['get'][$key]['name'] = $name;
	}

	/**
	 * @param string $name
	 * @param array  $param
	 *
	 * @return string
	 */
	public static function url(string $name, array $param = []): string {
		$route = array_key_first(array_filter(self::$routes['get'], function($route) use ($name) {
			return $route['name'] === $name;
		}));
		return str_replace(array_map(fn($key) => ":" . $key, array_keys($param)), array_values($param), $route);
	}

	/**
	 * @param string $prefix
	 *
	 * @return Route
	 */
	public static function prefix(string $prefix): Route {
		self::$prefix = $prefix;
		return new self();
	}

	/**
	 * @param \Closure $closure
	 *
	 * @return void
	 */
	public static function group(\Closure $closure): void {
		$closure();
		self::$prefix = '';
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return void
	 */
	public static function where(string $key, string $value): void {
		self::$patterns[":" . $key] = "(" . $value . ")";
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @param int    $status
	 *
	 * @return void
	 */
	public static function redirect(string $from, string $to, int $status = 301): void {
		self::$routes['get'][$from] = ["redirect" => $to, "status" => $status];
	}
}