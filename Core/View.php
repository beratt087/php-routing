<?php

namespace Luadex\Core;
use Jenssegers\Blade\Blade;
class View {
	/**
	 * @param string $view
	 * @param array  $data
	 *
	 * @return string
	 */
	public static function view(string $view, array $data = []): string {
		$blade = new Blade(dirname(__DIR__) . '/public/view', dirname(__DIR__) . '/storage/cache');
		return $blade->render($view, $data);
	}
}