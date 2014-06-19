drop table If exists FilesProcessed;

create table FilesProcessed
(
	ID		integer auto_increment primary key,
	FileName	varchar(20),
	StartTime	DateTime,
	EndTime		DateTime,
	ErrorCount	integer default 0,
	CreatedBy	varchar(20)
);