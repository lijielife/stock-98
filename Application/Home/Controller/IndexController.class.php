<?php
namespace Home\Controller;
use Think\Controller;
use Elasticsearch\ClientBuilder;
use Overtrue\Pinyin\Pinyin;
vendor('Elasticsearch.vendor.autoload');
class IndexController extends Controller {
    public function index(){
    	
    }
    
    //sh
    public function addsh(){
    	G('begin');
    	$client = ClientBuilder::create()->build();
    	$data = require 'data.php';
    	$params = ['body' => []];
    	 
    	foreach ($data as $v) {
    		$params['body'][] = [
    				'index' => [
    						'_index' => 'index_stock',
    						'_type' => 'type_info',
    						'_id' => 'sh'.$v[0]
    				]
    		];
    		 
    		$params['body'][] = [
    				'code' => $v[0],
    				'name' => $v[1],
    				'letters' => $v[2],
    				'market' => 'sh'
    		];
    		 
    	}
    	 
    	// Send the last batch if it exists
    	if (!empty($params['body'])) {
    		$responses = $client->bulk($params);
    		echo "<pre>";
    		print_r($responses);
    	}
    	G('end');
    	echo 'take times: '.G('begin','end',6);
    }
    
    //sz
    public function addsz(){
    	G('begin');
    	$client = ClientBuilder::create()->build();
    	$data = require 'data_sz.php';
    	$params = ['body' => []];
    
    	foreach ($data as $v) {
    		$params['body'][] = [
    				'index' => [
    						'_index' => 'index_stock',
    						'_type' => 'type_info',
    						'_id' => 'sz'.$v['code']
    				]
    		];
    		 
    		$params['body'][] = $v;
    		 
    	}
    	// Send the last batch if it exists
    	if (!empty($params['body'])) {
    		$responses = $client->bulk($params);
    		echo "<pre>";
    		print_r($responses);
    	}
    	G('end');
    	echo 'take times: '.G('begin','end',6);
    }
    
    
    
    public function search(){
    	$client = ClientBuilder::create()->build();
    	$params = [
    			'index' => 'index_stock',
    			'type' => 'type_info',
    			'body' => [
    					'query' => [
    							'match' => [
    									'code' => '0'
    							]
    					]
    			]
    	];
    	
    	$results = $client->search($params);
    	echo "<pre>";
    	print_r($results);
    }
    
    //相当是查询列表
    public function scan(){
    	set_time_limit(0);
    	$client = ClientBuilder::create()->build();
    	$params = [
    			"search_type" => "scan",    // use search_type=scan
    			"scroll" => "30s",          // how long between scroll requests. should be small!
    			//"from" => 1000,
    			"size" => 900,               // how many results *per shard* you want back
    			"index" => "index_stock",
    			"type" => "type_info",
    			"body" => [
    					"query" => [
    							"match_all" => []
    					]
    			]
    	];
    	
    	$docs = $client->search($params);   // Execute the search
    	$scroll_id = $docs['_scroll_id'];   // The response will contain no results, just a _scroll_id
    	
    	// Now we loop until the scroll "cursors" are exhausted
    	$count = 0;
    	$fail = 0;
    	while (\true) {
    	
    		// Execute a Scroll request
    		$response = $client->scroll([
    				"scroll_id" => $scroll_id,  //...using our previously obtained _scroll_id
    				"scroll" => "30s"           // and the same timeout window
    		]
    		);
    	
    		// Check to see if we got any search hits from the scroll
    		if (count($response['hits']['hits']) > 0) {
    			$stocks = $response['hits']['hits'];
    			foreach ($stocks as $k=>$v){
    				$stock = $v['_source'];
    				$code = $stock['code'];
    				$market = $stock['market'];
    				if($market == 'sh'){
    					$market = 'ss';
    				}
    				//$requestName = $code.'.'.$market;
    				$res = $this->getDayData($market,$code);
    				//if($res) $count++;
    				//dump($stock);
    				//$res = $this->getData($requestName);
    				if($res){
    					$count++;
    				}else{
    					$fail ++;
    				}
    			}
    			// Get new scroll_id
    			// Must always refresh your _scroll_id!  It can change sometimes
    			$scroll_id = $response['_scroll_id'];
    		} else {
    			// No results, scroll cursor is empty.  You've exported all the data
    			break;
    		}
    	}
    	
    	echo "成功下载，$count 条记录, 失败：$fail";
    }
    
    public function getData($stockName){
    	if(!S($stockName)){
	    	$stockInfo = explode('.',$stockName);
	    	$market = $stockInfo[1];
	    	$stockId = $stockInfo[0].'.'.$market;
	    	 
	    	$url = 'http://table.finance.yahoo.com/table.csv?s='.$stockId;
	    	$content = file_get_contents($url);
	    	if($content){
	    		$filename = "Data/{$market}{$stockInfo[0]}.csv";
	    		file_put_contents($filename, $content);
	    		S($stockName,md5($stockName));
	    		return true;
	    	}else{
	    		return false;
	    	}
	    	
    	}else{
    		return true;
    	}
    }
    
    //更新文档
    public function updateInfo($data){
    	$client = ClientBuilder::create()->build();
    	if(empty($data) || !isset($data['id'])){
    		return false;
    	}
    	$params = [
    			'index' => 'index_stock',
    			'type' => 'type_info',
    			'id' => $data['id'],
    			'body' => [
    					'doc' => $data
    			]
    	];
    	
    	return $client->update($params);
    }
    public function get(){
    	$client = ClientBuilder::create()->build();
    	$params = [
    			'index' => 'index_stock',
    			'type' => 'type_info',
    			'id' => 'sz000010',
    	];
    	 
    	$response = $client->get($params);
    	echo json_encode($response);
    }
    
    //
    public function getDayData($market, $code){
    	
    	$stockId = $market.$code;
    	
    	$url = 'Data/'.$stockId.'.csv';
    	//$url = 'table.csv';
    	$handle = fopen($url, 'r');
    	//dump($url);
    	$i = 0;
    	$params = [];
    	while ($data = fgetcsv($handle)){
    		if($i==0){
    			$i++;
    			continue;
    		}
    		if($i==1){
    			if($market=='ss') $market = 'sh';
    			$updata = [
    					'id' => $market.$code,
    					'update'=> $data[0]
    			];
    		}
    		
    		$params['body'][] = [
    				'index' => [
    						'_index' => 'index_stock',
    						'_type' => 'type_day',
    						'_id' => $stockId.'_'.$data['0']
    				]
    		];
    		 
    		$params['body'][] = [
    				'date' => $data[0],
    				'open' => $data[1],
    				'high' => $data[2],
    				'low' => $data[3],
    				'close' => $data[4],
    				'volume' => $data[5],
    				'adj_close' => $data[6],
    		];
    		$i++;
    	}
    	$client = ClientBuilder::create()->build();
    	if($params['body']){
    		$reponse = $client->bulk($params);
    		if(!$reponse['errors']){
    			$this->updateInfo($updata);
    			return true;
    		}else{
    			return false;
    		}
    	}else {
    		return false;
    	}
    }
    
    public function exchange(){
    	$str = '000054.sz,000468.sz,000986.sz,000992.sz,300318.sz,300469.sz,000152.sz,002767.sz,002769.sz,000160.sz,000512.sz,002318.sz,002328.sz,300479.sz,603188.ss,603355.ss,603818.ss,000168.sz,000771.sz,002760.sz,300471.sz,300488.sz,000053.sz,000706.sz,300498.sz';
    	$a = explode(',', $str);
    	print_r($a);
    	
    }
}