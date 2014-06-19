<?php

// Atomatically generated script file that produces the code to calculate bonuses
// Script run Mon,  2 Feb 2004 20:13:59 +0000


function CalculateProductBonus()
{
     if( ProductRange( 5, 5) and DateRange( '2000-01-01', '2005-01-01'))
     {
          return  Bonuses( 500, 1, 1, false);
     }
     return 0;
}


?>
