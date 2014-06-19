drop table If exists ReportsToRun;

create table ReportsToRun
(
	ID		int auto_increment primary key, 
	Status		enum( 'P', 'R', 'F', 'S', 'D' ) not null,
	Description	varchar(50),	
	Started		datetime,
	Finished	datetime,
	Created		DateTime,
	CreatedBy	char(20),
	SQLSTR		TEXT,
	ColumnHeads	varchar(255),
	ErrorStr	varchar(255),
	ResultsFile	varchar(50),
	INDEX( Status )
);

# P - pending
# R - Running
# F - Failed
# S - Success
# D - Deleted