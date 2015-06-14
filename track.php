<?php

$db = new mysqli(//info);
if($db->connect_error){
  die ('Connect Error ('.$db->connect_errno.')'.$db->connect_error);
}

$college = strtoupper($db->real_escape_string($_POST['college']));
$dept = strtoupper($db->real_escape_string($_POST['dept']));
$num = $db->real_escape_string($_POST['num']);
$section = strtoupper($db->real_escape_string($_POST['section']));
$phone = $db->real_escape_string($_POST['phone']);
$phone = str_replace("-", "", $phone);

$q = $db->query("SELECT * FROM `courses` WHERE `number` = '".$phone."' AND `college` = '".$college."' AND `department` = '".$dept."' AND `course` = '".$num."' AND `section` = '".$section."'");
if (mysqli_num_rows($q) > 0) {
  echo "You're already tracking this course";
} else {

  $query = $college."+".$dept."+".$num;

  $semester = "Spring 2013";

  $url = "http://www.bu.edu/htbin/class.pl?t=".$query;
  $html = file_get_contents($url);
  $newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
  $content = str_replace($newlines, "", html_entity_decode($html));
  $data = array();
  for ($i = 1; $i < 4; $i++) {
    $find = '<a name="a'.$i.'">';
    if (strpos($content, $find) > 0) {
      $temp = explode($find, $content);
      $temp2 = explode("</a>", $temp[1]);
      if (strpos($temp2[0], $semester) > 0) {
        //echo strpos($temp[1], "<table border=0 cellpadding=3>");
        $cleanse = html_entity_decode($temp[1]);
        $cur = explode("<table border=0 cellpadding=3>", $cleanse);
        $finale = explode("<font size=2>", $cur[1]);
        for ($j = 0; $j < count($finale); $j++) {
          $clean = explode("</font>", $finale[$j]);
          if (strpos($clean[0], "red>") > 0) {
            $whythefuckisthisred = explode("red>", $clean[0]);
            array_push($data, $whythefuckisthisred[1]);
          } else {
            array_push($data, $clean[0]);
          }
        }
      }
    }
  }

  $yes = false; //just a boolean to test if we ever had a match!
  for ($i = 0; $i < count($data); $i++) {
    if (strtoupper($data[$i]) == $section) {
      $yes = true;
      echo "Successfully tracked!<br>";
      $db->query("INSERT INTO `courses` (`number`, `college`, `department`, `course`, `section`) VALUES ('".$phone."', '".$college."', '".$dept."', '".$num."', '".$section."')");
      echo "<br>Currently:<br>";
      echo "Section: ".$data[$i]."<br>";
      echo "Open Seats: ".$data[$i+1]."<br>";
    }
  }

  if (!$yes) 
    echo "No course matches.";
  } 
}
  

?>
