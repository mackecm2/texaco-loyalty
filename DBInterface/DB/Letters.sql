drop table If exists LetterRequests;

create table LetterRequests
(
	RequestNo	integer auto_increment primary key,
	MemberNo	BIGINT,
	TrackingCode	integer,
	Notes		varchar(255),
	Printed		enum( 'N', 'S', 'Y' ) not null,
	PrintStamp	DateTime,
	CreationDate	DateTime,
	CreatedBy	varchar(20),
	INDEX( MemberNo )
);

#drop table If exists LetterCodes;
#
#create table LetterCodes
#(
#	LetterCode	integer auto_increment primary key,
#	Description	varchar(50),
#	Template	varchar(100),
#	Active		enum( 'Y', 'N' ) not null,
#	Priority	int,
#	CreationDate	TimeStamp,
#	CreatedBy	varchar(20)
#);

#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 1, 'Y', "Welcome Letter", "Welcome letter.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 2, 'Y', "Confirm Linkable cards", "AddCardInfo.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 3, 'Y', "Visa Scheme Closed", "VisaSchemeClosed.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 4, 'Y', "Voucher Exchange", "Vchr Exchange.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 5, 'Y', "Replacment Card", "TexRepCard.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 6, 'Y', "Additional Card", "RepSecondCard.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 7, 'Y', "Petrol Reciepts Acknowledge", "AcctsRecpAcknowledgement.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 8, 'Y', "Staff Investigation", "ProsecutionStaff.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 9, 'Y', "General Investigation", "ProsecutionGeneral.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 10, 'Y', "PCS Combined Card use", "PCS1.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 11, 'Y', "No Exchange", "NovExch.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 12, 'Y', "Missing Vouchers", "NonReceiptVchrForm.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 13, 'Y', "New Cards", "NewDupMembers.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 14, 'Y', "Letter Ackknowledge", "Name.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 15, 'Y', "Replacemnt Vouchers", "Lostvchr.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 16, 'Y', "Insurficient Stars Rejection", "insuffstarsrejlet.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 17, 'Y', "Transaction History", "history of awards.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 18, 'Y', "Fast Fuels Card Use", "FastFuels.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 19, 'Y', "Key Fuels Card Use", "Customerservbunkerlet.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 20, 'Y', "Confirm Points Transfer", "ConTrsfofStarsfromRepCardtoNew.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 21, 'Y', "Confirm Voucher Redeem Type", "Confirm Voucher redeemtype.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 22, 'Y', "Confirm Airmiles Redeem Type", "Confirm VF redeemtype.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 23, 'Y', "Confirm Spend", "Confirm Spend.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 24, 'Y', "Virgin Insufficient Funds", "CLUBVIRGININSUFF.dot","Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 25, 'Y', "Virgin Conversion Confirmation", "Clubvf.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 26, 'Y', "Current Balance", "ClubBalBook.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 27, 'Y', "Head Office", "Bstoho.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 28, 'Y', "Redemption Request No Preference", "Bsstarno.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 29, 'Y', "Virgin No Account", "Bsrejvg.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 30, 'Y', "Insurficient Stars Rejection 2", "Bsrejred.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 31, 'Y', "Death certificate", "Bsdeacer.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 32, 'Y', "Proof of Purchase required", "Bscredit.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 33, 'Y', "Change of Address", "Bsconcoa.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 34, 'Y', "Charity Registered", "Bschari2.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 35, 'Y', "Charity Redemption", "Bscharri3.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 36, 'Y', "Petrol Reciepts Credited","BsAcctsRecpts credited.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 37, 'Y', "Bonus Points Credited", "BonusStars.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 38, 'Y', "Current Balance 2", "BalStars.dot", "Letters.sql" );
#INSERT into LetterCodes ( LetterCode, Active, Description, Template, CreatedBy ) values ( 39, 'Y', "Vouchers Not in Stock", "Voucher not in Stock.dot", "Letters.sql" );


