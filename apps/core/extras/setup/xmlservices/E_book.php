<?php

$Server = $_SERVER["SERVER_ADDR"];
$url="http://".$Server."/xmlservices";

//
// Get the 2 variables (Page and phone).
$Page = $_GET['Page']; 		// Page index
$idx_phone = $_GET['phone'];	// phone's address book index

if ( $Page == 0  ) 
	{
		$Page = 1;
	}

if ($idx_phone)
	{
	//
	// Contact Selected = ok
	//
	$Menu  = "<CiscoIPPhoneDirectory>\n";
	$Menu .= "<Title>Phone Directory</Title>\n";
	$Menu .= "<Prompt>Please select one</Prompt>\n";
	//
	// Load variable E_address_book array from database 
	//
	exec("sqlite3 -separator '-' -nullvalue 'no-mail' /var/www/db/address_book.db \"select * from contact;\"",$E_address_book);
	list($idx,$name,$last_name,$phone_number,$extension,$mail)=explode("-",$E_address_book[$idx_phone]);
	//
	// Repare to displaying.
	//
	$Menu .= "<DirectoryEntry>\n";
	$Menu .= "<Name>$name $last_name </Name>\n";
	$Menu .= "<Telephone>$phone_number</Telephone>\n";
	$Menu .= "</DirectoryEntry>\n";
	$Menu .= "</CiscoIPPhoneDirectory>\n";
	//
	// Displaying the result on the Cisco extension.
	//
	echo $Menu;

	}

else

	{
	//
	// Displaying the address book by 31 Items/page
	//
	$Menu  = "<CiscoIPPhoneMenu>\n";
	$Menu .= "<Title>Phone Directory</Title>\n";
	$Menu .= "<Prompt>Please select one</Prompt>\n";
	//
	// consult address_book.db from contact and put into the variables
	//
	exec("sqlite3 -separator '-' -nullvalue 'no-mail' /var/www/db/address_book.db \"select * from contact;\"",$E_address_book);
	$p=0;

	while ($p <= count($E_address_book)-1)
		{	
		if ((($Page-1)*31) + $p < ($Page*31)) 
			{
			//
			// Displaying one item by loop. So 31 items/page 
			//
			$idx_array = $p+(($Page - 1) * 31);
			list($idx,$name,$last_name,$phone_number,$extension,$mail)=explode("-",$E_address_book[$idx_array]);
			$Menu .= "<MenuItem>\n";
			$Menu .= "<Name>$name $last_name </Name>\n";
			$Menu .= "<URL>$url/E_book.php?phone=$idx_array</URL>\n";
			$Menu .= "</MenuItem>\n";
			}
		$p++;
		}
	$Page++;
	//
	// Displaying the link with the next page.
	//
	$Menu .= "<MenuItem>\n";
	$Menu .= "<Name>Next </Name>\n";
	$Menu .= "<URL>$url/E_book.php?Page=$Page</URL>\n";
	$Menu .= "</MenuItem>\n";
	$Menu .= "</CiscoIPPhoneMenu>\n";
	//
	// Displaying the result on the Cisco extension.
	//
	echo $Menu;
	
	}

?>
