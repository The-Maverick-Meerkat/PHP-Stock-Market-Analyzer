<?php

include ('connect.php'); //my connect is on the same work-folder

function masterLoop(){
	$mainTickerFile = fopen("tickerMaster.txt","r");
	while(! feof($mainTickerFile)){
		$companyTicker = fgets($mainTickerFile);
		$companyTicker = trim($companyTicker);
		
		$nextDayInc = 0;
		$nextDayDec = 0;
		$nextDayNoC = 0;
		$total = 0;
		
		$sumOfInc = 0;
		$sumOfDec = 0;
		
		$sqlSel = "SELECT Date, percentage_change FROM $companyTicker WHERE percentage_change < '0' ORDER BY Date ASC"; //
		$resultSel = mysql_query($sqlSel);
		

		if($resultSel){
			while($row = mysql_fetch_array($resultSel)){
				$date1 = $row['Date'];
				$per_change = $row['percentage_change'];

				
				$sql2 = "SELECT Date, percentage_change FROM $companyTicker WHERE Date > '$date1' ORDER BY Date ASC LIMIT 1";
				$resultSel2 = mysql_query($sql2);
				$numOfRows = mysql_num_rows($resultSel2);
				$row2 = mysql_fetch_array($resultSel2);
				$date2 = $row2['Date'];
				$per_change2 = $row2['percentage_change'];

				
				if($numOfRows==1){

				
					if($per_change2 > 0){
						$nextDayInc++;
						$sumOfInc += $per_change2;
						$total++;
					}else if($per_change2 < 0){
						$nextDayDec++;
						$sumOfDec += $per_change2;
						$total++;
					}else{
						$nextDayNoC++;
						$total++;
					} 
				}else if ($numOfRows==0){
					//no data after today
				}else{
					echo "You have an error";
				}				
			}
		}else{
			echo "unable to select $companyTicker <br />";
		}
		
		$nextDayIncPer = ($nextDayInc/$total)*100;
		$nextDayDecPer = ($nextDayDec/$total)*100;
		$avgIncPer = $sumOfInc/$nextDayInc;
		$avgDecPer = $sumOfDec/$nextDayDec;
		
		
		insIntoTable($companyTicker, $nextDayInc, $nextDayIncPer, $avgIncPer, $nextDayDec, $nextDayDecPer, $avgDecPer);
	}
}

function insIntoTable($companyTicker, $nextDayInc, $nextDayIncPer, $avgIncPer, $nextDayDec, $nextDayDecPer, $avgDecPer){
	
	$buckyBuy = $nextDayIncPer * $avgIncPer;
	$buckySell = $nextDayDecPer * $avgDecPer;
	

	$queryS = "SELECT * FROM analysisA WHERE ticker = '$companyTicker' ";
	$result3 = mysql_query($queryS);
	$numOfRows2 = mysql_num_rows($result3);
	
	if($numOfRows2==1){
		$queryUpd = "UPDATE analysisA SET ticker='$companyTicker',daysInc='$nextDayInc',pctOfDaysInc='$nextDayIncPer',avgIncPer='$avgIncPer',daysDec='$nextDayDec',pctOfDaysDec='$nextDayDecPer',avgDecPer='$avgDecPer', buy='$buckyBuy', sell='$buckySell' ";
		mysql_query($queryUpd);		
	}else{
	$queryIns = "INSERT INTO analysisA (ticker, daysInc, pctOfDaysInc, avgIncPer, daysDec, pctOfDaysDec, avgDecPer, buy, sell) VALUES('$companyTicker','$nextDayInc','$nextDayIncPer','$avgIncPer','$nextDayDec','$nextDayDecPer','$avgDecPer','$buckyBuy','$buckySell')";
	mysql_query($queryIns);
	} 
}

$queryC = "CREATE TABLE analysisA (ticker VARCHAR(8), PRIMARY KEY(ticker), daysInc INT, pctOfDaysInc FLOAT, avgIncPer FLOAT, daysDec INT, pctOfDaysDec FLOAT, avgDecPer FLOAT, buy FLOAT, sell FLOAT)";
mysql_query($queryC);
masterLoop();

?>