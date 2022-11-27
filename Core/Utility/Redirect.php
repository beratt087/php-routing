<?php

namespace Luadex\Core\Utility;

class Redirect {
	/**
	 * @param string $to
	 * @param int    $status
	 *
	 * @return void
	 */
	public static function to(string $to, int $status = 301): void {
		header("Location:" . $to, true, $status);
		exit;
	}
}