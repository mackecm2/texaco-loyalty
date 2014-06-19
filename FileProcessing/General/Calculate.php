<?php

// Automatically generated script file that produces the code to calculate bonuses
// Script run Wed, 15 Oct 2008 18:30:01 +0100


error_reporting( E_ALL );


function CalculateProductVolumeBonus()
{
    global $gTransactionData, $gUserData, $gSiteData, $gProductData, $gDeptData;
    $rBonus = 0;
    $SectionValue = $gProductData->volume;
    return $rBonus;
}
function CalculateProductValueBonus()
{
    global $gTransactionData, $gUserData, $gSiteData, $gProductData, $gDeptData;
    $rBonus = 0;
    $SectionValue = $gProductData->value;
    return $rBonus;
}
function CalculateTotalBonus()
{
    global $gTransactionData, $gUserData, $gSiteData, $gProductData, $gDeptData;
    $rBonus = 0;
    $SectionValue = $gTransactionData->starValueCurrency;
    if( $SectionValue != 0 AND DateRange( '2008-10-06', '2008-11-02')
     AND (RangeMatch($gSiteData->siteCode, '886839,886840,886841,886842,886843,886844,886845,886846') ) )
    {
        $rBonus = Bonuses( 1, 100, 'CO-OPLocat', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-07-23', '')
     AND (HigherCard( $gUserData->cardNo, '7076550200814000008' )
        AND LowerCard( $gUserData->cardNo, '7076550200824073864' )
        AND LowerSwipes( $gUserData->totalSwipes, '1' ) ) )
    {
        $rBonus = Bonuses( 2, 100, 'TriplePts', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-08-15', '2008-09-12')
     AND (ExpressionMatch( $gSiteData->siteCode, '886401' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Pycombe', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-06-30', '2008-07-13')
     AND (ExpressionMatch( $gSiteData->siteCode, '251208' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'LenhamSSTN', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-04-21', '2008-05-18')
     AND (ExpressionMatch( $gSiteData->siteCode, '251224' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Chatham01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-04-09', '2008-05-10')
     AND (ExpressionMatch( $gSiteData->siteCode, '886591' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'PE MabonRd', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-03-10', '2008-04-07')
     AND (ExpressionMatch( $gSiteData->siteCode, '490407' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Lowers Gar', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-03-10', '2008-04-07')
     AND (ExpressionMatch( $gSiteData->siteCode, '886821' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Merriot Se', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-03-10', '2008-04-07')
     AND (ExpressionMatch( $gSiteData->siteCode, '258087' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Three Coun', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2008-01-14', '2008-02-11')
     AND (ExpressionMatch( $gSiteData->siteCode, '490374' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Maiden New', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-06-18', '2007-07-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '258087' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'ThreeCount', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-07-20', '2007-08-17')
     AND (ExpressionMatch( $gSiteData->siteCode, '886780' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Bolsover01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-06-16', '2007-07-01')
     AND (ExpressionMatch( $gSiteData->siteCode, '491515' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'SickleHolm', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-06-01', '2007-07-10')
     AND (ExpressionMatch( $gSiteData->siteCode, '257811' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Shepton01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-06-12', '2007-07-10')
     AND (ExpressionMatch( $gSiteData->siteCode, '886778' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Westcross0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-06-12', '2007-07-10')
     AND (ExpressionMatch( $gSiteData->siteCode, '886777' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Sussex01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-05-01', '2007-05-29')
     AND (ExpressionMatch( $gSiteData->siteCode, '886771' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Yelverton0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-03-19', '2007-04-16')
     AND (ExpressionMatch( $gSiteData->siteCode, '610857' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Pearce01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-03-27', '2007-04-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886737' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Tuffins01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-04-02', '2007-04-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886764' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Somerset01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-08-02', '2007-08-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886740' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Steeles01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-12-20', '2008-01-17')
     AND (ExpressionMatch( $gSiteData->siteCode, '886231' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Trostre01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-07-09', '2007-08-06')
     AND (ExpressionMatch( $gSiteData->siteCode, '886749' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Weybrook01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-08-06', '2007-09-03')
     AND (ExpressionMatch( $gSiteData->siteCode, '886750' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'CaleGreen0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-09-28', '2007-10-26')
     AND (ExpressionMatch( $gSiteData->siteCode, '255493' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'PaceRidge0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-10-01', '2007-10-31')
     AND (ExpressionMatch( $gSiteData->siteCode, '491345' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'RegentPark', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-09-28', '2007-10-26')
     AND (ExpressionMatch( $gSiteData->siteCode, '610872' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Pedestal01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-10-01', '2007-10-21')
     AND (ExpressionMatch( $gSiteData->siteCode, '886783' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Church01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-09-27', '2007-10-25')
     AND (ExpressionMatch( $gSiteData->siteCode, '253368' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Wimbourne0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-09-20', '2007-10-18')
     AND (ExpressionMatch( $gSiteData->siteCode, '886097' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Scholes01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-09-20', '2007-10-18')
     AND (ExpressionMatch( $gSiteData->siteCode, '251389' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'CrownPoint', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-08-08', '2007-08-31')
     AND (ExpressionMatch( $gSiteData->siteCode, '886447' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Threeways', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-10-01', '2007-10-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886775' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'MisterC01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-10-01', '2007-10-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886769' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'LordsRoss0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-10-24', '2007-11-21')
     AND (ExpressionMatch( $gSiteData->siteCode, '886715' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'PaceDitton', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-11-27', '2006-12-25')
     AND (ExpressionMatch( $gSiteData->siteCode, '886691' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'St Chads01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-11-01', '2006-11-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886696' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Thompson1', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-11-01', '2006-11-29')
     AND (ExpressionMatch( $gSiteData->siteCode, '886661' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Skippool2', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-11-01', '2006-11-29')
     AND (ExpressionMatch( $gSiteData->siteCode, '886725' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Cunliffes0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-09-11', '2006-10-08')
     AND (ExpressionMatch( $gSiteData->siteCode, '492073' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Oxenholme1', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-28', '2006-05-26')
     AND (ExpressionMatch( $gSiteData->siteCode, '886138' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'SpringBank', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-28', '2006-05-26')
     AND (ExpressionMatch( $gSiteData->siteCode, '886105' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Accrington', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-28', '2006-05-26')
     AND (ExpressionMatch( $gSiteData->siteCode, '491809' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'GriffinHea', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-07', '2006-05-01')
     AND (ExpressionMatch( $gSiteData->siteCode, '253385' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Maybury01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-09-11', '2006-10-08')
     AND (ExpressionMatch( $gSiteData->siteCode, '886681' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Corner01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-07', '2006-05-05')
     AND (ExpressionMatch( $gSiteData->siteCode, '886626' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Spar01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-10', '2006-05-08')
     AND (ExpressionMatch( $gSiteData->siteCode, '491052' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'MSSuper01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-10', '2006-05-08')
     AND (ExpressionMatch( $gSiteData->siteCode, '886641' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Robinsons0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2007-03-01', '2007-05-01')
     AND (ExpressionMatch( $gSiteData->siteCode, '251476' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Exhall Ope', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-06', '2006-05-04')
     AND (ExpressionMatch( $gSiteData->siteCode, '253414' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'WestNorwoo', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-04-28', '2006-05-26')
     AND (ExpressionMatch( $gSiteData->siteCode, '886459' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Tarporley', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-05-15', '2006-06-12')
     AND (ExpressionMatch( $gSiteData->siteCode, '886645' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Cathcart01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-08-01', '2006-08-29')
     AND (ExpressionMatch( $gSiteData->siteCode, '886687' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Canal01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-07-24', '2006-08-21')
     AND (ExpressionMatch( $gSiteData->siteCode, '886705' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Portway01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-06-12', '2006-07-10')
     AND (ExpressionMatch( $gSiteData->siteCode, '886683' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Cottage01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-06-12', '2006-07-10')
     AND (ExpressionMatch( $gSiteData->siteCode, '886684' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Farndon01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-06-12', '2006-07-10')
     AND (ExpressionMatch( $gSiteData->siteCode, '886685' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Normanton0', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-05-22', '2006-09-18')
     AND (ExpressionMatch( $gSiteData->siteCode, '886698' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Gorslas01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-05-22', '2006-09-18')
     AND (ExpressionMatch( $gSiteData->siteCode, '886698' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Groslas01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-01-30', '2006-02-28')
     AND (ExpressionMatch( $gSiteData->siteCode, '886654' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Hollinw01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-01-30', '2006-02-28')
     AND (ExpressionMatch( $gSiteData->siteCode, '886661' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Skippool1', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-11-14', '2005-12-12')
     AND (ExpressionMatch( $gSiteData->siteCode, '886661' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'SKIPPOOL', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0  AND (ExpressionMatch( $gUserData->promoCode, 'GrpBonus' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'GrpBonus', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-11-02', '2005-11-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '492073' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Oxenholme', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-11-01', '2005-11-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886657' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Chrysler G', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-11-01', '2005-11-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886647' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Cable S/St', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-01-16', '2006-02-06')
     AND (ExpressionMatch( $gSiteData->siteCode, '886666' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Kingsbridg', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-11-28', '2005-12-26')
     AND (ExpressionMatch( $gSiteData->siteCode, '886665' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Staddiscom', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-12-12', '2006-01-09')
     AND (ExpressionMatch( $gSiteData->siteCode, '251193' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Wellington', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-12-20', '2005-01-18')
     AND (ExpressionMatch( $gSiteData->siteCode, '886673' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Broadway', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-02-06', '2006-03-06')
     AND (ExpressionMatch( $gSiteData->siteCode, '886670' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'LomasKingt', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-01-23', '2006-02-20')
     AND (ExpressionMatch( $gSiteData->siteCode, '886662' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'BarrasHeat', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2006-01-16', '2006-02-16')
     AND (ExpressionMatch( $gSiteData->siteCode, '886675' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'StationRd', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-10-17', '2005-11-14')
     AND (ExpressionMatch( $gSiteData->siteCode, '886660' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'SALTNEY', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-10-10', '2005-11-07')
     AND (ExpressionMatch( $gSiteData->siteCode, '886655' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Marland', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-10-01', '2005-10-29')
     AND (ExpressionMatch( $gSiteData->siteCode, '886649' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Chivers', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-08-05', '2005-09-02')
     AND (ExpressionMatch( $gSiteData->siteCode, '886644' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'DblOpen1', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0  AND (ExpressionMatch( $gUserData->promoCode, 'HomeSiteCl' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'HomeSiteCl', 'Total', false, 2500, 25,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-04-01', '2005-04-30')
     )
    {
        $rBonus = Bonuses( 0, 100, 'AprilScrat', 'Total', false, 3000, 20,$SectionValue,false);
    }
    if( $SectionValue != 0  AND (ExpressionMatch( $gUserData->promoCode, 'Bereweeke' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Bereweeke', 'Total', false, 2500, 25,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-06-25', '2005-07-22')
     AND (ExpressionMatch( $gSiteData->siteCode, '611528' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'VMWMtrs', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2005-05-03', '2005-05-31')
     AND (RangeMatch($gSiteData->siteCode, '610705, 886641, 886601') ) )
    {
        $rBonus = Bonuses( 1, 100, 'DbleSites1', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0  AND $gUserData->PromoHitsLeft > 0  AND (ExpressionMatch( $gUserData->promoCode, 'Q8Welcome' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'Q8Welcome', 'Total', false, 0, 0,$SectionValue,true);
    }
    if( $SectionValue != 0 AND DateRange( '2005-03-02', '2005-03-30')
     AND (ExpressionMatch( $gSiteData->siteCode, '886640' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'DoublePts', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0  AND $gUserData->PromoHitsLeft > 0  AND (ExpressionMatch( $gUserData->promoCode, 'WELCOME25' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'WELCOME25', 'Total', false, 2500, 25,$SectionValue,true);
    }
    if( $SectionValue != 0  AND (ExpressionMatch( $gUserData->promoCode, 'SITECLS01' ) ) )
    {
        $rBonus = Bonuses( 1, 100, 'SITECLS01', 'Total', false, 0, 0,$SectionValue,false);
    }
    if( $SectionValue != 0 AND DateRange( '2004-10-01', '2004-10-31')
     )
    {
        $rBonus = Bonuses( 0, 100, 'October30', 'Total', false, 3000, 20,$SectionValue,false);
    }
    if( $SectionValue != 0  )
    {
        $rBonus = Bonuses( 1, 100, 'Standard', 'Total', false, 0, 0,$SectionValue,false);
    }
    return $rBonus;
}
function CalculateVisitBonus()
{
    global $gTransactionData, $gUserData, $gSiteData, $gProductData, $gDeptData;
    $rBonus = 0;
    $SectionValue = 1;
    return $rBonus;
}
function CalculateDeptBonus()
{
    global $gTransactionData, $gUserData, $gSiteData, $gProductData, $gDeptData;
    $rBonus = 0;
    $SectionValue = 0;
    return $rBonus;
}
function CalculatePeriodBonus()
{
    global $gTransactionData, $gUserData, $gSiteData, $gProductData, $gDeptData;
    $rBonus = 0;
    $SectionValue = $gUserData->periodSpend;
    return $rBonus;
}


?>
