<?php
$result = 'Success';
$message = 'Success';
$people_data = array();
$location_data = array();
$department_data = array();

$peopleUrl = "https://api.zenefits.com/core/people";
$locationUrl = "https://api.zenefits.com/core/locations";
$departmentsUrl = "https://api.zenefits.com/core/departments";
$authorization = "Authorization: Bearer 9DO1UVEFRU+8As9sqSuZ";


while($locationUrl != null){
    $ch = curl_init();
    curl_setopt($ch ,CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $locationUrl);
    $output = curl_exec($ch);
	$l_output = json_decode($output,true);
	if($l_output['status'] == 200){
		$lData = $l_output['data'];
		$locationUrl = $lData['next_url'];
		foreach ($lData['data'] as $locations) {
			$location_data[$locations['id']] = $locations['name'];
		}
	}
    curl_close($ch);
}

while($departmentsUrl != null){
    $ch = curl_init();
    curl_setopt($ch ,CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $departmentsUrl);
    $output = curl_exec($ch);
	$d_output = json_decode($output,true);
	if($d_output['status'] == 200){
		$dData = $d_output['data'];
		$departmentsUrl = $dData['next_url'];
		foreach ($dData['data'] as $departments) {
			$department_data[$departments['id']] = $departments['name'];
		}
	}
    curl_close($ch);
}

while($peopleUrl != null){
    $ch = curl_init();
    curl_setopt($ch ,CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $peopleUrl);
    $output = curl_exec($ch);

	$p_output = json_decode($output,true);

	if($p_output['status'] == 200){

		$pData = $p_output['data'];
		$peopleUrl = $pData['next_url'];
		foreach ($pData['data'] as $people) {
            if(!isset($people_data[$people['id']])) {
                if ($people['type'] != 'admin') {

                    $dep = '-';
                    $loc = '-';
                    $manager = '-';

                    if (!empty($people['department']['url'])) {
                        $posd = strrpos($people['department']['url'], '/');
                        $idd = $posd === false ? $people['department']['url'] : substr($people['department']['url'], $posd + 1);
                        if (!empty($idd) && is_numeric($idd) && isset($department_data[$idd])) {
                            $dep = $department_data[$idd];
                        }
                    }
                    if (!empty($people['manager']['url'])) {
                        $posm = strrpos($people['manager']['url'], '/');
                        $idm = $posm === false ? $people['manager']['url'] : substr($people['manager']['url'], $posm + 1);
                        if (!empty($idm) && isset($people_data[$idm])) {
                            $manager = $people_data[$idm]['last_name'] . ','. $people_data[$idm]['first_name'];
                        }
                    }
                    if (!empty($people['location']['url'])) {
                        $posl = strrpos($people['location']['url'], '/');
                        $idl = $posl === false ? $people['location']['url'] : substr($people['location']['url'], $posl + 1);

                        if (!empty($idl) && is_numeric($idl) && isset($location_data[$idl])) {
                            $loc = $location_data[$idl];
                        }
                    }
                    $people_data[$people['id']] = array(
                        "last_name" => isset($people['last_name']) ? $people['last_name']:'-',
                        "first_name" => isset($people['first_name']) ? $people['first_name']:'-',
                        "type" => isset($people['type']) ? $people['type']:'-',
                        "status" => isset($people['status']) ? $people['status']:'-',
                        "title" => isset($people['title']) ? $people['title']:'-',
                        "department" => $dep,
                        "location" => $loc,
                        "manager" => $manager,
                        "gender" => isset($people['gender']) ? $people['gender']:'-'
                    );
                }
            }
        }
	}
    curl_close($ch);
}

$data = array(
  "result"  => $result,
  "message" => $message,
  "data"    => array_values($people_data)
);

// Convert PHP array to JSON array
$json_data = json_encode($data);
print $json_data;
?>