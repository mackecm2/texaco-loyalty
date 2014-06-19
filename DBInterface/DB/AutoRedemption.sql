drop table If exists AutoRedeemOptions;

create table AutoRedeemOptions
(
	OptionId	integer auto_increment primary key,
	ProductId	integer,
	MerchantId	integer,
	Description	varchar(80),
	Active		enum( 'Y', 'N' ) not null,
	Cost		integer,
	AssocAccount	bigint,
	CreationDate	TimeStamp,
	CreatedBy	varchar(20),

	INDEX( ProductId, MerchantId )
);

insert into AutoRedeemOptions ( MerchantId, ProductId, Description, Cost, AssocAccount ) values ( 1, 1, "My Favorite Charity", 2, 123 );
insert into AutoRedeemOptions ( MerchantId, ProductId, Description, Cost, AssocAccount ) values ( 1, 2, "2nd Favorite Charity", 2, 124 );