<?php
/* use Elasticsearch\ClientBuilder;
require 'vendor/autoload.php';

$client = ClientBuilder::create();
$client->build()->setHosts(['localhost:9200']);

$params = [
		'index' => 'my_index',
		'type' => 'my_type',
		'id' => 'my_id',
		'body' => ['testField' => 'abc']
];

$response = $client->index($params);
print_r($response);
 */


function curl($url, $postData = "", $method = "PUT") {
	$ci = curl_init ();
	curl_setopt ( $ci, CURLOPT_URL, $url );
	curl_setopt ( $ci, CURLOPT_PORT, 9200 );
	curl_setopt ( $ci, CURLOPT_TIMEOUT, 200 );
	curl_setopt ( $ci, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ci, CURLOPT_FORBID_REUSE, 0 );
	curl_setopt ( $ci, CURLOPT_CUSTOMREQUEST, $method );
	if ($postData) {
		curl_setopt ( $ci, CURLOPT_POSTFIELDS, $postData );
	}
	$response = curl_exec ( $ci );
	return $response;
}



$data = ['query'=>['match'=>['last_name'=>'Smith']]];
$data_json = json_encode($data);

$data_json = '{
   "query": {
    "match": {
      "last_name": "smith"
    }
  },
  "aggs": {
    "all_interests": {
      "terms": {
        "field": "interests"
      }
    }
  }
}';

$url = 'http://localhost:9200/megacorp/employee/_search/?';


$res = curl($url,$data_json, 'GET');
echo "<pre>";
echo $res;


