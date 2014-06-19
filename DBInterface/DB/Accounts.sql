drop table If exists Accounts;

create table Accounts
(
	AccountNo	BIGINT auto_increment primary key,
	Balance		integer DEFAULT 0,
	TotalShadow	integer default 0,
	TotalRedemp	integer DEFAULT 0,
	FirstRedempDate	Date null,
	LastRedempDate	Date null,
	LastStatement	Date null,
#	StatementMember	bigint,
#	StatementPref	enum( 'N', 'P', 'E', 'S' ) not null,
	RedemptionStopDate  Date null,
	AwardStopDate   Date null,
#	AutoRedeemId 	integer default 0,
	AccountType	char(1),
	MonthlySpend	int,
	HomeSite	integer null,
	VirginNo	varchar(20),
	HomeSiteDate	DateTime,
	RevisedDate	Timestamp,
	CreationDate	DateTime,
	CreatedBy	varchar(20)
);


#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( AccountType, CreatedBy ) values ( 'A',"Accounts.sql" );
#INSERT into Accounts ( AccountType, CreatedBy ) values ( 'A',"Accounts.sql" );
#INSERT into Accounts ( AccountType, CreatedBy ) values ( 'A',"Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );
#INSERT into Accounts ( CreatedBy ) values ( "Accounts.sql" );


