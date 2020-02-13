<?php
use \Hcode\Model\User;

function formatPrice($vlprice)
{

	if(!$vlprice > 0) $vlprice = 0;

	return number_format($vlprice,2,",",".");

}

function checkLogin($inadmin = true)
{

	return User::checkLogin($inadmin);

}


function getUserName()
{

	$user = (array)User::getFromSession();

	foreach ($user as $key => $value) 
	{		
		foreach ($value as $keys => $values) 
		{
			if($keys==="deslogin")
			{
				return $values;
			}	

		}
	}

}



?>