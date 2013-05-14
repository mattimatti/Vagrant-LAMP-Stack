<?php
class Twig_Extension_Slim extends Twig_Extension
{
    public function getName()
    {
        return 'slim';
    }

    public function getFunctions()
    {
    	
        return array(
            'urlFor' => new Twig_Function_Method($this, 'urlFor'),
            'ml' => new Twig_Function_Method($this, 'multiLang')
        );
    }

    public function urlFor($name, $params = array(), $appName = 'default')
    {
        return Slim::getInstance($appName)->urlFor($name, $params);
    }


    public function multiLang()
    {
        /*$prop = $prop. "_". "en";
        return $obj->$prop;*/
      return "ss";
    }  



}
