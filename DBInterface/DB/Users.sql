drop table If exists Users;

create table Users
(
	UserID		int not null auto_increment PRIMARY KEY,
	UserName	varchar(20) not null,
	Active		enum( 'Y', 'N' ) not null,
	PassWrd		varchar(10),
	Grp		varchar(10),
	GrpPass		varchar(10),
	Permissions	varchar(20),
	PasswordExpire	datetime,
	LastLogin	datetime,
	CreatedBy	varchar(20),
	Created		datetime,
	INDEX( UserName )
);

create table UserPasswordHistory
(
	UserName	varchar(20) not null,
	PassWrd		varchar(10) not null,
	INDEX( UserName )
);
# DP Daily process
# UM User Manager
# BM Bonus Manager
# MS Member Screen
# CP Config Pages
# QU Questions
# MH Member History
# AP Add Points
# MM Merge Member
# TM Tracking 
# RC Request card

#	define( "PermissionsBigAdjust", "A" );
#	define( "PermissionsBonusManager", "B" );
#	define( "PermissionsConfigPages", "C" );
#	define( "PermissionsDailyProcess", "D" );
#	define( "PermissionsMemberHistory", "H" );
#	define( "PermissionsMergeMembers", "M" );
#	define( "PermissionsQuestionUser", "Q" );
#	define( "PermissionsShopping", "P" );
#	define( "PermissionsRequestCard", "R" );
#	define( "PermissionsSmallAdjust", "S" );
#	define( "PermissionsTracking",  "T" );
#	define( "PermissionsUserManager", "U" );
#	define( "PermissionsMemberScreen", "Z" );


Insert into Users values ( NULL, "DawleysAdmin", 'Y', "Secure", "DAdmin", "Global", "ACDHMQPRrTUZ", now(), null, "Users.sql", now() );
Insert into Users values ( NULL, "MarketAdmin", 'Y', "Secure", "MAdmin", "Global", "BHrUZ", now(), null, "Users.sql", now() );
Insert into Users values ( NULL, "UKAdmin", 'Y', "Secure", "UAdmin", "Global", "MRUZ", now(), null, "User.sql", now());

drop table if exists CreateUserTypes;

create table CreateUserTypes
(
	NewUserGrp	varchar(10),
	NewUserPass	varchar(10),
	NewUserDesc	varchar(40),
	NewUserPerms	varchar(20),
	CreatorGrp	varchar(10)
);

insert into CreateUserTypes values( "DBasic", "Global", "Dawleys User", "HMQPRSTZ", "DAdmin" );
insert into CreateUserTypes values( "DAdmin", "Global", "Dawleys Administrator", "ACDHMQPRrTUZ", "DAdmin" );
insert into CreateUserTypes values( "MBasic", "Global", "Marketing User", "BHrZ", "MAdmin" );
insert into CreateUserTypes values( "MAdmin", "Global", "Marketing Administrator", "BHrUZ", "MAdmin" );
insert into CreateUserTypes values( "UBasic", "Global", "UK Fuels User", "MRZ", "UAdmin" );
insert into CreateUserTypes values( "UAdmin", "Global", "UK Fuels Administrator", "MRUZ", "UAdmin" );