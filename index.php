<?php

$url = "https://api.thingspeak.com/channels/1759852/feeds.json?api_key=E2QFXBUFNABANL6N&results=1";

$opts = array('http' =>
    array(
        'method' => 'GET',
        'max_redirects' => '0',
        'ignore_errors' => '1'
    )
);

$context = stream_context_create($opts);
$stream = fopen($url, 'r', false, $context);

$json = json_decode(stream_get_contents($stream),true);
$feeds = $json['feeds'];

//$time = date('d-m-Y h:i:s'); // current time 
$time = $feeds[0]['created_at']; //data time
$time =  str_replace("T", " ", $time); //delete if useing current time
$time =  str_replace("Z", "", $time); //delete if using current time
$time = new  DateTime($time, new DateTimeZone('Europe/Istanbul'));
$time->add(new DateInterval('PT' . 180 . 'M'));
//date_default_timezone_set('Europe/Istanbul');
//$time->setTimezone(new DateTimeZone('Europe/Istanbul'));
$time = $time->format('d-m-Y H:i:s');

$temperature = 32; // sensor is broken, if yours working, delete this and uncomment the line at below
//$temperature = $feeds[0]['field1']; 

$humidity = $feeds[0]['field2'];
$moisture = abs((int)($feeds[0]['field3'])-1000)/10;
$rain = $feeds[0]['field4'];
$irrigation = $feeds[0]['field5'];



if($rain){
  $rain="Raining";
}else{
  $rain="Not Raining";
}

if($irrigation==1){
  $irrigation="Started";
}else{
  $irrigation="Stopped";
}

$url=$_SERVER['REQUEST_URI'];
header("Refresh: 2; URL=$url");  // Refresh the webpage every 2 seconds
?>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="nature.png" />
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Lobster&display=swap");
    </style>
    <title>Elephant</title>
  </head>
  <body>
    <div class="content">
      <div class="logo-container">
        <img class="logo" src="nature.png" />
        <span class="name">elephant</span>
      </div>
          <div class="container">
      <div class="data-box">
        <span class="title">Time</span>
        <?php 

        echo '<span class="data">'.$time.'</span>'
        ?>
      </div>
      <div class="data-box">
        <span class="title">Humidity</span>
        <?php 

        echo '<span class="data">'.$humidity.'%</span>'
        ?>
      </div>
      <div class="data-box">
        <span class="title">Temperature</span>
        <?php 
        echo '<span class="data">'.$temperature.' C</span>'
        ?>
      </div>
    </div>
    <div class="container">
      <div class="data-box">
        <span class="title">Soil Moisture</span>
        <?php 
        echo '<span class="data">'.$moisture.'%</span>'
        ?>
      </div>
      <div class="data-box">
        <span class="title">Rain</span>
        <?php 
        echo '<span class="data">'.$rain.'</span>'
        ?>
      </div>
      <div class="data-box">
        <span class="title">Irrigation</span>
        <?php 
        echo '<span class="data">'.$irrigation.'</span>'
        ?>
      </div>
    </div>
    </div>
    </body>
</html>