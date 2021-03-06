<?php
	//DownloadMii (API) v0.1-///
	/*Documentation
		This api.php is for parse the request URL and get the data from the db

		The URL request will be formated like this:
		To retrieve JSON
		App list by developer
		<domain>/api/bydev/[developerId]

		App list by category/sub/other
		<domain>/api/apps/[category]/[subcategory]/[othercategory]

		To rate an APP
		<domain>/api/rate/[securetoken]/[appguid]/[rating]
		
		To get banner
		<domain>/api/banner
		
		To get APP banner
		<domain>/api/banner/[appguid]

	*/
	
	function getJSONFromSQLQuery($conn, $sql, $name, $bindParamTypes = null, $bindParamVarsArr = null) {
		//TODO: Category ID -> name, Rating ID -> integer, add error checks
		
		$stmt = $conn->prepare($sql);
		if (isset($bindParamTypes, $bindParamVarsArr)) {
			$callUserArgs = $bindParamVarsArr;
			array_unshift($callUserArgs, $bindParamTypes);
			
			//Create references for call_user_func_array
			$callUserArgsRefs = array();
			foreach($callUserArgs as $key => $value) {
				$callUserArgsRefs[$key] = &$callUserArgs[$key];
			}
			
			call_user_func_array(array($stmt, 'bind_param'), $callUserArgsRefs); //Safe SQL binding
		}
		$stmt->execute(); //Perform query
		$mysqlResult = $stmt->get_result(); //Get results
		
		$arr = array();
		while ($mysqlRow = $mysqlResult->fetch_object()) {
			array_push($arr, $mysqlRow); //Push all rows to the array
		}
		
		$stmt->close();
		$jsonResultObj = (object)array($name => $arr); //Create an enclosing object
		return json_encode($jsonResultObj); //Return JSON
	}
	
	function sendResponseCodeAndExitIfTrue($condition, $responseCode) {
		if ($condition) {
			http_response_code($responseCode);
			exit();
		}
	}

	sendResponseCodeAndExitIfTrue(strpos(getenv('REQUEST_URI'), '/api/') != 0, 400);
	
	$origKey = ''; //Key to verify if the app that is accessing the API is valid
	$requestUri = strtok(getenv('REQUEST_URI'), '?');
	$param = explode('/', rtrim(substr($requestUri, strlen('/api/')), '/')); //All URL "directories" after /api/ -> array

	//get POST parameters
	$appKey = !empty($_POST['appKey']) ? $_POST['appKey'] : null;

	//Syntax/security checks
	sendResponseCodeAndExitIfTrue(count($param) < 1, 400);
	sendResponseCodeAndExitIfTrue($origKey != $appKey, 403);
	
	$config = include('config.php');
	$mysqlConn = new mysqli($config['mysql_host'], $config['mysql_user'], $config['mysql_pass'], $config['mysql_db']);
	$topLevelRequest = $param[0];
	
	switch ($topLevelRequest) {
		case 'bydev':
			$mysqlQuery = 'SELECT app.* FROM apps app JOIN users usr ON usr.userId = app.creator WHERE usr.nick = ?'; //Select rows from apps table by queried developer
			print(getJSONFromSQLQuery($mysqlConn, $mysqlQuery, $param[1], 's', [$param[1]]));
			break;
		
		case 'apps':
			sendResponseCodeAndExitIfTrue(count($param) < 2, 400);
			$secondLevelRequest = $param[1];
			
			switch ($secondLevelRequest) {
				case 'TopDownloadedApps':
				case 'TopDownloadedGames':
					$mysqlQuery = 'SELECT app.* FROM apps app JOIN categories maincat ON maincat.categoryId = app.category'; //Select top 10 downloaded apps/games
					
					//Ask for only apps/games depending on request
					if ($secondLevelRequest == 'TopDownloadedApps') {
						$mysqlQuery .= ' WHERE maincat.name != "Games"';
					}
					else {
						$mysqlQuery .= ' WHERE maincat.name = "Games"';
					}
					
					$mysqlQuery .= ' ORDER BY app.downloads DESC LIMIT 10';
					print(getJSONFromSQLQuery($mysqlConn, $mysqlQuery, $secondLevelRequest));
					break;
					
				case 'StaffPicks':
					# code...
					break;
				
				case 'Applications':
					$mysqlQuery = 'SELECT app.* FROM apps app';
					$bindParamTypes = null;
					$bindParamArgs = null;
					
					//Category query appending
					if (count($param) > 2) {
						$bindParamTypes = 's';
						$bindParamArgs = array($param[2]);
						
						$mysqlQueryEnd = ' WHERE maincat.name = ?';
						$mysqlQuery .= ' JOIN categories maincat ON maincat.categoryId = app.category';
						
						if (count($param) > 3) {
							$bindParamTypes .= 's';
							array_push($bindParamArgs, $param[3]);
							
							$mysqlQueryEnd .= ' AND subcat.name = ?';
							$mysqlQuery .= ' JOIN categories subcat ON subcat.categoryId = app.subcategory';
						
							if (count($param) > 4) {
								$bindParamTypes .= 's';
								array_push($bindParamArgs, $param[4]);
								
								$mysqlQueryEnd .= ' AND othercat.name = ?';
								$mysqlQuery .= ' JOIN categories othercat ON othercat.categoryId = app.othercategory';
							}
						}
						
						$mysqlQuery .= $mysqlQueryEnd;
					}
					
					print(getJSONFromSQLQuery($mysqlConn, $mysqlQuery, 'Apps', $bindParamTypes, $bindParamArgs));
					break;
			}
			break;
		
		case 'rate':
			#if (/*Check secure Token*/) {
				# code...
			#}
			#else 
				#error invalid user.
			# code...
			break;
		
		case 'banner':
			if (count($param) > 1) {
				//get the banner for the current application
			}
			else{
				//get the current main banner
			}
			break;
	}
	$mysqlConn->close();
?>
