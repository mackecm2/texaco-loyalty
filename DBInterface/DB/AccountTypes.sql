drop table If exists AccountTypes;

create table AccountTypes
(
	AccountType  char(1),
	Description  varchar( 50 ),
	Active	     enum( 'Y', 'N' ) not null
);

INSERT into AccountTypes values( null, "Unknown", 'Y' );
INSERT into AccountTypes values( 'A', "Account Card", 'Y' );
INSERT into AccountTypes values( 'B', "Business", 'Y' );
INSERT into AccountTypes values( 'C', "Charity", 'Y' );
INSERT into AccountTypes values( 'F', "Fraud", 'Y' );
INSERT into AccountTypes values( 'H', "Haulage/Coach", 'Y' );
INSERT into AccountTypes values( 'L', "Local Company Account", 'Y' );
INSERT into AccountTypes values( 'P', "Private", 'Y' );
INSERT into AccountTypes values( 'T', "Taxi/Local Delivery", 'Y' );
INSERT into AccountTypes values( 'S', "Seed", 'Y' );
INSERT into AccountTypes values( 'O', "Other", 'Y' );
