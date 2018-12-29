<?php

namespace App\Helpers;

use Ixudra\Curl\Facades\Curl;

class Helper
{
	public function curl_get($url){
		return Curl::to($url)->get();
	}
}

?>
