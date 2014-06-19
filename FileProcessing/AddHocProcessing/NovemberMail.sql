create temporary table MemberRedemptions
(
MemberNo bigint primary key,
RedemptionId bigint,
RedemptionDate  datetime,
RedemptionProduct varchar(20)
);

insert into MemberRedemptions (MemberNo, RedemptionId )  select MemberNo, Max(RedeptionId) 
from OrderProducts join Orders using(OrderNo) group by MemberNo;

Update MemberRedemptions join OrderProducts on( RedemptionId = RedeptionId) join Orders using(OrderNo) 
set MemberRedemptions.RedemptionDate = Orders.CreationDate, MemberRedemptions.RedemptionProduct = OrderProducts.ProductId;


      Create temporary table TempMemberData
      ( 
            MemberNo bigint primary key, 
            DaysFirstSwipe int, 
            DaysLastSwipe int, 
            TotalSwipes int, 
            TotalSpend Decimal( 10,2 )
      );

      insert into TempMemberData 
      Select 
      MemberNo, 
      TO_DAYS(now()) - TO_DAYS( Min( FirstSwipeDate)) as DaysFirstSwipe, 
      TO_DAYS(now()) - TO_DAYS( Max( LastSwipeDate) ) as DaysLastSwipe, 
      sum( TotalSwipes ) as TotalSwipes, 
      sum( TotalSpend ) as TotalSpend 
      from Cards where MemberNo is not null and MemberNo != 0 group by MemberNo;

drop table NovemberMail;

create table NovemberMail
select if( DaysLastSwipe < 180, 1, 2 ) as ListCode, PrimaryCard, Email, Salutation, Title, Forename, Surname, 
Address1, Address2, Address3, Address4, Address5, Postcode, DATE_SUB( now(), INTERVAL DaysLastSwipe DAY) as LastSwipeDate, RedemptionDate, RedemptionProduct, 
if( Members.CreatedBy = 'WEB', 'Y', 'N' ) as RegisteredOnline
from Accounts join Members using(AccountNo) join TempMemberData using(MemberNo) 
left join MemberRedemptions using(MemberNo)
where Email is not null and Email != '' and PrimaryMember = 'Y' and PrimaryCard is not null 
and (Members.OKEmail = 'Y' or (Members.OKEmail = 'U' and DaysLastSwipe < 180))
and Members.Deceased = 'N' and Members.GoneAway = 'N' and PrimaryCard not like '4916%' 
and RedemptionStopDate is null;

select min(ListCode), Max(PrimaryCard), Email, max(Salutation), max(Title), max(Forename), max(Surname), max(Address1), max(Address2), max(Address3), max(Address4), max(Address5), max(Postcode), 
max(LastSwipeDate), Max(RedemptionDate), Max(RedemptionProduct), max(RegisteredOnline) 
into outfile '/data/mysql/NovemberEMail5.csv'
from NovemberMail group by Email;
