<?php

namespace App\Console\Commands\Courier;

use Illuminate\Console\Command;
use App\Helpers\Helper;
use App\Repositories\Shipments;

class ShipmentDailyFetch extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'fetch_shipment:schedule';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetch Awb status every hour';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		echo "Reading data from csv\n";
		//read csv
		$filePath = public_path('files/tracking_ids.csv');
		$limit = 1000;

		$fileHandle = fopen($filePath, "r");
		if ($fileHandle === FALSE){
		    die('Error opening '.$filePath);
		}

		$offset = 0;
		while(!feof($fileHandle)){
		    fseek($fileHandle, $offset); //Go to where we were when we ended the last batch

		    $i = 0;
		    while (($currRow = fgetcsv($fileHandle)) !== FALSE){
		        $i++;
		        $this->fetch_shipment_data($currRow);

		        if($i >= $limit){
		            $offset = ftell($fileHandle); //Update current position in the file

		            break;
		        }
		    }
		}
		fclose($fileHandle);
	}

	public function fetch_shipment_data($csv_row)
	{		
		//call api
		$url = config('external.shipment_api.url').'?awb='.$csv_row[0];

		$api_data = (new Helper())->curl_get($url);
		
		//update data in db
		if(!empty($api_data)){
			$this->update_shipment_data($csv_row,$api_data);
		}
	}

	public function update_shipment_data($csv_row,$data)
	{
		$data = json_decode($data);
		
		if(isset($data[0]) && $data[0]->scan_detail){
			$shipment_array = $data[0]->scan_detail;
			$len = count($shipment_array);

			$arr = array(
				'awb' => $shipment_array[$len-1]->awbno ? $shipment_array[$len-1]->awbno : NULL,
				'pickup_pincode' => $csv_row[1],
				'drop_pincode' => $csv_row[2],
				'order_no' => $shipment_array[$len-1]->orderno ? $shipment_array[$len-1]->orderno : NULL,
				'current_status_code' => $shipment_array[$len-1]->status_code ? $shipment_array[$len-1]->status_code : NULL,
				'current_status' => $shipment_array[$len-1]->status ? $shipment_array[$len-1]->status : NULL,
				'current_status_description' => $shipment_array[$len-1]->status_description ? $shipment_array[$len-1]->status_description : NULL,
				'remarks' => $shipment_array[$len-1]->remarks ? $shipment_array[$len-1]->remarks : NULL,
				'current_location' => $shipment_array[$len-1]->location ? $shipment_array[$len-1]->location : NULL,
				'status_updated_date' => $shipment_array[$len-1]->updated_date ? $shipment_array[$len-1]->updated_date : NULL
			);
			$shipment = Shipments::create($arr);
			print_r($shipment);die;
		}
	}
}