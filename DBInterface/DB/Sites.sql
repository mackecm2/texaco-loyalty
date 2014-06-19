drop table If exists Sites;

create table Sites
(
	SiteCode	integer Primary Key,
	UKFuelsCode	integer,
	SiteType	varchar(30),
	LocationCode	integer,
	SiteName	varchar(45),
	Address1	varchar(40),
	Address2	varchar(40),
	Address3	varchar(40),
	Address4	varchar(40),
	Address5	varchar(40),
	PostCode	varchar(10),
	AreaCode	char(5),
	RegionCode	char(7),
	SiteContact	varchar(40),
	AphoneNo	varchar(25),
	AFaxNo		varchar(25),
	CarWash		enum( 'U', 'Y', 'N' ) not null,
	Hr24		enum( 'U', 'Y', 'N' ) not null,
	Vacuum		enum( 'U', 'Y', 'N' ) not null,
	Status		enum( 'O', 'C' ),
	CreationDate	Date,
	CreatedBy	varchar(20),
	RevisedDate	Date,
	RevisedBy	varchar(20),
	INDEX( UKFuelsCode )
);

#source Stations.sql;


drop table If exists TIDs;

create table TIDs
(
	TerminalID	integer Primary Key,
	LastMessageNo	integer,
	SiteCode	integer
);

drop table if exists SiteChanges;

create table SiteChanges
(
	EffectiveDate	date,
	SiteCode	int,
	Action		enum( 'O', 'C' ),
	Actioned	enum( 'N', 'Y' )
);

drop table if exists AreaRegionManagers;

create table AreaRegionManagers
(
	ManagerID	integer auto_increment primary key, 
	FirstName	varchar(20),
	Surname		varchar(20)
);

