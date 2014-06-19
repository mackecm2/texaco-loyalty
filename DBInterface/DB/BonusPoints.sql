drop table If exists BonusPoints;

create table BonusPoints
(
	PromotionCode   varchar(10) PRIMARY KEY,
	Priority	integer,

	BonusName	varchar(50),

	StartDate	Date,
	EndDate		Date,

	AppliesTo	varchar( 10 ),
	Threshold	integer default 0,
	BonusPoints	integer default 0,
	PerQuantity	integer default 1,
	Exclude		integer,
	ThresholdPts	integer default 0,
	MaximumHits	integer default -1,

	RevisionDate	Timestamp,
	RevisedBy	varchar(20),
	CreationDate	TimeStamp,
	CreatedBy	varchar(20)
);

drop table if exists BonusCriteria;

create table BonusCriteria
(
	PromotionCode   varchar(10),
	CriteriaNo	integer,
	
	FieldName	varchar(20),
	ComparisionType varchar(20),
	Boolean		varchar(5),

	ComparisionCrteria varchar(50)
);

drop table if exists FieldComparisions;

create table FieldComparisions
(
	FieldName	varchar(20),
	ComparisionType varchar(20),
	PopulateType	varchar(10),
	Populate	varchar(100),
	FieldTest	varchar(100)

);

INSERT into FieldComparisions values ( "SiteID", "=", "Text", null, "ExpressionMatch( $gSiteData->siteCode, %exp )");
INSERT into FieldComparisions values ( "SiteID", "Single", "List", "Select SiteCode, SiteCode from Sites Order by SiteCode", "$gSiteData->siteCode==%exp"  );
INSERT into FieldComparisions values ( "SiteID", "Multiple", "Multi", "Select SiteCode, SiteCode from Sites Order by SiteCode",  "RanageMatch($gSiteData->siteCode, %exp)" );
INSERT into FieldComparisions values ( "AreaID", "=", "Text", null,  "ExpressionMatch( $gSiteData->areaID, %exp )");
INSERT into FieldComparisions values ( "RegionID", "=", "Text", null, "ExpressionMatch( $gSiteData->regionID, %exp )");
INSERT into FieldComparisions values ( "ProductCode", "=", "Text", null, "ExpressionMatch( $gProductData->code, %exp)");
INSERT into FieldComparisions values ( "ProductCode", "Multiple", "Text", null, "RanageMatch( $gProductData->code, %exp)");
INSERT into FieldComparisions values ( "PromotionCode", "=", "List", "Select Distinct PromotionCode, PromotionCode from BonusPoints Order by PromotionCode", "ExpressionMatch( $gUserData->promoCode, %exp )" );
INSERT into FieldComparisions values ( "PeriodSpend", ">", "Text", null, "PeriodCheck( $gUserData->PeriodSpend, %exp )" );


INSERT into BonusPoints (PromotionCode, Priority, BonusName, AppliesTo, BonusPoints, PerQuantity ) values ( "Standard", 99, "Standard Points", "Total", 1, 100 );
INSERT into BonusPoints (PromotionCode, Priority, BonusName, AppliesTo, BonusPoints, PerQuantity ) values ( "DOUBLE", 98, "DOUBLE Points", "Total", 2, 100 );

INSERT into BonusCriteria values ( "DOUBLE", 0, "SiteID", "=", "", 254959 );