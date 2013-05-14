<?php


$app->hook('slim.before', function(){

		$app = Slim::getInstance();
		if(isset($_SESSION["role"])){
			$app->view()->appendData(array('role' => $_SESSION["role"]));
		}
		
});


// ACL VALIDATION 
$authAdmin = function  ( $role = 'member') {

    return function () use ( $role ) {

        $app = Slim::getInstance();

        // Check for password in the cookie
        if($app->getEncryptedCookie('auth_x',false) != $role ){

            $app->redirect('/login');
        }
    };
};







$authApplication= function  ( $role = 'representative') {
    
    return function () use ( $role ) {

        $app = Slim::getInstance();
      
        if(!isset($_SESSION["user_id"])){
        	$app->redirect('/login');
        }
       

        
         //Check for password in the cookie
        if($app->getEncryptedCookie('auth_rep',false) != $role ){
        	  $_SESSION["last_url"] = $app->request()->getResourceUri();
            $app->redirect('/login');
        }
    };
};






function curPageName() {
 return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
}



$app->map('/login', function () use ($app) {

    // Test for Post & make a cheap security check, to get avoid from bots

    if($app->request()->isPost() && sizeof($app->request()->post()) >= 2)
    {
        // Don't forget to set the correct attributes in your form (name="user" + name="password")
        $post = (object)$app->request()->post();

        
          
         // if we have credentials set.
        if(isset($post->user) && isset($post->password) && isset($post->lang))
        {
          
        	 // fetch for a user with given credentials
        	 $user = R::findOne("user","username=:username AND password=:password", array( ":username" => $post->user , ":password" => $post->password));
        	 

        	 // if recognized user
        	 if($user){
        	 	
        	 	     $_SESSION["role"] = $user->role;
        	 	     
        	 	     if($user->role == "admin" || $user->role == "superadmin"){
        	 	     	
        	 	     	   $_SESSION["role"] = $user->role;
        	 	     	   
        	 	     	   $app->setEncryptedCookie('auth_x','admin');
                     $_SESSION["user_id"] = $user->id;
        	 	     	   
        	 	     	   $app->redirect('admin'); 

        	 	     }else{
        	 	              // set sessions
					                $_SESSION["user_id"] = $user->id;
					                $_SESSION["lang"] = $post->lang;
					                
					                
					                // update user's last access
					                $user->last_access = R::isoDateTime();
					                R::store($user);
					                
					                // set a cookie just to remind.
					                $app->setEncryptedCookie('auth_rep','representative');
					                
					                if(isset($_SESSION["last_url"])){
					                  $app->redirect($_SESSION["last_url"]);
					                }else{
					                   $app->redirect('/');  
					                }
        	 	     }
        	 	
    	 	
        	 	
        	 }else{
        	 	$app->flash('error', 'Invalid Credentials');
        	 	$app->redirect('login');
        	 }
        	 
        } 
        else
        {
            $app->redirect('login');
        }
    }
            // render login
    $app->render('login.html');

})->via('GET','POST')->name('login');






$app->map('/logout', function () use ($app) {

    $app->setEncryptedCookie('auth_x',null);
    $app->setEncryptedCookie('auth_rep',null);
    
    
 
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);
    
    

    $app->redirect('/');

})->via('GET','POST')->name('logout');







/*

$app->map('/login', function () use ($app) {

    // Test for Post & make a cheap security check, to get avoid from bots
   
    if($app->request()->isPost() && sizeof($app->request()->post()) >= 2)
    {
        // Don't forget to set the correct attributes in your form (name="user" + name="password")
        $post = (object)$app->request()->post();

        if(isset($post->user) && isset($post->password))
        {
        	
        	
        	// fetch for a user with given credentials
           $user = R::findOne("admin","username=:username AND password=:password", array( ":username" => $post->user , ":password" => $post->password));
        	
           if($user){
            
                // set sessions
                $_SESSION["user_id"] = $user->id;
                $_SESSION["lang"] = "en";
                
                
                // update user's last access
                $user->last_access = R::isoDateTime();
                R::store($user);

	             $app->setEncryptedCookie('auth_x','admin');
	             $app->redirect('admin');        
            
           }else{
            $app->flash('error', 'Invalid Credentials');
            $app->redirect('/login');
           }
          

        } 
        else
        {
            $app->redirect('login');
        }
    }
            // render login
    $app->render('login.html');

})->via('GET','POST')->name('login');


*/
