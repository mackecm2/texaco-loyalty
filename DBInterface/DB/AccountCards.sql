drop table If exists AccountCards;

create table AccountCards
(
	GAccountNo	int primary Key,
	CardNo		char(19),
	TotalLoads	integer DEFAULT 0,
	Active		enum( 'Y', 'N', 'P' ) not null,
	CreationDate	TimeStamp,
	CreatedBy	varchar(20)
);

