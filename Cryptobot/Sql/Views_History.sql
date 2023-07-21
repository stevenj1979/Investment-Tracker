CREATE OR REPLACE VIEW `LastMonthHighPrice` AS
select `Ph`.`CoinID` AS `CoinID`,max(`Ph`.`Price`) AS `MonthHighPrice`,month(`Phd`.`PriceDateTime`) AS `Month`,year(`Phd`.`PriceDateTime`) AS `Year` from `PriceHistory` `Ph` join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID` where (month(`Phd`.`PriceDateTime`) = (month(now()) - 1)) group by `Ph`.`CoinID`;

CREATE OR REPLACE VIEW `CountOfCoinPrice` AS
select count(`Ph`.`Price`) AS `Count of Price`,`Ph`.`Price` AS `Price`,`Ph`.`CoinID` AS `CoinID`
from `PriceHistory` `Ph` join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID`  where (`Phd`.`PriceDateTime` >= ((select max(`PriceHistoryDate`.`PriceDateTime`) from `PriceHistoryDate`) - interval 2 month)) group by `Ph`.`Price` order by `Ph`.`Price` desc;

CREATE OR REPLACE VIEW `CountOfCoinPrice_1Month` AS
select count(`Ph`.`Price`) AS `Count of Price`,`Ph`.`Price` AS `Price`,`Ph`.`CoinID` AS `CoinID` from `PriceHistory` `Ph`  join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID` where (`Phd`.`PriceDateTime` >= ((select max(`PriceHistoryDate`.`PriceDateTime`) from `PriceHistoryDate`) - interval 1 month)) group by `Ph`.`Price` order by `Ph`.`Price` desc;

CREATE OR REPLACE VIEW `CurrentMonthHighPrice` AS
select `Ph`.`CoinID` AS `CoinID`,max(`Ph`.`Price`) AS `MonthHighPrice`,month(`Phd`.`PriceDateTime`) AS `Month`,year(`Phd`.`PriceDateTime`) AS `Year` from `PriceHistory` `Ph`  join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID` where (month(`Phd`.`PriceDateTime`) = month(now())) group by `Ph`.`CoinID`;

CREATE OR REPLACE VIEW `CurrentMonthLowPrice` AS
select `Ph`.`CoinID` AS `CoinID`,min(`Ph`.`Price`) AS `MonthLowPrice`,month(`Phd`.`PriceDateTime`) AS `Month`,year(`Phd`.`PriceDateTime`) AS `Year` from `PriceHistory` `Ph`  join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID` where ((month(`Phd`.`PriceDateTime`) = month(now())) and (`Ph`.`Price` <> 0)) group by `Ph`.`CoinID`;

CREATE OR REPLACE VIEW `FifteenMinsPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`15MinPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `FortyFiveMinsPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`45MinPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `LastMonthLowPrice` AS
select `Ph`.`CoinID` AS `CoinID`,min(`Ph`.`Price`) AS `MonthLowPrice`,month(`Phd`.`PriceDateTime`) AS `Month`,year(`Phd`.`PriceDateTime`) AS `Year` from `PriceHistory` `Ph`  join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID` where ((month(`Phd`.`PriceDateTime`) = (month(now()) - 1)) and (`Ph`.`Price` > 0)) group by `Ph`.`CoinID`;

CREATE OR REPLACE VIEW `LastMonthMinPrice` AS
select `Ph`.`CoinID` AS `CoinID`,max(`Ph`.`Price`) AS `MonthHighPrice`,month(`Phd`.`PriceDateTime`) AS `Month`,year(`Phd`.`PriceDateTime`) AS `Year` from `PriceHistory` `Ph`  join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID` where (month(`Phd`.`PriceDateTime`) = (month(now()) - 1)) group by `Ph`.`CoinID`;

CREATE OR REPLACE VIEW `LivePrice` AS
select `Ph`.`CoinID` AS `CoinID`,avg(`Ph`.`Price`) AS `Price`,max(`Ph`.`Price`) AS `MaxPrice`,min(`Ph`.`Price`) AS `MinPrice`,(select max(`PriceHistoryDate`.`PriceDateTime`) from `PriceHistoryDate`) AS `PriceDate` from `PriceHistory` `Ph`  join `PriceHistoryDate` `Phd` on `Phd`.`ID` = `Ph`.`PriceDateTimeID`
where (`Phd`.`PriceDateTime` = (select max(`PriceHistoryDate`.`PriceDateTime`) from `PriceHistoryDate`))  group by `Ph`.`CoinID` order by `Ph`.`CoinID` desc;

CREATE OR REPLACE VIEW `OneHourPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`1HrPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `OneHourPrice_SpreadBet` AS
select `PPCH`.`CoinID` AS `CoinID`,sum(`PPCH`.`1HrPrice`) AS `Price` from (`PricePctChangeHistory` `PPCH` join `SpreadBetCoins` `Sbc` on((`PPCH`.`CoinID` = `Sbc`.`CoinID`))) group by `Sbc`.`SpreadBetRuleID`;

CREATE OR REPLACE VIEW `SevenDayPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`7DPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `SevenDayPrice_SpreadBet` AS
select `PPCH`.`CoinID` AS `CoinID`,sum(`PPCH`.`7DPrice`) AS `Price` from (`PricePctChangeHistory` `PPCH` join `SpreadBetCoins` `Sbc` on((`PPCH`.`CoinID` = `Sbc`.`CoinID`))) group by `Sbc`.`SpreadBetRuleID`;

CREATE OR REPLACE VIEW `SeventyFiveMinsPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`75MinPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `SpreadBetPriceHistory` AS
select `Sbc`.`SpreadBetRuleID` AS `SpreadBetRuleID`,sum(`Ph`.`Price`) AS `Price`,`Ph`.`PriceDate` AS `PriceDate`,`Ph`.`BaseCurrency` AS `BaseCurrency`,avg(`Ph`.`Hr1Pct`) AS `Hr1Pct`,avg(`Ph`.`Hr24Pct`) AS `Hr24Pct`,avg(`Ph`.`D7Pct`) AS `D7Pct` from (`PriceHistory` `Ph` join `SpreadBetCoins` `Sbc` on((`Sbc`.`CoinID` = `Ph`.`CoinID`))) group by `Sbc`.`SpreadBetRuleID`,year(`Ph`.`PriceDate`),month(`Ph`.`PriceDate`),dayofmonth(`Ph`.`PriceDate`),hour(`Ph`.`PriceDate`),minute(`Ph`.`PriceDate`) order by `Ph`.`PriceDate` desc;

CREATE OR REPLACE VIEW `TenMinsPrice` as
select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price` from `PriceHistory` where ((`PriceHistory`.`PriceDate` < ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 10 minute)) and (`PriceHistory`.`PriceDate` > ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 15 minute))) group by `PriceHistory`.`CoinID` order by `PriceHistory`.`CoinID` desc;

CREATE OR REPLACE VIEW `ThirtyMinsPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`30MinPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `TwentyFourHourPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`24HrPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `TwentyFourHourPrice_SpreadBet` as
select `PPCH`.`CoinID` AS `CoinID`,sum(`PPCH`.`24HrPrice`) AS `Price` from (`PricePctChangeHistory` `PPCH` join `SpreadBetCoins` `Sbc` on((`PPCH`.`CoinID` = `Sbc`.`CoinID`))) group by `Sbc`.`SpreadBetRuleID`;

CREATE OR REPLACE VIEW `TwentyMinsPrice` as
select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price` from `PriceHistory` where ((`PriceHistory`.`PriceDate` < ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 20 minute)) and (`PriceHistory`.`PriceDate` > ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 25 minute))) group by `PriceHistory`.`CoinID` order by `PriceHistory`.`CoinID` desc;
