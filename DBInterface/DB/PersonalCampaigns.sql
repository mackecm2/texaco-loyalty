drop table If exists PersonalCampaigns;

create table PersonalCampaigns
(
	MemberNo	BIGINT,
	PromotionCode	char(10),
	StartDate	Date,
	EndDate		Date,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	primary key( MemberNo, PromotionCode )
);

drop table If exists ArchivePersonalCampaigns;

create table ArchivePersonalCampaigns
(
	MemberNo	BIGINT,
	PromotionCode	char(10),
	StartDate	Date,
	EndDate		Date,
	PeriodSpend	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	primary key( MemberNo, PromotionCode )
);

