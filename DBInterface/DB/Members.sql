drop table If exists Members;

create table Members
(
	MemberNo	BIGINT auto_increment primary key,
	AccountNo	BIGINT,
	PrimaryMember	enum( 'Y', 'N' ) not null,
	PrimaryCard	char(20),
	Title		varchar(10),
	Initials	varchar(5),
	Forename	varchar(40),
	Surname		varchar(40),
	Honours		varchar(10),
	Salutation	varchar(40),
	GenderCode	enum('U','F','M') not null,
	SegmentCode	char(16),
	OldSegmentCode	char(16),
	DOB		year(4),
	HomePhone	varchar(30),
	HomeVerified	date,
	WorkPhone	varchar(30),
	WorkVerified	date,
	Fax		varchar(30),
	Email		varchar(80),
	EmailVerified	date,
	Address1	varchar(40),
	Address2	varchar(40),
	Address3	varchar(40),
	Address4	varchar(40),
	Address5	varchar(40),
	PostCode	char(10),
	AddressVerified date,
	CntryCode	char(2),
	Passwrd		varchar(20),
	PassPrompt	varchar(40),
	StatementPref	enum( 'N', 'P', 'E', 'S' ) not null,
	CanRedeem	enum( 'N', 'Y') not null,
	OKMail		enum( 'N', 'Y') not null,
	TOKMail		enum( 'N', 'Y') not null,
	OKEMail		enum( 'N', 'Y') not null,
	OKSMS		enum( 'N', 'Y') not null,
	OKHomePhone	enum( 'N', 'Y') not null,
	OKWorkPhone	enum( 'N', 'Y') not null,
	GoneAway	enum( 'N', 'Y') not null,
	Deceased	enum( 'N', 'Y') not null,
	MemberData	text,
	LastLogin	date,
	SourceSite	varchar(10),
	PeriodTotal	int,
	PromoCode	varchar(10),
	PromoHitsLeft	int,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	RevisedDate	Timestamp,
	RevisedBy	varchar(20),
	INDEX( AccountNo ),
	INDEX( Surname ),
	INDEX( PostCode ),
	INDEX( Email )
);

#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 1, 7076550200022853363, "Single Card", "Account", "1M1C", "Members.sql" );
#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 2, 7076550200022853364, "Multiple Card", "Account", "1M2C", "Members.sql" );

#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 3, 7076550200022853366, "User1", "Multi-account", "3MXC", "Members.sql" );
#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 3, 7076550200022853368, "User2", "Multi-account", "3MXC", "Members.sql" );
#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 3, 7076550200022853370, "User3", "Multi-account", "3MXC", "Members.sql" );

#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 4, 7076550200026595140, "Simple", "uk fuels account", "UK1", "Members.sql" );
#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 5, 7076550200041033742, "S", "uk fuels account", "UK2", "Members.sql" );
#INSERT into Members( AccountNo, PrimaryCard, Forename, Surname, PostCode, CreatedBy ) values ( 6, 7076550200040966643, "Multi", "uk fuels account", "UK3", "Members.sql" );
