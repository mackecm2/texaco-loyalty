drop table If exists Statement;

create table Statement
(
	AccountNo	BIGINT,
#	MemberNo	BIGINT,
	StateDate	Date, 
	Balance		int, 
	Mail_seg	varchar(8), 
	Promo_Text	varchar(8), 
	INDEX( AccountNo, StateDate )
);
