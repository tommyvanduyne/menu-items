<?php 
include_once('superCategories.php');
if (isset($argv) && isset($argv[1]))
{
  $food = $argv[1];
}
else 
{
  $food = false; 
}
get_it_done();
function get_it_done() 
{
  global $food, $superCategories;
  $lines = file("menu-items.csv");
  if ($food) $file_to_write = "temp-$food";
  else $file_to_write = 'temp';
  $catIndex = [];
  $linecount = 0;
  $countIndex = [];
  $lineIndex = [];
  $dupIndex = [];
  //$excludes = ['salad'=>true,'cheese'=>true,'and cheese'=>true];
  foreach ($lines as $line) 
  {
    if (isset($lineIndex[$line])) continue;
    else $lineIndex[$line] = true;
    $line = strtolower($line);
    $line = str_replace('&apos;','\'',$line);
    $line = str_replace('&','and',$line);
    $line = str_replace('(v)','and',$line);
    $line = str_replace('*','',$line);
    $assoc = explode(',',$line);
    $linecount += 1;
    if ($linecount % 1000 == 0) echo $linecount."\n";
    $category = $assoc[0];
    $restaurantName = $assoc[1];
    $menuItem = $assoc[2];
    $price = $assoc[3];
    if (!isset($catIndex[$category]))
      $catIndex[$category] = [];
    $index = '';
    $words = array_values(array_filter(explode(' ',trim($menuItem))));
    
    //check if restaurant and menu item have been seen before
    $dupKey = $restaurantName.$menuItem;
    if (isset($dupIndex[$dupKey])) continue;
    else $dupIndex[$dupKey] = true;
   
    while (count($words) > 0) 
    {
      $index = array_pop($words) . ' ' . $index;
      $index = trim($index);
      if ($food && strpos($index,$food) === false) continue;

      if (!isset($superCategories[$category])) die("this category has not been dealt with: $category");
      $categories = $superCategories[$category];
      //$categories []= 'all';
      $categories = array_values(array_filter($categories));
      foreach ($categories as $cat) {
        if (!isset($countIndex[$cat][$index])) $countIndex[$cat][$index] = 0;
        $countIndex[$cat][$index] += 1;
      }
    }      
  }
  foreach ($countIndex as $cat=>$obj) {
    arsort($countIndex[$cat]);
  } 
  //file_put_contents($file_to_write,print_r($catIndex,true));
  file_put_contents($file_to_write,print_r($countIndex,true));
}
