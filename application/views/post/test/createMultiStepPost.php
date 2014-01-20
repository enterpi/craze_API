<?php
/*
 * createMultiStepPost.php
 */
?>
<?php
$url = site_url('posts/createMultiStepPost');

	$file = file_get_contents('/mnt/WWW/current/html/application/views/post/test/createPostData1.txt');
        $content = json_encode($file);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
                array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        $json_response = curl_exec($curl);

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $response = json_decode($json_response, true);
	print_r($response);
?>
