<?php
function GetStatementHistory( $AccountNo )
{
	$sql = "
	(
		SELECT StateDate AS 'Statement Date', Balance, Description AS 'Mail Segment', Promo_Text AS Promotion
		FROM Statement JOIN StatementListCodes USING ( StateDate, Mail_seg )
		WHERE AccountNo = $AccountNo
	)
	UNION
	(
		SELECT StateDate as 'Statement Date', Balance, 
		Description as 'Mail Segment', 
		Promo_Text as Promotion from Statement Join MergeHistory on (Statement.AccountNo = MergeHistory.SourceAccount) 
		JOIN StatementListCodes USING ( StateDate, Mail_seg )
		where MergeHistory.DestinationAccount = $AccountNo	
	)
	Order By 'Statement Date' DESC";

	return DBQueryExitOnFailure( $sql );
}

function GetCampaignHistory( $AccountNo )
{
	$sql = "
	(
		SELECT CreationDate as 'Creation Date', CampaignType, CampaignCode, ListCode, MiscData from CampaignHistory where AccountNo = $AccountNo
	)
	UNION
	(
		SELECT CreationDate as 'Creation Date', CampaignType, CampaignCode, ListCode, MiscData from CampaignHistory Join MergeHistory on (CampaignHistory.AccountNo = MergeHistory.SourceAccount) where MergeHistory.DestinationAccount = $AccountNo
	)
	Order By 'Creation Date' DESC";

	return DBQueryExitOnFailure( $sql );
}

function GetPersonalCampaignHistory( $AccountNo )
{
	$sql = "
	(
		SELECT PromotionCode as 'Promotion Code', StartDate, EndDate,PeriodSpend,PersonalCampaigns.PromoHitsLeft from Members right join PersonalCampaigns using (MemberNo) where AccountNo = $AccountNo
	)
	UNION
	(
		SELECT PromotionCode as 'Promotion Code', StartDate, EndDate,PeriodSpend,PersonalCampaigns.PromoHitsLeft from Members right join PersonalCampaigns using (MemberNo) Join MergeHistory on (Members.AccountNo = MergeHistory.SourceAccount) where MergeHistory.DestinationAccount = $AccountNo
	)
	Order By 'Creation Date' DESC";

	return DBQueryExitOnFailure( $sql );
}


?>