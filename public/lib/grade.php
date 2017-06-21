<?php

	function send_grade($grade, $config, $lti_grade_url, $sourcedid, $lti_consumer_key){
		$method="POST";
		if (get_magic_quotes_gpc()) $sourcedid = stripslashes($sourcedid);
		$oauth_consumer_key = $lti_consumer_key;
		$oauth_consumer_secret = $config['lti_keys'][$lti_consumer_key];
		$content_type = "application/xml";
		$operation = 'replaceResultRequest';
		$messageIdent = $_SERVER['REQUEST_TIME'];
		$body = '<?xml version = "1.0" encoding = "UTF-8"?>
		<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
		    <imsx_POXHeader>
		        <imsx_POXRequestHeaderInfo>
		            <imsx_version>V1.0</imsx_version>
		            <imsx_messageIdentifier>'.$messageIdent.'</imsx_messageIdentifier>
		        </imsx_POXRequestHeaderInfo>
		    </imsx_POXHeader>
		    <imsx_POXBody>
		        <'.$operation.'>
		            <resultRecord>
		                <sourcedGUID>
		                    <sourcedId>'.$sourcedid.'</sourcedId>
		                </sourcedGUID>
		                <result>
		                    <resultScore>
		                        <language>en-us</language>
		                        <textString>'.$grade.'</textString>
		                    </resultScore>
		                </result>
		            </resultRecord>
		        </'.$operation.'>
		    </imsx_POXBody>
		</imsx_POXEnvelopeRequest>';
		$hash = base64_encode(sha1($body, TRUE));
			$params = array('oauth_body_hash' => $hash);
		$token = '';
		$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
		$consumer = new OAuthConsumer($oauth_consumer_key, $oauth_consumer_secret);
		$outcome_request = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $lti_grade_url, $params);
		//return $outcome_request;
		$outcome_request->sign_request($hmac_method, $consumer, $token);
		$header = $outcome_request->to_header();
		$header = $header . "\r\nContent-type: " . $content_type . "\r\n";
		$options = array(
			'http' => array(
				'method' => 'POST',
				'content' => $body,
				'header' => $header,
			),
		);
		$ctx = stream_context_create($options);
		$fp = @fopen($lti_grade_url, 'rb', FALSE, $ctx);
		$response = @stream_get_contents($fp);
		return $response;
	}

?>
