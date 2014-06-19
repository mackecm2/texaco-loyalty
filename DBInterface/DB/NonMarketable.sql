drop table If exists NonMarketableMembers;

create table NonMarketableMembers
(
	MemberNo	BIGINT primary key,
	PersonIsAngry	int, 
	PersonIsExplitve int,
	PersonIsSuspect int, 
	PersonIsExternalList int, 
	PersonIsHoaxCaller int,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	RevisedDate	DateTime,
	RevisedBy	varchar(20)
);
