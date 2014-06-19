drop table If exists MonthlySpends;

create table MonthlySpends
(
	SpendId	     int unique,
	Description  varchar( 50 ),
	Active	     enum( 'Y', 'N' ) not null
);

INSERT into MonthlySpends values( null, "Unknown", 'Y' );
INSERT into MonthlySpends values( 1, "0-£100", 'Y' );
INSERT into MonthlySpends values( 100, "£100-£200", 'Y' );
INSERT into MonthlySpends values( 200, "£200-£500", 'Y' );
INSERT into MonthlySpends values( 500, "£500-£1,000", 'Y' );
INSERT into MonthlySpends values( 1000, "£1,000-£1,500", 'Y' );
INSERT into MonthlySpends values( 1500, "£1,500-£2,000", 'Y' );
INSERT into MonthlySpends values( 2000, "£2,000 plus", 'Y' );
