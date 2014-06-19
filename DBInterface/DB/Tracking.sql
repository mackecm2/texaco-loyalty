drop table If exists Tracking;

create table Tracking
(
#	TrackingNo	integer auto_increment primary key,
	MemberNo	bigint,
	AccountNo	BIGINT,
	TrackingCode	int,
	Notes		varchar(255),
	Stars		int,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( MemberNo ),
	INDEX( AccountNo )
);

drop table If exists TrackingCodes;

create table TrackingCodes
(
	TrackingCode	int auto_increment primary key,
	Description	varchar(255),
	CreditDebit	enum( 'N', 'Y' ) not null,
	AddTracking	enum( 'N', 'Y' ) not null,
	StopTracking    enum( 'N', 'Y' ) not null,
	Letters		enum( 'N', 'Y' ) not null,
	Template	varchar(100),
	Active		enum( 'Y', 'N' ) not null,
	Priority	int,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	RevisedDate	DateTime,
	RevisedBy	varchar(20)
);

drop table If exists MergeHistory;

create table MergeHistory
(
	SourceAccount	BIGINT,
	DestinationAccount BIGINT,
	MemberNo	BIGINT,

	INDEX( MemberNo, DestinationAccount )
);

#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit, AddTracking, StopTracking, CreatedBy ) values ( 1,  "Miscellaneous", 'Y',  'Y',  'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 2,  "Incoming Letter", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 3,  "Outgoing Leter", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 4,  "Incoming Email", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 5,  "Outgoing Email", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 6,  "Link Card", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 7,  "Additional Card Requested", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 8,  "Additional Member Created", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 9,  "Replacement Card Requested", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 10,  "New Member Created", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 11,  "Modified Contact Details", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 12,  "Modified Member Preference", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 13,  "Modified Account Type", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 14,  "Merge Members", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 15,  "Merge Card", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 16,  "Multi card request", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 17,  "Redemption", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 18,  "Award Stop", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 19,  "Redemption Stop", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit, CreatedBy ) values ( 20,  "Credit", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit, CreatedBy ) values ( 21,  "Debit", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 23,  "Home Site Changed", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 24,  "Statement Type Changed", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 25,  "Unmerge member", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 26,  "Member unmerged", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 27,  "Account Card Added", "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreatedBy ) values ( 28,  "Card Added", "Tracking.sql" );

#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit, CreatedBy ) values ( 31,  "MTV Credit", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit,  CreatedBy ) values ( 32,  "Merging Error", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit, CreatedBy ) values ( 33,  "Charity Donation", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit, CreatedBy ) values ( 34,  "Company Account Credit", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, CreditDebit, CreatedBy ) values ( 35,  "Receipt Credit", 'Y', "Tracking.sql");
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 36,  "Account Cleared", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 37,  "Angry Customer", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 38,  "Fast Fuel Customer", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 39,  "Fraud", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 40,  "Miscellaneous", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 41,  "Non-Receipt of Voucher", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 42,  "Old Virgin Member", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 43,  "Staff Card Fraud", 'Y', "Tracking.sql" );
#INSERT into TrackingCodes ( TrackingCode,  Description, AddTracking, CreatedBy ) values ( 44,  "Travel Query (MKM)", 'Y', "Tracking.sql" );


#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 51, 'Y', "Welcome Letter", "Welcome letter.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 52, 'Y', "Confirm Linkable cards", "AddCardInfo.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 53, 'Y', "Visa Scheme Closed", "VisaSchemeClosed.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 54, 'Y', "Voucher Exchange", "Vchr Exchange.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 55, 'Y', "Replacment Card", "TexRepCard.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 56, 'Y', "Additional Card", "RepSecondCard.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 57, 'Y', "Petrol Reciepts Acknowledge", "AcctsRecpAcknowledgement.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 58, 'Y', "Staff Investigation", "ProsecutionStaff.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 59, 'Y', "General Investigation", "ProsecutionGeneral.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 60, 'Y', "PCS Combined Card use", "PCS1.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 61, 'Y', "No Exchange", "NovExch.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 62, 'Y', "Missing Vouchers", "NonReceiptVchrForm.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 63, 'Y', "New Cards", "NewDupMembers.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 64, 'Y', "Letter Ackknowledge", "Name.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 65, 'Y', "Replacemnt Vouchers", "Lostvchr.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 66, 'Y', "Insurficient Stars Rejection", "insuffstarsrejlet.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 67, 'Y', "Transaction History", "history of awards.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 68, 'Y', "Fast Fuels Card Use", "FastFuels.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 69, 'Y', "Key Fuels Card Use", "Customerservbunkerlet.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 70, 'Y', "Confirm Points Transfer", "ConTrsfofStarsfromRepCardtoNew.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 71, 'Y', "Confirm Voucher Redeem Type", "Confirm Voucher redeemtype.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 72, 'Y', "Confirm Airmiles Redeem Type", "Confirm VF redeemtype.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 73, 'Y', "Confirm Spend", "Confirm Spend.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 74, 'Y', "Virgin Insufficient Funds", "CLUBVIRGININSUFF.dot","Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 75, 'Y', "Virgin Conversion Confirmation", "Clubvf.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 76, 'Y', "Current Balance", "ClubBalBook.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 77, 'Y', "Head Office", "Bstoho.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 78, 'Y', "Redemption Request No Preference", "Bsstarno.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 79, 'Y', "Virgin No Account", "Bsrejvg.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 80, 'Y', "Insurficient Stars Rejection 2", "Bsrejred.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 81, 'Y', "Death certificate", "Bsdeacer.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 82, 'Y', "Proof of Purchase required", "Bscredit.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 83, 'Y', "Change of Address", "Bsconcoa.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 84, 'Y', "Charity Registered", "Bschari2.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 85, 'Y', "Charity Redemption", "Bscharri3.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 86, 'Y', "Petrol Reciepts Credited","BsAcctsRecpts credited.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 87, 'Y', "Bonus Points Credited", "BonusStars.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 88, 'Y', "Current Balance 2", "BalStars.dot", "Letters.sql" );
#INSERT into TrackingCodes ( TrackingCode, Letters, Description, Template, CreatedBy ) values ( 89, 'Y', "Vouchers Not in Stock", "Voucher not in Stock.dot", "Letters.sql" );
