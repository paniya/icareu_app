<?php
	$con = mysqli_connect("br-cdbr-azure-south-b.cloudapp.net", "b4c9e4de10ed39", "ac9164be", "icareu");

	$ID = $_POST["ID"];
	$Password = $_POST["Password"];

    $sql = "SELECT * FROM users WHERE `ID`='$ID' and  `Password`='$Password' ";
	$re = mysqli_query($con,$sql);
	if(mysqli_num_rows($re) == 1){
		while($row = mysqli_fetch_assoc($re)){
			$did = $row["ID"];
		}
		
		echo "Login Success :$did";
	}
	else{
		echo "Login Failed";
	}
?>