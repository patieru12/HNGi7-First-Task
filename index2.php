<?php
/*
 * This file is designed to read the scripts folder and read the content of every single file found there o extract the content
 *
 **/
$supported_languages = ["php"=>"php", "py"=>"python", "js"=>"/usr/local/bin/node"];
$supported_client_side_languages = [];

$scripts_dir = "./scripts";
$files = scandir($scripts_dir);
// var_dump($files);
$foundInterns = [];
$foundInternsHtml = "";
$Pass = "green";
$Fail = "red";
if(count($files) > 0){
	//Here Loop in all found files and try to get the right ones.
	foreach($files AS $file){
		$fileInformation = explode(".", $file);
		
		//Check if the found file is valid First
		if(is_dir($scripts_dir."/".$file)){
			// echo $file." is skipped because it looks strange!\n";
			continue;
		}
		//Now gwt thw file extension to determin w=hich command should be used
		
		$results = "";
		//Here the Extensiol should be the last data
		if(count($fileInformation) > 1){
			//Here the file has a name and an extension
			$extension = $fileInformation[(count($fileInformation) - 1)];
			$command = "";
			// var_dump($extension);
			if(array_key_exists(strtolower($extension), $supported_languages)){
				$command = $supported_languages[strtolower($extension)]." ".$scripts_dir."/".$file;
				//Here Make sure to return the text from command
				$results = exec($command);
			} else {
				$results = "";
			}
		} else {
			$results = "";
		}
		$internPassed = [];
		$internPassedHTML = "";
		if(trim($results)){
			$rslt = "Pass";

			//Here Get the email from the string
			$emailsMatch = [];
			$user_email = "";
			$internPassed["file"] = $file;
			$internPassed['output'] = $results;
			if(preg_match("/\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+/", $results, $emailsMatch)){
				// var_dump($emailsMatch, "\n");
				foreach($emailsMatch AS $foundEmail){
					// var_dump($foundEmail, "<br />");
					if(preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/", trim($foundEmail))){
						$user_email = trim($foundEmail);
						break;
					}
				}
			}
			//Here Extract the Required Information 
			$removeHelloword = preg_split("/(Hello World, this is )/", $results);
			$results = $removeHelloword[1];
			//Here Get the intern full name from the string returned from its data
			$splittedInformation = preg_split("/(with HNGi7 ID)/", $results);

			$full_name = str_replace("Hello World, this is ", "", trim($splittedInformation[0])) ;
			if(!trim($full_name) || preg_match("/[^a-zA-Z0-9_ ]/", $full_name)){
				$rslt = "Fail";
			}
			$remainingPart = $splittedInformation[1];

			$internPassedHTML = "Hello World, this is <b>".$full_name."</b> with ";
			$internPassed['name'] = $full_name;

			//Here Extraxt the Intern ID
			$splittedInformation = preg_split("/( using )/", $remainingPart);

			$hgni7_id = trim($splittedInformation[0]);
			if(!trim($hgni7_id) || !preg_match("/^HNG-[0-9]{5}$/", $hgni7_id)){
				$rslt = "Fail";
			}
			$internPassedHTML .= "HNGi7 ID: <b>".$hgni7_id."</b> using ";
			$internPassed['id'] = $hgni7_id;

			$internPassed['email']		= $user_email;
			//remove the last strings informations
			$language = preg_split("/( for stage 2 task)/", trim($splittedInformation[1]))[0];

			if(!trim($language)){
				$rslt = "Fail";
			}
			$internPassedHTML .= "language: <b>".$language."</b> for stage 2 task<br />TEST RESULT:<span style='font-weight: bold; font-size: 18px; color:".($$rslt)."'>".$rslt."</span> email: <b>".$user_email."<b><hr />";
			$internPassed['language'] 	= $language;
			
			$internPassed['status'] 	= $rslt;
		}
		// 
		if(count($internPassed) == 7){

			$foundInterns[] = $internPassed;
		}
		$foundInternsHtml .= "<br />".$internPassedHTML;

	}
}

if(isset($_GET['json'])){
	echo json_encode($foundInterns);
} else {
	echo $foundInternsHtml;
}
?>