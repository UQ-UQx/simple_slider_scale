<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ReactJS + PHP (LTI Support)</title>
         <?php
        	require_once('./config.php');
        	require_once('./lib/lti.php');
        	$lti = new Lti($config,true);
        	if(isset($config['use_db']) && $config['use_db']) {
        		require_once('./lib/db.php');
        		Db::config( 'driver',   'mysql' );
        		Db::config( 'host',     $config['db']['hostname'] );
        		Db::config( 'database', $config['db']['dbname'] );
        		Db::config( 'user',     $config['db']['username'] );
        		Db::config( 'password', $config['db']['password'] );
        	}

            if(!$lti->is_valid()) {
                echo("LTI Not Valid");
        		die();
        	}

            $lti_id = $lti->lti_id();
			$user_id = $lti->user_id();
			$calldata = $lti->calldata();
			$lti_grade_url = $lti->grade_url();
			$lti_consumer_key = $lti->lti_consumer_key();
			$result_sourcedid = $lti->result_sourcedid();
			$role = $lti->user_roles();

			if(($role !== "Instructor") || ($role !== "Administrator")){
				$calldata = '';
			}

            
        ?>
    </head>
    <body>
    <script type="text/javascript">

		$LTI_resourceID = '<?php echo $lti_id ?>';
		$LTI_userID = '<?php echo $user_id ?>';
		$LTI_grade_url = '<?php echo $lti_grade_url ?>';
		$LTI_consumer_key = '<?php echo $lti_consumer_key ?>';
		$LTI_result_sourcedid = '<?php echo $result_sourcedid ?>';
		$LTI_user_role = '<?php echo $role ?>';
		$LTI_call_data = JSON.parse('<?php echo json_encode($calldata) ?>');

        $LTI_is_valid = JSON.parse('<?php echo json_encode($lti->is_valid()) ?>'); 
	</script>
    <div id="app"></div>

    <script type="text/javascript" src="./build/bundle.js"></script>
    </body>
</html>
