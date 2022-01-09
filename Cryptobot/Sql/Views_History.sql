CREATE OR REPLACE VIEW `CountOfCoinPrice` AS
select count(`PriceHistory`.`Price`) AS `Count of Price`,`PriceHistory`.`Price` AS `Price`,`PriceHistory`.`CoinID` AS `CoinID`
from `PriceHistory` where (`PriceHistory`.`PriceDate` >= ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 2 month)) group by `PriceHistory`.`Price` order by `PriceHistory`.`Price` desc;

CREATE OR REPLACE VIEW `CountOfCoinPrice_1Month` AS
select count(`PriceHistory`.`Price`) AS `Count of Price`,`PriceHistory`.`Price` AS `Price`,`PriceHistory`.`CoinID` AS `CoinID` from `PriceHistory` where (`PriceHistory`.`PriceDate` >= ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 1 month)) group by `PriceHistory`.`Price` order by `PriceHistory`.`Price` desc;

CREATE OR REPLACE VIEW `CurrentMonthHighPrice` AS
select `PriceHistory`.`CoinID` AS `CoinID`,max(`PriceHistory`.`Price`) AS `MonthHighPrice`,month(`PriceHistory`.`PriceDate`) AS `Month`,year(`PriceHistory`.`PriceDate`) AS `Year` from `PriceHistory` where (month(`PriceHistory`.`PriceDate`) = month(now())) group by `PriceHistory`.`CoinID`;

CREATE OR REPLACE VIEW `CurrentMonthLowPrice` AS
select `PriceHistory`.`CoinID` AS `CoinID`,min(`PriceHistory`.`Price`) AS `MonthLowPrice`,month(`PriceHistory`.`PriceDate`) AS `Month`,year(`PriceHistory`.`PriceDate`) AS `Year` from `PriceHistory` where ((month(`PriceHistory`.`PriceDate`) = month(now())) and (`PriceHistory`.`Price` <> 0)) group by `PriceHistory`.`CoinID`;

CREATE OR REPLACE VIEW `FifteenMinsPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`15MinPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `FortyFiveMinsPrice` AS
select `PricePctChangeHistory`.`CoinID` AS `CoinID`,`PricePctChangeHistory`.`45MinPrice` AS `Price` from `PricePctChangeHistory`;

CREATE OR REPLACE VIEW `LastMonthHighPrice` AS
select `PriceHistory`.`CoinID` AS `CoinID`,max(`PriceHistory`.`Price`) AS `MonthHighPrice`,month(`PriceHistory`.`PriceDate`) AS `Month`,year(`PriceHistory`.`PriceDate`) AS `Year` from `PriceHistory` where (month(`PriceHistory`.`PriceDate`) = (month(now()) - 1)) group by `PriceHistory`.`CoinID`;


CREATE OR REPLACE VIEW `LastMonthLowPrice` AS
select `PriceHistory`.`CoinID` AS `CoinID`,min(`PriceHistory`.`Price`) AS `MonthLowPrice`,month(`PriceHistory`.`PriceDate`) AS `Month`,year(`PriceHistory`.`PriceDate`) AS `Year` from `PriceHistory` where ((month(`PriceHistory`.`PriceDate`) = (month(now()) - 1)) and (`PriceHistory`.`Price` > 0)) group by `PriceHistory`.`CoinID`;

CREATE OR REPLACE VIEW `LastMonthMinPrice` AS
select `PriceHistory`.`CoinID` AS `CoinID`,max(`PriceHistory`.`Price`) AS `MonthHighPrice`,month(`PriceHistory`.`PriceDate`) AS `Month`,year(`PriceHistory`.`PriceDate`) AS `Year` from `PriceHistory` where (month(`PriceHistory`.`PriceDate`) = (month(now()) - 1)) group by `PriceHistory`.`CoinID`;

CREATE OR REPLACE VIEW `LivePrice` AS
select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price`,max(`PriceHistory`.`Price`) AS `MaxPrice`,min(`PriceHistory`.`Price`) AS `MinPrice`,(select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) AS `PriceDate` from `PriceHistory` where ((`PriceHistory`.`PriceDate` < ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 1 minute)) and (`PriceHistory`.`PriceDate` > ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 10 minute))) group by `PriceHistory`.`CoinID` order by `PriceHistory`.`CoinID` desc;

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
