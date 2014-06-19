drop table If exists Monthly;

create table Monthly
(
	CardNo		char(19),
	MonthYear	char(6), 
	SpendVal	DECIMAL(8,2), 
	PointsEarned	int, 
#	PointsRedeemed	int, 
	Swipes		int, 
	CreationDate	Date,
	CreatedBy	char(20),
#	PRIMARY KEY( AccountNo, MonthYear )
	INDEX( CardNo )
);

drop table If exists MonthlyMember;

create table MonthlyMember
(
	MemberNo	bigint,
	MonthYear	char(6), 
	SpendVal	DECIMAL(8,2), 
	PointsEarned	int, 
	PointsRedeemed	int, 
	AdjPlus		int, 
	AdjMinus	int, 
	Swipes		int, 
	CreationDate	Date,
	CreatedBy	char(20),
	INDEX( MemberNo )
);
