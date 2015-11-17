<?php
use Elasticsearch\ClientBuilder;
require 'vendor/autoload.php';
$client = ClientBuilder::create()->build();


$hosts = [
		'114.215.83.56:9200',         // IP + Port
// 		'127.0.0.1',              // Just IP
// 		'mydomain.server.com:9201', // Domain + Port
// 		'mydomain2.server.com',     // Just Domain
// 		'https://localhost',        // SSL to localhost
// 		'https://192.168.1.3:9200'  // SSL to IP + Port
];

$clientBuilder = ClientBuilder::create();   // Instantiate a new ClientBuilder
$clientBuilder->setHosts($hosts);           // Set the hosts
$client = $clientBuilder->build();          // Build the client object




// $response = $client->index($params);
// print_r($response);


// $params = [
//     'index' => 'my_index',
//     'body' => [
//         'settings' => [
//             'number_of_shards' => 2,
//             'number_of_replicas' => 0
//         ]
//     ]
// ];

// $params = [
//     'index' => 'index_shop',
//     'type' => 'shop',
//     'body' => [
//         'query' => [
//             'match' => [
//                 'name' => '农庄'
//             ]
//         ]
//     ]
// ];

// $response = $client->search($params);
// echo "<pre>";
// // print_r($response);
// echo json_encode($response);

$params = [
		'index' => 'test',
		'type' => 'test',
		'id' => 1,
		'client' => [
				'future' => 'lazy'
		]
];

$future = $client->get($params);
echo "<pre>";
print_r($future);
