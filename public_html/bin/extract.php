<?php


// http://twig.sensiolabs.org/doc/extensions/i18n.html



require_once 'vendor/Twig/Autoloader.php';
Twig_Autoloader::register();

require_once 'vendor/Twig/Extensions/Autoloader.php';
Twig_Extensions_Autoloader::register();



$tplDir = dirname(__FILE__).'/templates';
$tmpDir = 'tmp/cache/';
$loader = new Twig_Loader_Filesystem($tplDir);

// force auto-reload to always have the latest version of the template
$twig = new Twig_Environment($loader, array(
    'cache' => $tmpDir,
    'auto_reload' => true
));
$twig->addExtension(new Twig_Extensions_Extension_I18n());
// configure Twig the way you want

echo "<pre>";

if(file_exists($tmpDir)){
	$twig->clearCacheFiles();
	recursive_remove_directory($tmpDir);	
}


@mkdir($tmpDir);

//die();
// iterate over all your templates
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDir), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
	
		$findme = '.svn';
		$pos = strpos($file, $findme);

    //echo $file ."\n";

	//$ext = pathinfo($file  , PATHINFO_EXTENSION);
	if($pos === FALSE){
		$filen =   str_replace($tplDir.'/', '', $file);
   
		if(isset($filen) && $filen!= "" && $filen != NULL ){
			 //echo $filen ."\n";
			 try{ 
			 	echo $filen ."\n";
			 	$twig->render($filen,array());
			 	//$twig->loadTemplate($filen);
			 }catch(Exception $ex){
			 	
			 	//echo $filen ."\n";
			 };
		}
		
	}
	
 


}





function recursive_remove_directory($directory, $empty=FALSE)
{
  // if the path has a slash at the end we remove it here
  if(substr($directory,-1) == '/')
  {
    $directory = substr($directory,0,-1);
  }

  // if the path is not valid or is not a directory ...
  if(!file_exists($directory) || !is_dir($directory))
  {
    // ... we return false and exit the function
    return FALSE;

  // ... if the path is not readable
  }elseif(!is_readable($directory))
  {
    // ... we return false and exit the function
    return FALSE;

  // ... else if the path is readable
  }else{

    // we open the directory
    $handle = opendir($directory);

    // and scan through the items inside
    while (FALSE !== ($item = readdir($handle)))
    {
      // if the filepointer is not the current directory
      // or the parent directory
      if($item != '.' && $item != '..')
      {
        // we build the new path to delete
        $path = $directory.'/'.$item;

        // if the new path is a directory
        if(is_dir($path)) 
        {
          // we call this function with the new path
          recursive_remove_directory($path);

        // if the new path is a file
        }else{
          // we remove the file
          unlink($path);
        }
      }
    }
    // close the directory
    closedir($handle);

    // if the option to empty is not set to true
    if($empty == FALSE)
    {
      // try to delete the now empty directory
      if(!rmdir($directory))
      {
        // return false if not possible
        return FALSE;
      }
    }
    // return success
    return TRUE;
  }
}
