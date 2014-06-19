drop table If exists Cards;

create table Cards
(
	CardNo		char(20) PRIMARY KEY,
	MemberNo	BIGINT,
	LastSwipeLoc	integer null,
#	PrimaryCard	enum( 'N', 'Y') not null,
	LastSwipeDate	DateTime null,
	FirstSwipeDate  DateTime null,
	TotalSwipes	integer default 0,
	TotalSpend	integer default 0,
	FuelSpend	integer default 0,
	ShopSpend	integer default 0,
	IssueDate	Date,
	LostDate	Date,
#	StopDate	Date,
	StoppedPoints	integer not null default 0,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( MemberNo )
);

#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853363, 1, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853364, 2, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853365, 2, 'Cards.sql' );

#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853366, 3, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853367, 4, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853368, 4, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853369, 5, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853370, 5, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200022853371, 5, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200026595140, 6, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200041033742, 7, 'Cards.sql' );
#insert into Cards (CardNo, MemberNo,  CreatedBy ) values ( 7076550200040966643, 8, 'Cards.sql' );
