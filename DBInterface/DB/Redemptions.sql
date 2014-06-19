drop table If exists Orders;

create table Orders
(
	OrderNo		bigint auto_increment primary key,
	MemberNo	bigint,
	AccountNo	bigint,
	Name		varchar(80),
	Address1	varchar(40),
	Address2	varchar(40),
	Address3	varchar(40),
	Address4	varchar(40),
	Address5	varchar(40),
	PostCode	char(10),
	CreationDate	DateTime,
	CreatedBy	char(20),
	INDEX( MemberNo ),
	INDEX( AccountNo )
);


drop table If exists OrderProducts;

create table OrderProducts
(
	RedeptionId	bigint auto_increment primary key,
	OrderNo		bigint,
	ProductId	varchar(20),
	MerchantId	integer,
	MerchantRef	varchar(20),
	MerchantTxno	varchar(20),
	ProductOption	varchar(40),
	Description	varchar(40),
	Status		enum( 'P', 'T', 'O', 'R', 'F' ) not null,
	Cost		integer,
	Quantity	integer default 1,
	BatchTime	DateTime,
	INDEX( OrderNo )
);

# P Pending
# T Tempary
# O Output
# F Fulfilled
# R Rejected

drop table If exists RedemptionMerchants;

create table RedemptionMerchants
(
	MerchantId	integer primary key,
	MerchantName	varchar(80),
	CreationDate	DateTime,
	CreatedBy	varchar(20)
);

drop table If exists MerchantOptions;

create table MerchantOptions
(
	MerchantId	integer,
	ProductId	varchar(20),
	Description	varchar(255),
	Active		enum( 'Y', 'N' ) not null,

	unique INDEX( MerchantId, ProductId )
);

insert into RedemptionMerchants ( MerchantId, MerchantName ) values ( 1, "WEOU Website" );
insert into RedemptionMerchants ( MerchantId, MerchantName ) values ( 2, "Auto Redemption" );
insert into RedemptionMerchants ( MerchantId, MerchantName ) values ( 3, "Historic Redemption" );

