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
  if ($food) $file_to_write = "temp-$food.csv";
  else $file_to_write = 'temp.csv';
  unlink($file_to_write);
  $catIndex = [];
  $linecount = 0;
  $countIndex = [];
  $lineIndex = [];
  $dupIndex = [];
  //$excludes = ['salad'=>true,'cheese'=>true,'and cheese'=>true];
  //first we do a test run
  $awesomeObj = [];
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
    $menuItem = $assoc[2];
    $indices = get_index($menuItem);    
    $awesomeObj[$indices[0]] = true; 
  }  
  $lineIndex = [];
  $linecount = 0;
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
    if (!isset($superCategories[$category])) die("this category has not been dealt with: $category");
    $restaurantName = $assoc[1];
    $menuItem = $assoc[2];
    $price = $assoc[3];
    if (!isset($catIndex[$category]))
      $catIndex[$category] = [];
    
    //check if restaurant and menu item have been seen before
    $dupKey = $restaurantName.$menuItem;
    if (isset($dupIndex[$dupKey])) continue;
    else $dupIndex[$dupKey] = true;
    
    //check if $food
    if ($food && strpos($menuItem,$food) === false) continue; 
    //$indices = get_indices($menuItem);
    //$indices = get_index($menuItem);    
    $indices = get_indices($menuItem,$wordmin=1);
    foreach ($indices as $index) 
    {
      if (!isset($awesomeObj[$index])) continue;
      $categories = $superCategories[$category];
      $categories = array_values(array_filter($categories));
      foreach ($categories as $cat) {
        if (!isset($countIndex[$cat][$index])) $countIndex[$cat][$index] = 0;
        $countIndex[$cat][$index] += 1;
      }
    }      
  }
  foreach ($countIndex as $cat=>$obj) {
    arsort($countIndex[$cat]);
    $countIndex[$cat] = array_slice($countIndex[$cat],0,25);
  } 
  //file_put_contents($file_to_write,print_r($catIndex,true));
  //file_put_contents($file_to_write,print_r($countIndex,true));
  //file_put_contents($file_to_write,print_r($countIndex,true));
  foreach ($countIndex as $cat=>$obj) {
    file_put_contents($file_to_write,$cat."\n",FILE_APPEND);
    foreach ($obj as $food=>$count) 
    {
      file_put_contents($file_to_write,",$food,$count\n",FILE_APPEND);
    }
  } 
}
function get_index($menuItem) 
{
  return [implode(' ',array_map('trim',explode(' ',$menuItem)))];
}
function get_indices($menuItem,$wordmin) 
{
  if (!isset($wordmin)) $wordmin = 1;
  $words = explode(' ',$menuItem);
  array_map('trim',$words);
  $indices = [];
  $count = count($words);
  //var_dump($words);
  foreach ($words as $ix=>$word) 
  {
    $slicelen = $ix+1;
    while ($slicelen <= $count) 
    {
      if ($slicelen-$ix >= $wordmin) {
        $indices []= implode(' ',array_slice($words,$ix,$slicelen-$ix));
      }
      $slicelen += 1;
    }
  }
  //var_dump ($indices);
  //die();
  return $indices;
}
