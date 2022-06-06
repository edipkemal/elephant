<?php
/*
$dataFile = fopen("dataFile.txt", "r") or die("Unable to open file");
$lastLine="";


while($line = fgets($dataFile))
  $lastLine=$line;


$data = explode(",", $lastLine);

$time=$data[0];
$moisture=round(abs($data[1]-950)/10,2);
$humidity=$data[2];
$temperature=$data[3];
$rain=$data[4];
$irrigation=$data[5];*/


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

// meta veri ve başlıklar
//var_dump(stream_get_meta_data($stream));

// esas veri
$json = json_decode(stream_get_contents($stream),true);
$feeds = $json['feeds'];

$time = date('d-m-Y');
$temperature = 32; //$feeds[0]['field1'];
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
if($temperature=="nan"){
  $temperature="60.10";
  $humidity="36.0";
}

//fclose($dataFile);

$url=$_SERVER['REQUEST_URI'];
header("Refresh: 1; URL=$url");  // Refresh the webpage every 5 seconds
?>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" type="text/css" href="styles.css">
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