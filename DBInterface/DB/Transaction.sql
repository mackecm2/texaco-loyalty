
drop table If exists Transactions;
drop table If exists Transactions200401;

create table Transactions200401
(
	TransactionNo	integer not null auto_increment primary key,
	Month		integer default 200401,
	CardNo		char(20) not null,
	AccountNo	BIGINT,
	SiteCode	integer,
	TransTime	DateTime,
	TransValue	Decimal(6,2),
	PanInd		char(1),
	Flag		char(1),
	PayMethod	char(1),
	PointsAwarded	integer,
	InputFile	varchar(25),
	ReceiptNo	char(10),
	EFTTransNo	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( CardNo )
);

drop table If exists Transactions200402;

create table Transactions200402
(
	TransactionNo	integer not null auto_increment primary key,
	Month		integer default 200402,
	CardNo		char(20) not null,
	AccountNo	BIGINT,
	SiteCode	integer,
	TransTime	DateTime,
	TransValue	Decimal(6,2),
	PanInd		char(1),
	Flag		char(1),
	PayMethod	char(1),
	PointsAwarded	integer,
	InputFile	varchar(25),
	ReceiptNo	char(10),
	EFTTransNo	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( CardNo )
);

drop table If exists Transactions200403;

create table Transactions200403
(
	TransactionNo	integer not null auto_increment primary key,
	Month		integer default 200403,
	CardNo		char(20) not null,
	AccountNo	BIGINT,
	SiteCode	integer,
	TransTime	DateTime,
	TransValue	Decimal(6,2),
	PanInd		char(1),
	Flag		char(1),
	PayMethod	char(1),
	PointsAwarded	integer,
	InputFile	varchar(25),
	ReceiptNo	char(10),
	EFTTransNo	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( CardNo )
);

drop table If exists Transactions200404;

create table Transactions200404
(
	TransactionNo	integer not null auto_increment primary key,
	Month		integer default 200404,
	CardNo		char(20) not null,
	AccountNo	BIGINT,
	SiteCode	integer,
	TransTime	DateTime,
	TransValue	Decimal(6,2),
	PanInd		char(1),
	Flag		char(1),
	PayMethod	char(1),
	PointsAwarded	integer,
	InputFile	varchar(25),
	ReceiptNo	char(10),
	EFTTransNo	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( CardNo )
);

drop table If exists Transactions200405;

create table Transactions200405
(
	TransactionNo	integer not null auto_increment primary key,
	Month		integer default 200405,
	CardNo		char(20) not null,
	AccountNo	BIGINT,
	SiteCode	integer,
	TransTime	DateTime,
	TransValue	Decimal(6,2),
	PanInd		char(1),
	Flag		char(1),
	PayMethod	char(1),
	PointsAwarded	integer,
	InputFile	varchar(25),
	ReceiptNo	char(10),
	EFTTransNo	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( CardNo )
);

drop table If exists Transactions200406;

create table Transactions200406
(
	TransactionNo	integer not null auto_increment primary key,
	Month		integer default 200406,
	CardNo		char(20) not null,
	AccountNo	BIGINT,
	SiteCode	integer,
	TransTime	DateTime,
	TransValue	Decimal(6,2),
	PanInd		char(1),
	Flag		char(1),
	PayMethod	char(1),
	PointsAwarded	integer,
	InputFile	varchar(25),
	ReceiptNo	char(10),
	EFTTransNo	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( CardNo )
);


create table Transactions
(
	TransactionNo	integer not null auto_increment,
	Month		integer,
	CardNo		char(20) not null,
	AccountNo	BIGINT,
	SiteCode	integer,
	TransTime	DateTime,
	TransValue	Decimal(6,2),
	PanInd		char(1),
	Flag		char(1),
	PayMethod	char(1),
	PointsAwarded	integer,
	InputFile	varchar(25),
	ReceiptNo	char(10),
	EFTTransNo	integer,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	Unique index(Month, TransactionNo),
	INDEX( CardNo )
)
ENGINE=MERGE UNION=( Transactions200401, Transactions200402, Transactions200403, Transactions200404, Transactions200405, Transactions200406 )
INSERT_METHOD=NO;

drop table If exists ProductsPurchased;
drop table If exists ProductsPurchased200401;

create table ProductsPurchased200401
(
	Month		integer default 200401,
	TransactionNo	BIGINT NOT NULL,
	SequenceNo	TINYINT NOT NULL,
	DepartmentCode  integer,
	ProductCode	integer,
	PointsAwarded	integer,
	Quantity	integer,
	Value		Decimal,

	PRIMARY KEY ( TransactionNo , SequenceNo ) 

);

drop table If exists ProductsPurchased200402;

create table ProductsPurchased200402
(
	Month		integer default 200402,
	TransactionNo	BIGINT NOT NULL,
	SequenceNo	TINYINT NOT NULL,
	DepartmentCode  integer,
	ProductCode	integer,
	PointsAwarded	integer,
	Quantity	integer,
	Value		Decimal,

	PRIMARY KEY ( TransactionNo , SequenceNo ) 

);


create table ProductsPurchased
(
	Month		integer,
	TransactionNo	BIGINT NOT NULL,
	SequenceNo	TINYINT NOT NULL,
	DepartmentCode  integer,
	ProductCode	integer,
	PointsAwarded	integer,
	Quantity	integer,
	Value		Decimal,

	unique index ( Month, TransactionNo , SequenceNo ) 
)
ENGINE=MERGE UNION=( ProductsPurchased200401, ProductsPurchased200402 )
INSERT_METHOD=NO;

drop table If exists BonusHit;
drop table If exists BonusHit200401;

create table BonusHit200401
(
	Month		integer default 200401,
	TransactionNo	BIGINT NOT NULL,
	SequenceNo	TINYINT NOT NULL,
	PromotionCode	varchar(10),
	Points		integer,

	PRIMARY KEY ( TransactionNo , SequenceNo ) 
);

drop table If exists BonusHit200402;

create table BonusHit200402
(
	Month		integer default 200402,
	TransactionNo	BIGINT NOT NULL,
	SequenceNo	TINYINT NOT NULL,
	PromotionCode	varchar(10),
	Points		integer,

	PRIMARY KEY ( TransactionNo , SequenceNo ) 
);


create table BonusHit
(
	Month		integer,
	TransactionNo	BIGINT NOT NULL,
	SequenceNo	TINYINT NOT NULL,
	PromotionCode	varchar(10),
	Points		integer,

	UNIQUE INDEX( Month, TransactionNo , SequenceNo ) 
)
ENGINE=MERGE UNION=(BonusHit200401, BonusHit200402 )
INSERT_METHOD=NO;