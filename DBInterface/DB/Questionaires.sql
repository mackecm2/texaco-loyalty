drop table If exists Questions;

Create Table Questions
(
	QuestionId	int auto_increment primary key,
	QuestionText	varchar(255),
	Type		enum( 'I', 'S', 'B', 'T' ),
	Active		enum( 'Y', 'N' ) not null,
	VerifyPeriod	int,
	Priority	int
);

drop table If exists QuestionOptions;

create table QuestionOptions
(
	QuestionId	int,
	OptionValue	varchar(20),
	OptionText	varchar(50),
	Active		enum( 'Y', 'N' ) not null,
	Priority	int,
	primary key( QuestionId, OptionValue )
);

drop table If exists Answers;

create Table Answers
(
	MemberNo	BIGINT,
	QuestionId	int,
	Answer		varchar(20),
	CreationDate	DateTime,
	CreatedBy	varchar(20),

	primary key( MemberNo, QuestionId )  
);

insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 1, "Mileage", 1, 1, 'I' );
insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 2, "Fills out of 10", 1, 1, 'I' );
insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 3, "Company Paid Fuel", 1, 1, 'B' );
insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 4, "Visa Interest", 1, 1, 'B' );
insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 5, "Fleet Buyer", 1, 1, 'B' );
insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 6, "Cars in household", 1, 1, 'I' );
insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 7, "Income", 1, 1, 'S' );
insert into Questions( QuestionId, QuestionText, VerifyPeriod, Priority, Type ) values ( 8, "No of kids", 1, 1, 'I' );

insert into QuestionOptions values ( 7, "5000", "< £5,000", 'Y', 1 );
insert into QuestionOptions values ( 7, "9999", "£5,000 - £9,999", 'Y', 2 );
insert into QuestionOptions values ( 7, "14999", "£10,000 - £14,999", 'Y', 3 );
insert into QuestionOptions values ( 7, "19999", "£15,000 - £19,999", 'Y', 4 );
insert into QuestionOptions values ( 7, "24999", "£20,000 - £24,999", 'Y', 5 );
insert into QuestionOptions values ( 7, "29999", "£25,000 - £29,999", 'Y', 6 );
insert into QuestionOptions values ( 7, "39999", "£30,000 - £39,999", 'Y', 7 );
insert into QuestionOptions values ( 7, "49999", "£40,000 - £49,999", 'Y', 8 );
insert into QuestionOptions values ( 7, "59999", "£50,000 - £59,999", 'Y', 9 );
insert into QuestionOptions values ( 7, "60000", "£60,000 +", 'Y', 10 );


