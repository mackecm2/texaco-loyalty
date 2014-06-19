drop table If exists UserActions;

create table UserActions
(
	UserName	varchar(20) not null,
	CreationDate	TimeStamp,
	MemberNo	INTEGER not null,
	UNIQUE INDEX( UserName, MemberNo )
);
