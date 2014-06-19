mysql> select count(*), CreatedBy from DuplicateEmails join Members using( Email ) group by CreatedBy;
+----------+-------------------+
| count(*) | CreatedBy         |
+----------+-------------------+
|        1 | NULL              |
|      120 | AdamW             |
|        3 | AndrewC           |
|        2 | annab             |
|        3 | BeckyP            |
|        1 | BOSUpdate         |
|        2 | CathyG            |
|       19 | CharlotteR        |
|       19 | charlottew        |
|       91 | cherylr           |select
|       88 | ChrisL            |
|       94 | ClaireN           |
|     3821 | dbo_tex           |
|        5 | DebbieS           |
|        6 | DShing            |
|     4033 | Feb2004_Recarding |
|      120 | HelenA            |
|      182 | Helent            |
|        1 | James Beacon      |
|       36 | JamesB            |
|      157 | janeb             |
|      248 | Jas-ReCarding     |
|      141 | jennyr            |
|       11 | JohnA             |
|        3 | JulieO            |
|        2 | Katy Freeman      |
|      151 | KatyF             |
|      213 | KellyC            |
|       66 | LizD              |
|       30 | marshag           |
|       30 | michellec         |
|       40 | MSBUILD           |
|        2 | MTV               |
|        1 | NabilJ            |
|     2697 | NewApps           |
|      109 | Paulah            |
|       47 | Pennyw            |
|        3 | pholdc            |
|      126 | RE-RE-CARDING     |
|        2 | rebeccag          |
|       16 | RodW              |
|      115 | roman             |
|        2 | RosieC            |
|        1 | RuthD             |
|        2 | samuelA           |
|        2 | SharonC           |
|      127 | Sued              |
|      199 | suem              |
|        3 | Tara Ingham       |
|      115 | TaraI             |
|        4 | temp              |
|      146 | Temp1             |
|       24 | temp2             |
|       11 | tempjh            |
|        8 | Templ             |
|      696 | texdba            |
|      489 | WEB               |
|       16 | ZannahB           |
+----------+-------------------+
58 rows in set (1.13 sec)


CREATE TABLE `DeletedMembers` (
  `DeletedMemberNo` bigint(20) primary key,
  NewMemberNo bigint(20),
  `AccountNo` bigint(20) default NULL,
  `PrimaryMember` enum('Y','N') NOT NULL default 'Y',
  `PrimaryCard` varchar(20) default NULL,
  `Title` varchar(10) default NULL,
  `Initials` varchar(5) default NULL,
  `Forename` varchar(40) default NULL,
  `Surname` varchar(40) default NULL,
  `Honours` varchar(10) default NULL,
  `Salutation` varchar(40) default NULL,
  `GenderCode` enum('U','F','M') NOT NULL default 'U',
  `SegmentCode` varchar(16) default NULL,
  `OldSegmentCode` varchar(16) default NULL,
  `DOB` year(4) default NULL,
  `HomePhone` varchar(30) default NULL,
  `HomeVerified` date default NULL,
  `WorkPhone` varchar(30) default NULL,
  `WorkVerified` date default NULL,
  `Fax` varchar(30) default NULL,
  `Email` varchar(80) default NULL,
  `EmailVerified` date default NULL,
  `Address1` varchar(40) default NULL,
  `Address2` varchar(40) default NULL,
  `Address3` varchar(40) default NULL,
  `Address4` varchar(40) default NULL,
  `Address5` varchar(40) default NULL,
  `PostCode` varchar(10) default NULL,
  `AddressVerified` date default NULL,
  `CntryCode` char(2) default NULL,
  `Passwrd` varchar(20) default NULL,
  `PassPrompt` varchar(40) default NULL,
  `StatementPref` enum('N','P','E','S') NOT NULL default 'N',
  `CanRedeem` enum('N','Y') NOT NULL default 'N',
  `OKMail` enum('N','Y') NOT NULL default 'N',
  `TOKMail` enum('N','Y') NOT NULL default 'N',
  `OKEmail` enum('N','Y','U') default NULL,
  `OKSMS` enum('N','Y') NOT NULL default 'N',
  `OKHomePhone` enum('N','Y') NOT NULL default 'N',
  `OKWorkPhone` enum('N','Y') NOT NULL default 'N',
  `GoneAway` enum('N','Y') NOT NULL default 'N',
  `Deceased` enum('N','Y') NOT NULL default 'N',
  `MemberData` text,
  `LastLogin` date default NULL,
  `SourceSite` varchar(10) default NULL,
  `CreationDate` datetime default NULL,
  `CreatedBy` varchar(20) default NULL,
  `RevisedDate` timestamp(14) NOT NULL,
  `RevisedBy` varchar(20) default NULL,
  KEY `AccountNo` (`AccountNo`),
  KEY `Surname` (`Surname`),
  KEY `PostCode` (`PostCode`),
  KEY `Email` (`Email`)
) ;

AccountNo,PrimaryMember,PrimaryCard,Title,Initials,Forename,Surname,Honours,Salutation,GenderCode,
SegmentCode,OldSegmentCode,DOB,HomePhone,HomeVerified,WorkPhone,WorkVerified,Fax,Email,EmailVerified,
Address1,Address2,Address3,Address4,Address5,PostCode,AddressVerified,CntryCode,Passwrd,PassPrompt,
StatementPref,CanRedeem,OKMail,TOKMail,OKEmail,OKSMS,OKHomePhone,OKWorkPhone,GoneAway,Deceased,
MemberData,LastLogin,SourceSite,CreationDate,CreatedBy,RevisedDate,RevisedBy

insert into DeletedMembers
select M2.MemberNo, M1.MemberNo,  

M2.AccountNo,M2.PrimaryMember,M2.PrimaryCard,M2.Title,M2.Initials,M2.Forename,M2.Surname,M2.Honours,M2.Salutation,M2.GenderCode,M2.
SegmentCode,M2.OldSegmentCode,M2.DOB,M2.HomePhone,M2.HomeVerified,M2.WorkPhone,M2.WorkVerified,M2.Fax,M2.Email,M2.EmailVerified,M2.
Address1,M2.Address2,M2.Address3,M2.Address4,M2.Address5,M2.PostCode,M2.AddressVerified,M2.CntryCode,M2.Passwrd,M2.PassPrompt,M2.
StatementPref,M2.CanRedeem,M2.OKMail,M2.TOKMail,M2.OKEmail,M2.OKSMS,M2.OKHomePhone,M2.OKWorkPhone,M2.GoneAway,M2.Deceased,M2.
MemberData,M2.LastLogin,M2.SourceSite,M2.CreationDate,M2.CreatedBy,M2.RevisedDate,M2.RevisedBy

from DuplicateEmails join Members as M1 on(DuplicateEmails.Email = M1.Email) 
Join Members as M2 on(DuplicateEmails.Email = M2.Email) 
where M1.MemberNo != M2.MemberNo and M1.AccountNo = M2.AccountNo
and M1.PrimaryMember = 'Y' and M1.Surname = M2.Surname and 
M1.Title = M2.Title and
(substring(M1.Forename, 0, 1) = substring( M2.Forename, 0, 1 ) or
 substring(M1.Initials, 0, 1) = substring( M2.Forename, 0, 1 ) or
 substring(M2.Initials, 0, 1) = substring( M1.Forename, 0, 1 ) or
 substring(M1.Initials, 0, 1) = substring( M2.Initials, 0, 1 ) )
and M1.Postcode = M2.Postcode;

select count(*) from Cards Join DeletedMembers on( Cards.MemberNo = DeletedMembers.DeletedMemberNo );
select Count(*) from CardRequests join DeletedMembers on( CardRequests.MemberNo = DeletedMembers.DeletedMemberNo ); 
select Count(*) from MonthlyMember join DeletedMembers on( MonthlyMember.MemberNo = DeletedMembers.DeletedMemberNo ); 
select Count(*) from Tracking join DeletedMembers on( Tracking.MemberNo = DeletedMembers.DeletedMemberNo ); 
select Count(*) from Answers join DeletedMembers on( Answers.MemberNo = DeletedMembers.DeletedMemberNo ); 

update Cards Join DeletedMembers on( Cards.MemberNo = DeletedMembers.DeletedMemberNo ) 
set Cards.MemberNo = DeletedMembers.NewMemberNo;

update CardRequests Join DeletedMembers on( CardRequests.MemberNo = DeletedMembers.DeletedMemberNo ) 
set CardRequests.MemberNo = DeletedMembers.NewMemberNo;

update MonthlyMember join DeletedMembers on( MonthlyMember.MemberNo = DeletedMembers.DeletedMemberNo ) 
set MonthlyMember.MemberNo = DeletedMembers.NewMemberNo;

update Tracking join DeletedMembers on( Tracking.MemberNo = DeletedMembers.DeletedMemberNo ) 
set Tracking.MemberNo = DeletedMembers.NewMemberNo;

delete Members from Members join DeletedMembers on( Tracking.MemberNo = DeletedMembers.DeletedMemberNo );