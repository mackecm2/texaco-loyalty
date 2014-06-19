drop table If exists ErrorLog;

create table ErrorLog
(
	ErrorString	varchar(255),
	CreationDate	TimeStamp,
	CreatedBy	varchar(20)
);
