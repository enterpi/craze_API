<?php
	//Response Header
	$this->load->view('xmlheader');
	
	$nodesStr = '';
	$resStatusStr = '';
	$msgStr = '';
	$combinedXML = '';

	foreach ($nodes as $node => $nodeview) {
		$nodedata = $$node;
		

		$nodesStr .= "<$node/>";
		
		$resStatusStr .= "<$node>" . $nodedata['resStatus'] ."</$node>";
		
		if ($nodedata['resStatus'] == 0) {
			$msgStr .= "<$node>";
            $msgStr .= "<msgCode>" . $nodedata['msgCode'] . "</msgCode>";
			$msgStr .= "<msg>" . $nodedata['msg'] . "</msg>";
            $msgStr .= "</$node>";			
		}
		
		$nodeXML = $this->load->view($nodeview,$nodedata,true);
		$combinedXML .= $nodeXML . "\n";
	}	
	
	echo "<nodes>";
	echo "$nodesStr";
	echo "</nodes>\n";	

	echo "<resStatus>";
	echo "$resStatusStr";
	echo "</resStatus>\n";
	
	echo "<resMessage>";
	echo "$msgStr";
	echo "</resMessage>\n";		
	
	echo $combinedXML;

	//Response Footer
	$this->load->view('xmlfooter');	
?>