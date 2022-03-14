<?php
declare(strict_types=1);

namespace App\Configure;

class Configure {

	public static function read( string $tag = '' ) {

		$config = include DIR_APP . "/config/config.php";

		return isset( $config[ $tag ] ) ? $config[ $tag ] : false;
	}
}