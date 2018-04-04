<?php
/*
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true ");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

echo $_SERVER['REQUEST_METHOD'];
echo $_SERVER['HTTP_CONTENT_TYPE'];

return;
exit();
*/

if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
  http_response_code(200);
  //header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Credentials: true ");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Content-Type, Authorization");
  return;
}

if ($_SERVER['REQUEST_METHOD'] != "POST") {
  echo "Go away";
  http_response_code(405);
  return;
}


if ($_SERVER['HTTP_CONTENT_TYPE'] != "application/atom+xml; type=entry") {
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Credentials: true ");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Content-Type, Authorization");
  echo "I only speak atom";
  http_response_code(415);
  return;
}


$xmlData = simplexml_load_string(file_get_contents('php://input'));

$xmlData->registerXPathNamespace("atom", "http://www.w3.org/2005/Atom");
$xmlData->registerXPathNamespace("vdf", "http://www.vizrt.com/types");


if(!empty(array_filter($xmlData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="slug"]/vdf:value'))))
{
  http_response_code(204);
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Credentials: true ");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Content-Type, Authorization");

  //settings
  //$serverPath = "/Users/jasongraening/Documents/_Projects/tmp/";
  $serverPath = "/tmp/";
  $bodyContent;
  $count=0;

  $slug = $xmlData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="slug"]/vdf:value');
  $headline = $xmlData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="title"]/vdf:value');
  $body = $xmlData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="body"]/vdf:value');
  $author = $xmlData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="open_author"]/vdf:value');
  $photos = $xmlData->xpath('/atom:entry/atom:link[@rel="related"]/@href');

  $slug = $slug[0][0];
  $filename = $slug[0][0].'.txt';

  //mkdir
  $dir = $serverPath.$slug;
  if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
  }

  //make rtf
  foreach ($body[0]->div->p as $value) {
    $bodyContent .= $value."\n\n";
  }

  $content  = "Headline: ".$headline[0][0]."\n";
  $content .= "Author: ".$author[0][0]."\n\n";
  $content .= "Body\n";
  $content .= $bodyContent;

  file_put_contents($dir.'/'.$filename , $content);

  //images
  foreach($photos as $photo){
    $count++;
    $photo_href = $photo['href']->__toString();

    $context = stream_context_create(array(
      'http' => array(
          'method' => 'GET',
          'header' => 'Authorization:' . $_SERVER["HTTP_AUTHORIZATION"] . "\r\n"
      )
    ));

    $photoXMLData = simplexml_load_string(file_get_contents($photo_href, false, $context));
    $photoXMLData->registerXPathNamespace("atom", "http://www.w3.org/2005/Atom");
    $photoXMLData->registerXPathNamespace("vdf", "http://www.vizrt.com/types");

    $photoURL = $photoXMLData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="binary"]/vdf:value/atom:link');
    $photoURL = $photoURL[0]['href']->__toString();

    $photoName = $photoXMLData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="binary"]/vdf:value/atom:link');
    $photoName = $photoName[0]['title']->__toString();

    $image = file_get_contents($photoURL, false, $context);
    file_put_contents($dir.'/'.$photoName, $image);
  }
}
else{
  http_response_code(400);
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Credentials: true ");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Content-Type, Authorization");

  echo "No slug exists.";

  //var_dump($body[0]->div->p);
  //print_r($xmlData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field'));
  //print_r($xmlData->xpath('/atom:entry/atom:link/vdf:payload/vdf:field/vdf:value'));

  //photo caption
  //print_r($xmlData->xpath('/atom:entry/atom:link[@rel="related"]/vdf:payload/vdf:field[@name="caption"]/vdf:value'));

  //photo api
  //print_r($xmlData->xpath('/atom:entry/atom:link[@rel="related"]/@href'));

  /*
  $photos = $xmlData->xpath('/atom:entry/atom:link[@rel="related"]/@href');
  $count=0;

  foreach($photos as $photo){
    echo 'in the foreach';


    $count++;
    $photo_href = $photo['href']->__toString();

    //print_r($photo_href);
    //print_r("\n");

    $context = stream_context_create(array(
      'http' => array(
          'method' => 'GET',
          'header' => 'Authorization:' . $_SERVER["HTTP_AUTHORIZATION"] . "\r\n"
      )
    ));

    $photoXMLData = simplexml_load_string(file_get_contents($photo_href, false, $context));
    $photoXMLData->registerXPathNamespace("atom", "http://www.w3.org/2005/Atom");
    $photoXMLData->registerXPathNamespace("vdf", "http://www.vizrt.com/types");

    $photoURL = $photoXMLData->xpath('/atom:entry/atom:content/vdf:payload/vdf:field[@name="binary"]/vdf:value/atom:link');
    $photoURL = $photoURL[0]['href']->__toString();
    print_r($photoURL);

    $image = file_get_contents($photoURL, false, $context);
    file_put_contents('/Users/jasongraening/Documents/_Projects/tmp/savedimage.jpg', $image);


    //file_put_contents("/Users/jasongraening/Documents/_Projects/tmp/${count}-out.xml", $photo);
    //echo "/Users/jasongraening/Documents/_Projects/tmp/${count}-out.xml";
  }
*/
  //print_r($test->xpath('/atom:entry/atom:content/vdf:payload/vdf:field["binary"]/vdf:link/@href'));

  //file_put_contents ('/Users/jasongraening/Documents/_Projects/tmp/vdf.xml' , file_get_contents('php://input'));
  //$xmlData->asXml('/Users/jasongraening/Documents/_Projects/tmp/vdf.xml');
}

?>
