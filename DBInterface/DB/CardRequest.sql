drop table if exists CardRequests;

create table CardRequests
(
	RequestNo	integer auto_increment primary key,
	RequestCode	char(2),
	MemberNo	bigint,
	Status		enum( 'N', 'O', 'S' ) not null,
	BatchTime	DateTime,
	CreationDate	DateTime,
	CreatedBy	varchar(20)
);

# status 
# N - new
# O - priviously output not yet returned
# S - Satisfied
