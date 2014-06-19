	</tr>
	</table>
	<?php
	
	echo '&copy'; ?>
	RSM 2000 // <?php echo $_SESSION['subdomain'];
	echo " // Release ";
    echo $_SESSION['rpmrelease'];
    if( $SERVER_NAME_FOR_ALL != "MASTER" ) 
    {
    	echo " // ".$SERVER_NAME_FOR_ALL;
    }
    ?>
</BODY>
</HTML>
