<?php
die('@@@@@@%%%%');
require_once 'connection.php';
$servername = DB_SERVER_NAME;
$username = DB_USER_NAME;
$password = DB_PASSWORD; 
$dbname = DB_NAME;

//Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
//Check connection
if ($conn->connect_error) {
  die("Connection failed@@@$$$: " . $conn->connect_error);
} else {
	die("success");
}
die('@@@@@@%%%%');
global $conn;
//Get Latest Week info from wallpapertypes table
function getLatestWeekInfo(){
	global $conn;
	$latestWeekInfo = "SELECT * FROM wallpapertypes order by id desc limit 1"; #echo $latestWeekInfo;
	$result = $conn->query($latestWeekInfo); #echo $result->num_rows;die;
	if ($result->num_rows > 0) {
		$latestWeekArr = array();
		while($row = $result->fetch_assoc()) {
			$latestWeekArr['week_num'] = $row['week_num'];
			$latestWeekArr['upcoming_wallpaper_ids'] = $row['upcoming_wallpaper_ids'];
			$latestWeekArr['past_wallpaper_ids'] = $row['past_wallpaper_ids'];
			$latestWeekArr['creation_date'] = $row['created_at'];
		}
		return $latestWeekArr;
	} else {
		return array();
	}
}

//Get active wallpapers list from images table
function getActiveWallapersList(){
	global $conn;
	$activeWallpaperInfo = "SELECT id FROM images order by prefOrder desc"; #echo $activeWallpaperInfo;
	$result = $conn->query($activeWallpaperInfo); #echo $result->num_rows;die;
	if ($result->num_rows > 0) {
		$activeWallListArr = array();
		while($row = $result->fetch_assoc()) {
			$activeWallListArr[] = $row['id']; //wallpapers ids
		}
		return $activeWallListArr;
	} else {
		return array();
	}
}

//Get future and past wallpaper list for next weeks
function getFuturePastWallListForNextWeeks($future_list,$past_list,$activeWallapersList,$week_num){
	global $conn;
	$exResult = array();

	if($future_list !=""){
		$future_list_arr = explode(",",$future_list);
	} else {
		$future_list_arr = array();
	}
	
	if($past_list !=""){
		$past_list_arr = explode(",",$past_list);
	} else {
		$past_list_arr = array();
	}
	
	if(!empty($activeWallapersList)){
		$active_list_arrR = array_reverse($activeWallapersList);
	} else {
		$active_list_arrR = array();
	}
	
	//echo "<pre>future_list_arr=";print_R($future_list_arr);
	//echo "<pre>past_list_arr=";print_R($past_list_arr);
	//echo "<pre>active_list_arrR=";print_R($active_list_arrR);
	
	if( count($active_list_arrR) >= count($future_list_arr) ){
		$futureRR = array_values(array_diff($active_list_arrR,$future_list_arr));
	} else {
		$futureRR = array_values(array_diff($future_list_arr,$active_list_arrR));
	}
	//echo "<pre>futureRR=";print_R($futureRR);#die;
	$exResult['future'] = implode(",",array_values(array_slice($futureRR, 0,3, true)));
	$exResult['futureArr'] = array_values(array_slice($futureRR, 0,3, true));
	//echo "<pre>futureArr=";print_R($exResult['futureArr']);#die;
	
	//Get past week list
	if(!empty($future_list_arr)){
		$exResult['past'] = implode(",",array_values(array_slice($future_list_arr, -2, 2, true)));
		$exResult['pastArr'] = array_values(array_slice($future_list_arr, -2, 2, true));
		#echo "<pre>exResult-past=";print_R($exResult['pastArr']);#die;
		
		$exResult['notActiveArr'] = array_values(array_diff($future_list_arr,$exResult['pastArr']));
		//echo "<pre>notActiveArr=";print_R($exResult['notActiveArr']);#die;
		
		
		$arrayCom = array_merge($future_list_arr,$past_list_arr);
		//echo "<pre>arrayCom=";print_R($arrayCom);die;
		$notUseArr1 = array_values(array_diff($active_list_arrR,$arrayCom));
		//echo "<pre>notUseArr1=";print_R($notUseArr1);
		//echo "<pre>future_list_arr=";print_R($future_list_arr);
		//die;
		if( count($notUseArr1) >= count($exResult['futureArr']) ){
			$exResult['notUseArr'] = array_values(array_diff($notUseArr1,$exResult['futureArr']));
		} else {
			$exResult['notUseArr'] = array_values(array_diff($exResult['futureArr'],$notUseArr1));
		}
	}
	return $exResult;
}


//Reset order for next available wallpapers for next weeks
function resetWallpaperOrder($notActiveArr,$notUseArr){
	global $conn;
	$exResult = array();
	#echo "<pre>notActiveArr=";print_R($notActiveArr); 
	#echo "<pre>notUseArr=";print_R($notUseArr); die;
	//less order of next week IDs  preference
	if(!empty($notUseArr)){
		foreach($notUseArr as $kk=>$vv){
			$getPerfInfo = "SELECT prefOrder FROM images where id= '".$vv."'"; #echo $latestWeekInfo;
			$resultPerf = $conn->query($getPerfInfo); #echo $result->num_rows;die;
			if ($resultPerf->num_rows > 0) {
				$rowPerf = $resultPerf->fetch_assoc();
				$prefOrderSel = $rowPerf['prefOrder'];
				$updPrefNew = $prefOrderSel -1;
				$sql225 = "UPDATE images 
						SET prefOrder='".$updPrefNew."',updated_at=now()  
						WHERE id = '".$vv."' ";
				$conn->query($sql225); 
			} 
		}
	}
	
	//Get highest order of that
	if(!empty($notActiveArr)){
		//Get count of all images
		$getIdInfo = "SELECT id FROM images"; 
		$resultId = $conn->query($getIdInfo); #echo $result->num_rows;die;
		$num_rowsF = $resultId->num_rows;
		foreach($notActiveArr as $kkA=>$vvA){
			$sql29 = "UPDATE images 
					SET prefOrder='".$num_rowsF."',updated_at=now()  
					WHERE id = '".$vvA."' ";
			$conn->query($sql29); 
		}
	}
}


//Get Latest Week info from wallpapertypes table
$latestWeekInfo = getLatestWeekInfo();
#echo "<pre>latestWeekInfo=";print_R($latestWeekInfo);die;
if(!empty($latestWeekInfo)){
	//Get active wallpapers list from images table
	$activeWallapersList = getActiveWallapersList();
	//echo "<pre>activeWallapersList=";print_R($activeWallapersList);#die;
	if(!empty($activeWallapersList)){
		$startdate = strtotime("Sunday");
		//$enddate = strtotime("+10 weeks", $startdate);
		//while ($startdate < $enddate) {
			//echo "<br/>".date("M d", $startdate) . "<br>";
		  
			$currentDate = date("Y-m-d");
			$cronWeekDate = date("Y-m-d", $startdate);
			
			if ( strtotime($currentDate) != strtotime($cronWeekDate) ) { 
				die('not-equal');
			} 
			else { //die('equal');
				
				//Get future and past wallpaper list for next weeks
				$ListArr = getFuturePastWallListForNextWeeks($latestWeekInfo['upcoming_wallpaper_ids'],$latestWeekInfo['past_wallpaper_ids'],$activeWallapersList,$latestWeekInfo['week_num']);
				//echo "<pre>ListArr=";print_R($ListArr);#die;
				
				//insert into wallpapertypes table
				if(!empty($ListArr)){
					$nextWeek = $latestWeekInfo['week_num']+1;
					$sql55 = "INSERT INTO wallpapertypes (week_num, upcoming_wallpaper_ids, past_wallpaper_ids, created_at,updated_at)
					VALUES ('".$nextWeek."','".$ListArr['future']."', '".$ListArr['past']."',now(), now())";
					$ins55 = $conn->query($sql55);
					if($ins55){
						
						//Last week future entry "prefType" and "prefOrder" removal from image table
						$upcoming_wallpaper_ids = $latestWeekInfo['upcoming_wallpaper_ids'];
						if($upcoming_wallpaper_ids != ""){
							$lastWeekFutureArr = explode(",",$upcoming_wallpaper_ids);
							foreach($lastWeekFutureArr as $lastKey=>$lastVal){
								$sql21 = "UPDATE images 
										SET prefOrder='',prefType='',updated_at=now()  
										WHERE id = '".$lastVal."' ";
								$conn->query($sql21); 
							}
						}
						
						//update image table "prefType" and "prefOrder" field
						//past array
						if(!empty($ListArr['pastArr'])){
							foreach($ListArr['pastArr'] as $pastKey=>$pastVal){
								if($pastKey <1){
									$prefOrder = 4;
									
								} if($pastKey ==1){
									$prefOrder = 5;
								}
								$sql22 = "UPDATE images 
										SET prefOrder='".$prefOrder."',prefType='Past',updated_at=now()  
										WHERE id = '".$pastVal."' ";
								$conn->query($sql22); 
							}
						}
						
						//update image table "prefType" and "prefOrder" field
						//future array
						if(!empty($ListArr['futureArr'])){
							foreach($ListArr['futureArr'] as $futureKey=>$futureVal){
								if($futureKey <1){
									$futureOrder = 1;
								} else if($futureKey ==1){
									$futureOrder = 2;
								} else if($futureKey ==2){
									$futureOrder = 3;
								}
								$sql23 = "UPDATE images 
										SET prefOrder='".$futureOrder."',prefType='Future',updated_at=now()  
										WHERE id = '".$futureVal."' ";
								$conn->query($sql23); 
							}
						}
						//Reset order for next available wallpapers
						$resetWallList = resetWallpaperOrder($ListArr['notActiveArr'],$ListArr['notUseArr']);
						//echo "<pre>resetWallList=";print_R($resetWallList);die;
					}
				}
			//}
			//$startdate = strtotime("+1 week", $startdate);
		}
	} else {
		echo "No active wallpaper exist in Db";
	}
} else {
	echo "No future or past wallpaper is set for week in Db";
} 
$conn->close();
?>