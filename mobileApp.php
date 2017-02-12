<?php 
include 'config.php';
$func = $_POST['func'];
$sendStr='';
//Session Variables
//== Elder <- elder's id
//== ID    <- guardian's ID
function elderName($firName, $midName){
	if (strlen(trim($midName)) > 0) {
		$name = preg_split('/ /',$midName);
		return $name[sizeof($name)-1];
	}
	else{
		$name = preg_split('/ /',$firName);
		return $name[sizeof($name)-1];
	}
	
}

function getInitials($drName){
	$name = preg_split('/ /',$drName);
	$init = '';
	for($i = 0; $i < sizeof($name)-1; $i++){
		$init = $init.$name[$i][0].'.';
	}
	$init = strtoupper($init);
	$init = $init.$name[sizeof($name)-1];
	return $init;
}

function getDose($dose, $pattern){
	$dosex = '';
	$patternx = '';
	if ($dose == 1)
		$dosex = "1 Tablet on";
	else
		$dosex = $dose.' Tablets on';

	if($pattern[0] == '1')
		$patternx = $patternx.', Morning';
	if($pattern[1] == '1')
		$patternx = $patternx.', Noon';
	if($pattern[2] == '1')
		$patternx = $patternx.', Night';
	if (strlen($patternx) == 0){
		return '';
	}
	else{
		$meal='';
		if($pattern[3] == '0')
			$meal = ', Before meal';
		else
			$meal = ', After meal';
		$patternx = substr($patternx,1,strlen($patternx)-1);
		return $dosex.$patternx.$meal;
	}
}

if ($func == "getDefaults"){ // get all the defaults
	$sendStr ="";
	//physician list
	$qry = 'SELECT * FROM physician;';
	$result = mysqli_query($con,$qry);
	$rows = mysqli_num_rows($result);
	$innerData = "";
	if ($rows > 0){
		mysqli_data_seek($result,0);
		$record = mysqli_fetch_assoc($result);
		$innerData = $record['Title'].". ".$record['Name']." ".$record['LastName'].','.$record['PhysicianID'] ;
		for($i = 1 ; $i < $rows ; $i++){
			mysqli_data_seek($result,$i);
			$record = mysqli_fetch_assoc($result);
			$innerData = $innerData.','.$record['Title'].". ".$record['Name']." ".$record['LastName'].','.$record['PhysicianID'] ;
		}
		$sendStr =  $innerData;
	}
	//blackout days
	$qry = 'SELECT * FROM params WHERE Parameter = \'DaysClosed\';';
	$result = mysqli_query($con,$qry);
	$record = mysqli_fetch_assoc($result);
	$len = strlen($record['Value']);
	$innerData ="";
	if ($len > 0){
		$innerData = $record['Value'][0];
		for($i = 1; $i < $len; $i++){
			$innerData =$innerData.','.$record['Value'][$i];
		}
	}
	$sendStr =  $sendStr.'#'.$innerData;
	//blackout dates
	$qry = 'SELECT * FROM params;';
	$result = mysqli_query($con,$qry);
	$rows = mysqli_num_rows($result);
	for($i = 0; $i < $rows; $i++){
		mysqli_data_seek($result,$i);
		$record = mysqli_fetch_assoc($result);
		$params[$record['Parameter']] = $record['Value'];
	}
	$startTime = $params['StartOfDay'];
	$endTime = $params['EndOfDay'];
	$todate = DateTime::createFromFormat('Y-m-d',date('Y-m-d'));
	$todate = $todate->modify('+7 days');
	$todate = $todate->format('Y-m-d');
	$qry = 'SELECT * FROM calender WHERE Person = \'general\' AND `From` = \''.$startTime.'\' AND `To` = \''.$endTime.'\' AND (Date BETWEEN \''.date('Y-m-d').'\' AND \''.$todate.'\') ;';
	$result = mysqli_query($con,$qry);
	$rows = mysqli_num_rows($result);
	$innerData = "";
	//echo $qry.'<br>'.$rows.'<br>';
	if ($rows > 0){
		mysqli_data_seek($result,0);
		$record = mysqli_fetch_assoc($result);
		$innerData = $record['Date'];
		for($i = 1 ; $i < $rows ; $i++){
			mysqli_data_seek($result,$i);
			$record = mysqli_fetch_assoc($result);
			$innerData = $innerData.','.$record['Date'];
		}
	}
	$sendStr =  $sendStr.'#'.$innerData.'#'.$params['StartOfDay'];
}

elseif(isset($_SESSION['ID']) || $_POST['Debg']=="true"){ // see if the session is present
	if(isset($_POST['Debg'])){
		$_SESSION['Elder'] = 10162530246;
	}
	if (mysqli_connect_errno()){
		echo 'Connection Error Occured';
	}
	
	elseif ($func == "addAppointment"){ //send PillPod Data
		//echo "4:20 pm";
		$breaks = Array();
		$elderID = $_SESSION['Elder']; // get pillpod Data
		$physicianID = $_POST['physician'];
		$date = $_POST['date'];
		$reason = $_POST['reason'];
		//check for existing appointment
		$qry = 'SELECT * FROM appointment WHERE Status IS NULL AND AppointmentDate = \''.$date.'\' AND ElderID = \''.$elderID.'\' AND PhysicianID = \''.$physicianID.'\' ORDER BY AppointmentTime DESC LIMIT 1;';
		$exist = mysqli_query($con,$qry);
		if(mysqli_num_rows($exist)){
			$sendStr = "already";
		}
		else{
			//load parameters table
			$params;
			$qry = 'SELECT * FROM params;';
			$para = mysqli_query($con,$qry);
			$para_len = mysqli_num_rows($para);
			for($i = 0; $i < $para_len; $i++){
				mysqli_data_seek($para,$i);
				$record = mysqli_fetch_assoc($para);
				$params[$record['Parameter']] = $record['Value'];
			}
			$params['StartOfDay'] = DateTime::createFromFormat('G:i:s',$params['StartOfDay']);
			$params['EndOfDay'] = DateTime::createFromFormat('G:i:s',$params['EndOfDay']);
			$params['StartOfBreak'] = DateTime::createFromFormat('G:i:s',$params['StartOfBreak']);
			$params['EndOfBreak'] = DateTime::createFromFormat('G:i:s',$params['EndOfBreak']);
			//list current appointments on that date
			$qry = 'SELECT * FROM appointment WHERE (Status <> \'cancelled\' OR Status IS NULL) AND AppointmentDate = \''.$date.'\' AND PhysicianID = \''.$physicianID.'\' ORDER BY AppointmentTime;';
			$appointments = mysqli_query($con,$qry);
			$appointment_len = mysqli_num_rows($appointments);
			$nextTime = $params['StartOfDay'];
			$nextEndTime = $nextTime->format('G:i:s');
			$nextEndTime = DateTime::createFromFormat('G:i:s',$nextEndTime);
			$nextEndTime = $nextEndTime->modify('+'.$params['TimePerElder'].' minutes');
			//add existing appointments to breaks
			for($i = 0 ; $i < $appointment_len; $i++){
				mysqli_data_seek($appointments,$i);
				$appointment = mysqli_fetch_assoc($appointments);
				$endTime = DateTime::createFromFormat('G:i:s',$appointment['AppointmentTime']);
				$endTime = $endTime->modify('+'.$params['TimePerElder'].' minutes');
				$breaks[sizeof($breaks)] = [DateTime::createFromFormat('G:i:s',$appointment['AppointmentTime']), $endTime];
			}
			
			$breaks[sizeof($breaks)] = [DateTime::createFromFormat('G:i:s','00:00:00'), $nextTime]; // add fromm 00:00:00 to start of day/end of current appointments+ time per elder
			$breaks[sizeof($breaks)] = [$params['EndOfDay'],DateTime::createFromFormat('G:i:s','23:59:59')]; // add form end of day to midnight
			//remove lunch break
			$breaks[sizeof($breaks)] = [$params['StartOfBreak'],$params['EndOfBreak']]; // add mid break
			//remove other breaks(from calender)
			$qry = 'SELECT * FROM calender WHERE Person = \''.$physicianID.'\' AND Date = \''.$date.'\' ;';
			$result = mysqli_query($con,$qry);
			$rows = mysqli_num_rows($result);
			if ($rows > 0){
				for($i = 0 ; $i < $rows; $i++){
					mysqli_data_seek($result,$i);
					$record = mysqli_fetch_assoc($result);
					$t1 = DateTime::createFromFormat('G:i:s',$record['From']);//start
					$t2 = DateTime::createFromFormat('G:i:s',$record['To']);//end
					$breaks[sizeof($breaks)] = [$t1,$t2];				
				}
			}
			//sort breaks according to start time(insertion sort)
			for ($i = 0; $i < sizeof($breaks); $i++){
				for($j = 0; $j < $i; $j++){
					if($breaks[$j][0] > $breaks[$i][0]){
						$a  = $breaks[$j];
						$breaks[$j] = $breaks[$i];
						$breaks[$i] = $a;
					}
				}
			}
			//arrange breaks -> check end time with start time
			$ind = 0;
			while($ind < sizeof($breaks)-1){
				if($breaks[$ind][1] > $breaks[$ind+1][0]){ // > start of next break
					if($breaks[$ind][1] < $breaks[$ind+1][1]){ // < end of next break
						$breaks[$ind][1] = $breaks[$ind+1][1];
					}
					array_splice($breaks,$ind+1,1);//remove the next break						
				}
				else{
					$ind++;
				}
			}
			//check for suitable time
			foreach ($breaks as $break){
				if($nextTime >= $break[0] && $nextTime < $break[1]){
					$nextTime = $break[1];
					$nextEndTime = $nextTime->format('G:i:s');
					$nextEndTime = DateTime::createFromFormat('G:i:s',$nextEndTime);
					$nextEndTime = $nextEndTime->modify('+'.$params['TimePerElder'].' minutes');
				}
				if($nextEndTime > $break[0] && $nextTime <= $break[1]){
					$nextTime = $break[1];
					$nextEndTime = $nextTime->format('G:i:s');
					$nextEndTime = DateTime::createFromFormat('G:i:s',$nextEndTime);
					$nextEndTime = $nextEndTime->modify('+'.$params['TimePerElder'].' minutes');
				}
			}
			if($nextEndTime <= $params['EndOfDay']){
				
				//add the appointment
				$qry = "INSERT INTO appointment(ElderID, AppointmentDate, AppointmentTime, Reason, PhysicianID) VALUES ('$elderID','$date','".$nextTime->format('G:i:s')."', '$reason', '$physicianID');";
				if($con->query($qry)){
					$sendStr = $nextTime->format('G:i:s').'#'.$nextEndTime->format('G:i:s');
				}
				else{
					$sendStr = 'error';
				}
			}
			else{
				$sendStr = "booked";
			}				
		}
	}
	elseif ($func == "loadAppointments"){ //load all appointments
		//load todays
		$qry = "SELECT appointment.*,physician.Title, physician.Name, physician.LastName FROM appointment INNER JOIN physician ON physician.PhysicianID = appointment.PhysicianID WHERE Status IS NULL AND ElderID='".$_SESSION['Elder']."' AND AppointmentDate = '".date('Y-m-d')."' ORDER BY AppointmentDate;" ;
		$result = mysqli_query($con,$qry);
		$rows = mysqli_num_rows($result);
		if ($rows > 0){
			mysqli_data_seek($result,0);
			$record = mysqli_fetch_assoc($result);
			$sendStr = $record['AppointmentID'].",".'@ '.date('g:i a',strtotime($record['AppointmentTime'])).",".$record['Title'].". ".getInitials($record['Name'])." ".$record['LastName'].','.$record['Status'];
			for ($i = 1 ; $i < $rows; $i++){
				mysqli_data_seek($result,$i);
				$record = mysqli_fetch_assoc($result);
				$sendStr = $sendStr."%".$record['AppointmentID'].",".'@ '.date('g:i a',strtotime($record['AppointmentTime'])).",".$record['Title'].". ".getInitials($record['Name'])." ".$record['LastName'].','.$record['Status'];
			}
		}
		$sendStr = $sendStr.'#';
		
		//load new
		$qry = "SELECT appointment.*,physician.Title, physician.Name, physician.LastName FROM appointment INNER JOIN physician ON physician.PhysicianID = appointment.PhysicianID WHERE ElderID='".$_SESSION['Elder']."' AND AppointmentDate > '".date('Y-m-d')."' ORDER BY AppointmentTime;" ;
		$result = mysqli_query($con,$qry);
		$rows = mysqli_num_rows($result);
		if ($rows > 0){
			mysqli_data_seek($result,0);
			$record = mysqli_fetch_assoc($result);
			$sendStr = $sendStr.$record['AppointmentID'].",".$record['AppointmentDate'].' @ '.date('g:i a',strtotime($record['AppointmentTime'])).",".$record['Title'].". ".getInitials($record['Name'])." ".$record['LastName'].','.$record['Status'];
			for ($i = 1 ; $i < $rows; $i++){
				mysqli_data_seek($result,$i);
				$record = mysqli_fetch_assoc($result);
				$sendStr = $sendStr."%".$record['AppointmentID'].",".$record['AppointmentDate'].' @ '.date('g:i a',strtotime($record['AppointmentTime'])).",".$record['Title'].". ".getInitials($record['Name'])." ".$record['LastName'].','.$record['Status'];
			}
		}
		$sendStr = $sendStr.'#';
		
		//load past
		$qry = "SELECT appointment.*,physician.Title, physician.Name, physician.LastName FROM appointment INNER JOIN physician ON physician.PhysicianID = appointment.PhysicianID WHERE ElderID='".$_SESSION['Elder']."' AND AppointmentDate < '".date('Y-m-d')."' ORDER BY AppointmentTime;" ;
		$result = mysqli_query($con,$qry);
		$rows = mysqli_num_rows($result);
		if ($rows > 0){
			mysqli_data_seek($result,0);
			$record = mysqli_fetch_assoc($result);
			$sendStr = $sendStr.$record['AppointmentID'].",".$record['AppointmentDate'].' @ '.date('g:i a',strtotime($record['AppointmentTime'])).",".$record['Title'].". ".getInitials($record['Name'])." ".$record['LastName'].','.$record['Status'];
			for ($i = 1 ; $i < $rows; $i++){
				mysqli_data_seek($result,$i);
				$record = mysqli_fetch_assoc($result);
				$sendStr = $sendStr."%".$record['AppointmentID'].",".$record['AppointmentDate'].' @ '.date('g:i a',strtotime($record['AppointmentTime'])).",".$record['Title'].". ".getInitials($record['Name'])." ".$record['LastName'].','.$record['Status'];
			}
		}	
	}
	elseif ($func == "delAppointment"){ //load all appointments	
		$qry = "UPDATE appointment SET Status = 'cancelled' WHERE AppointmentID=".$_POST['id'].";" ;
		if($con->query($qry)){
			$sendStr = 'done';
		}
		else{
			$sendStr = 'error';
		}
	}
	elseif ($func == "getPillPod"){ //load Pillpod data
		//echo "kkk#llll#panadol,twice a day,Round%Amoxillin, once a day,Oval";
		//get latest prescription limit =1
		$qry = "SELECT * FROM prescription WHERE DeviceID IS NOT NULL AND ElderID = '".$_SESSION['Elder']."' ORDER BY Date DESC LIMIT 1;" ;
		$result = mysqli_query($con,$qry);
		if(mysqli_num_rows($result) == 1){
			$prescription = mysqli_fetch_assoc($result);
			$qry = "SELECT prescription_entry.*, drug.DrugName, drug.Shape FROM prescription_entry LEFT JOIN drug ON drug.DrugID = prescription_entry.DrugID WHERE PrescriptionID = '".$prescription['PrescriptionID']."' ORDER BY Days DESC;" ;
			//echo $qry;
			$result = mysqli_query($con,$qry);
			$rows = mysqli_num_rows($result);
			if ($rows > 0){
				$entry = mysqli_fetch_assoc($result);
				$em ='';
				if ($entry['Emergency']== '1')
						$em = '(Em)';
					else $em = '';
				$sendStr = $entry['DrugName'].$em.'%'.getDose($entry['Dose'],$entry['Pattern'])."%".$entry['Shape'];
				for($i = 1; $i < $rows; $i++){
					mysqli_data_seek($result,$i);
					$entry = mysqli_fetch_assoc($result);
					if ($entry['Emergency']== '1')
						$em = '(Em)';
					else $em = '';
					$sendStr = $sendStr.'#'.$entry['DrugName'].$em.'%'.getDose($entry['Dose'],$entry['Pattern'])."%".$entry['Shape'];
				}
			}
			else $sendStr = "Null";
			
			//list all drugs with descending days order
			
		
		//next change = max of days + prescrip date
		}
		else $sendStr = "Null";
	}
	elseif ($func == "getElders"){ //load elders
		$qry = "";
		if(isset($_SESSION['ID'])){
			$qry = "SELECT * FROM elder WHERE GuardianID = '".$_SESSION['ID']."';" ;
		}
		else{
			$qry = "SELECT * FROM elder ;" ;
		}
		$result = mysqli_query($con,$qry);
		$rows = mysqli_num_rows($result);
		if ($rows > 0){
			$record = mysqli_fetch_assoc($result);
			$sendStr = elderName($record['FirstName'],$record['MiddleName'])." ".$record['LastName'].'%'.$record['ElderID'];
			for($i = 1 ; $i < $rows; $i++){
				mysqli_data_seek($result,$i);
				$record = mysqli_fetch_assoc($result);
				$sendStr = $sendStr."#".elderName($record['FirstName'],$record['MiddleName'])." ".$record['LastName'].'%'.$record['ElderID'];
			}
		}
		else $sendStr = "Null";
	}
	
	elseif ($func == "setElder"){ //set the elder ID
		if(isset($_POST['elder'])){
			$_SESSION['Elder'] = $_POST['elder'];
			$sendStr = "done";
		}
		else{
			$sendStr = "error";
		}
	}
}
else $sendStr = "sesEr";
echo $sendStr;

?>