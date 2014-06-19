#REVOKE ALL PRIVILEGES, GRANT from 'UserCheck'@'localhost';
#REVOKE ALL PRIVILEGES, GRANT from 'DBasic'@'localhost';
#REVOKE ALL PRIVILEGES, GRANT from 'DAdmin'@'localhost';
#REVOKE ALL PRIVILEGES, GRANT from 'MBasic'@'localhost';
#REVOKE ALL PRIVILEGES, GRANT from 'MAdmin'@'localhost';

REVOKE ALL PRIVILEGES on *.* from 'UserCheck'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'DBasic'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'DAdmin'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'MBasic'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'MAdmin'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'UBasic'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'UAdmin'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'Letters'@'localhost';
REVOKE ALL PRIVILEGES on *.* from 'HomeExport'@'localhost';

REVOKE GRANT OPTION on *.* from 'UserCheck'@'localhost';
REVOKE GRANT OPTION on *.* from 'DBasic'@'localhost';
REVOKE GRANT OPTION on *.* from 'DAdmin'@'localhost';
REVOKE GRANT OPTION on *.* from 'MBasic'@'localhost';
REVOKE GRANT OPTION on *.* from 'MAdmin'@'localhost';
REVOKE GRANT OPTION on *.* from 'UBasic'@'localhost';
REVOKE GRANT OPTION on *.* from 'UAdmin'@'localhost';
REVOKE GRANT OPTION on *.* from 'Letters'@'localhost';
REVOKE GRANT OPTION on *.* from 'WEOU'@'localhost';
REVOKE GRANT OPTION on *.* from 'HomeExport'@'localhost';

GRANT USAGE ON *.* TO 'UserCheck'@'localhost';
GRANT USAGE ON *.* TO 'DBasic'@'localhost';
GRANT USAGE ON *.* TO 'DAdmin'@'localhost';
GRANT USAGE ON *.* TO 'MBasic'@'localhost';
GRANT USAGE ON *.* TO 'MAdmin'@'localhost';
GRANT USAGE ON *.* TO 'UBasic'@'localhost';
GRANT USAGE ON *.* TO 'UAdmin'@'localhost';
GRANT USAGE ON *.* TO 'Letters'@'localhost';
GRANT USAGE ON *.* TO 'UKFulesProcess'@'localhost';
GRANT USAGE ON *.* TO 'CompowerProcess'@'localhost';
GRANT USAGE ON *.* TO 'WEOU'@'localhost';
GRANT USAGE ON *.* TO 'HomeExport'@'localhost';

GRANT SELECT, UPDATE ON `texaco`.`Accounts` TO 'CompowerProcess'@'localhost';
GRANT SELECT, UPDATE ON `texaco`.`Members` TO 'CompowerProcess'@'localhost';
GRANT SELECT, UPDATE ON `texaco`.`Cards` TO 'CompowerProcess'@'localhost';
GRANT SELECT ON `texaco`.`Sites` TO 'CompowerProcess'@'localhost';
GRANT INSERT ON `texaco`.`ErrorLog` TO 'CompowerProcess'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`FilesProcessed` TO 'CompowerProcess'@'localhost';


GRANT SELECT, UPDATE ON `texaco`.`Accounts` TO 'UKFuelsProcess'@'localhost';
GRANT SELECT, UPDATE ON `texaco`.`Members` TO 'UKFuelsProcess'@'localhost';
GRANT SELECT, UPDATE ON `texaco`.`Cards` TO 'UKFuelsProcess'@'localhost';
GRANT SELECT ON `texaco`.`Sites` TO 'UKFuelsProcess'@'localhost';
GRANT INSERT ON `texaco`.`ErrorLog` TO 'UKFuelsProcess'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`FilesProcessed` TO 'UKFuelsProcess'@'localhost';

# HomeExport

GRANT FILE ON *.* TO 'HomeExport'@'localhost';
GRANT SELECT ON `texaco`.`Accounts` TO 'HomeExport'@'localhost';
GRANT SELECT ON `texaco`.`Members` TO 'HomeExport'@'localhost';
GRANT INSERT ON `texaco`.`ErrorLog` TO 'HomeExport'@'localhost';

# User Check account
GRANT USAGE ON `texaco`.* TO 'UserCheck'@'localhost';
#GRANT SELECT, UPDATE (`LastLogin`) ON `texaco`.`Users` TO 'UserCheck'@'localhost';

GRANT SELECT, UPDATE ON `texaco`.`Users` TO 'UserCheck'@'localhost';

# WEOU Account

GRANT SELECT, UPDATE, INSERT ON `texaco`.`Accounts` TO 'WEOU'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Members` TO 'WEOU'@'localhost';
GRANT SELECT ON `texaco`.`Transactions` TO 'WEOU'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Cards` TO 'WEOU'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Orders` TO 'WEOU'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`OrderProducts` TO 'WEOU'@'localhost';
GRANT SELECT ON `texaco`.`RedemptionMerchants` TO 'WEOU'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`authcodes` TO 'WEOU'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Msgref` TO 'WEOU'@'localhost';


# Dawleys Basic account
#GRANT SELECT, UPDATE (`PassWrd` ,`GrpPass`,`PasswordExpire`) ON `texaco`.`Users` TO 'DBasic'@'localhost';
GRANT SELECT, UPDATE ON `texaco`.`Users` TO 'DBasic'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Accounts` TO 'DBasic'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Members` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`Transactions` TO 'DBasic'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Cards` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT, DELETE ON `texaco`.`UserActions` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`AccountTypes` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`TrackingCodes` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Tracking` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`LetterRequests` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`CardRequests` TO 'DBasic'@'localhost';
#GRANT SELECT ON `texaco`.`LetterCodes` TO 'DBasic'@'localhost';
#GRANT SELECT ON `texaco`.`AutoRedeemOptions` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`RedemptionMerchants` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`Questions` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`QuestionOptions` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`Answers` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Orders` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`OrderProducts` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`Monthly` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`MonthlyMember` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`Statement` TO 'DBasic'@'localhost';
GRANT SELECT ON `texaco`.`MonthlySpends` TO 'DBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`MergeHistory` TO 'DBasic'@'localhost';

# Dawleys Administrator account
GRANT USAGE ON `texaco`.* TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Users` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Accounts` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Members` TO 'DAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Transactions` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Cards` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, DELETE ON `texaco`.`UserActions` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`AccountTypes` TO 'DAdmin'@'localhost';
GRANT SELECT ON `texaco`.`CreateUserTypes` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`TrackingCodes` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Tracking` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`LetterRequests` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`CardRequests` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`ReportsToRun` TO 'DAdmin'@'localhost';
#GRANT SELECT, INSERT, UPDATE ON `texaco`.`LetterCodes` TO 'DAdmin'@'localhost';
#GRANT SELECT, INSERT, UPDATE ON `texaco`.`AutoRedeemOptions` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`RedemptionMerchants` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Questions` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`QuestionOptions` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`Answers` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`Orders` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`OrderProducts` TO 'DAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Monthly` TO 'DAdmin'@'localhost';
GRANT SELECT ON `texaco`.`MonthlyMember` TO 'DAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Statement` TO 'DAdmin'@'localhost';
GRANT SELECT ON `texaco`.`MonthlySpends` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`MergeHistory` TO 'DAdmin'@'localhost';
GRANT SELECT, UPDATE on `texaco`.`Sites` TO 'DAdmin'@'localhost';
GRANT INSERT ON `texaco`.`ErrorLog` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`FilesProcessed` TO 'DAdmin'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`PersonalCampaigns` TO 'DAdmin'@'localhost';



# Marketing Administrator account
GRANT USAGE ON `texaco`.* TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE  ON `texaco`.`Users` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`CreateUserTypes` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`BonusPoints` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`BonusCriteria` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`FieldComparisions` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Sites` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT, DELETE ON `texaco`.`UserActions` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Members` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Accounts` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Transactions` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Cards` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`AccountTypes` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`TrackingCodes` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Tracking` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`LetterRequests` TO 'MAdmin'@'localhost';
GRANT SELECT, UPDATE, INSERT ON `texaco`.`ReportsToRun` TO 'MAdmin'@'localhost';
#GRANT SELECT ON `texaco`.`LetterCodes` TO 'MAdmin'@'localhost';
#GRANT SELECT ON `texaco`.`AutoRedeemOptions` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`RedemptionMerchants` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Questions` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`QuestionOptions` TO 'MAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`Answers` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Orders` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`OrderProducts` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Monthly` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`MonthlyMember` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`Statement` TO 'MAdmin'@'localhost';
GRANT SELECT ON `texaco`.`MonthlySpends` TO 'MAdmin'@'localhost';


# Marketing Basic account
GRANT USAGE ON `texaco`.* TO 'MBasic'@'localhost';
GRANT SELECT, UPDATE(`PassWrd` ,`GrpPass`,`PasswordExpire`) ON `texaco`.`Users` TO 'MBasic'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`BonusPoints` TO 'MBasic'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`BonusCriteria` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`FieldComparisions` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Sites` TO 'MBasic'@'localhost';
GRANT SELECT, INSERT, DELETE ON `texaco`.`UserActions` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Members` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Accounts` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Transactions` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Cards` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`AccountTypes` TO 'MBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`LetterRequests` TO 'MBasic'@'localhost';
#GRANT SELECT ON `texaco`.`LetterCodes` TO 'MBasic'@'localhost';
#GRANT SELECT ON `texaco`.`AutoRedeemOptions` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`RedemptionMerchants` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Questions` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`QuestionOptions` TO 'MBasic'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `texaco`.`Answers` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Orders` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`OrderProducts` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Monthly` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`MonthlyMember` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`Statement` TO 'MBasic'@'localhost';
GRANT SELECT ON `texaco`.`MonthlySpends` TO 'MBasic'@'localhost';


# UKFules Accounts

GRANT USAGE ON `texaco`.* TO 'UBasic'@'localhost';
GRANT SELECT, UPDATE(`PassWrd` ,`GrpPass`,`PasswordExpire`) ON `texaco`.`Users` TO 'UBasic'@'localhost';
GRANT SELECT, INSERT, DELETE ON `texaco`.`UserActions` TO 'UBasic'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Members` TO 'UBasic'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Accounts` TO 'UBasic'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Cards` TO 'UBasic'@'localhost';
GRANT SELECT ON `texaco`.`AccountTypes` TO 'UBasic'@'localhost';
GRANT SELECT ON `texaco`.`TrackingCodes` TO 'UBasic'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Tracking` TO 'UBasic'@'localhost';
GRANT SELECT ON `texaco`.`MonthlySpends` TO 'UBasic'@'localhost';

GRANT USAGE ON `texaco`.* TO 'UAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Users` TO 'UAdmin'@'localhost';
GRANT SELECT ON `texaco`.`CreateUserTypes` TO 'UAdmin'@'localhost';
GRANT SELECT, INSERT, DELETE ON `texaco`.`UserActions` TO 'UAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Members` TO 'UAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Accounts` TO 'UAdmin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `texaco`.`Cards` TO 'UAdmin'@'localhost';
GRANT SELECT ON `texaco`.`AccountTypes` TO 'UAdmin'@'localhost';
GRANT SELECT ON `texaco`.`TrackingCodes` TO 'UAdmin'@'localhost';
GRANT SELECT, INSERT ON `texaco`.`Tracking` TO 'UAdmin'@'localhost';
GRANT SELECT ON `texaco`.`MonthlySpends` TO 'UAdmin'@'localhost';

# Mail merge Processor account
GRANT USAGE ON `texaco`.* TO 'Letters'@'localhost';
GRANT SELECT ON `texaco`.`Members` TO 'Letters'@'localhost';
GRANT SELECT ON `texaco`.`Accounts` TO 'Letters'@'localhost';
GRANT SELECT ON `texaco`.`Transactions` TO 'Letters'@'localhost';
GRANT SELECT ON `texaco`.`Cards` TO 'Letters'@'localhost';



