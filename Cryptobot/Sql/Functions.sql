DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `getBTCPrice`(`Coin_ID` INT) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare BTC_Price DEC(20,14);
SET BTC_Price = 0;
select `LiveCoinPrice` into BTC_Price From `CoinPrice` where `CoinID` = Coin_ID;
return BTC_Price;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `AvgMaxPrice`(`Coin_ID` INT, `nPct` DECIMAL(20,14)) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare avgPrice DEC(20,14);
Declare avg6Month DEC(20,14);
Declare avg3Month DEC(20,14);
Declare avgAllTime DEC(20,14);
Declare reducePct DEC(20,14);
Set avgPrice = 0.0;
Set avg6Month = 0.0;
Set avg3Month = 0.0;
Set avgAllTime = 0.0;
Set reducePct = 0.0;

SELECT MAX(`MaxPrice`) INTO avg3Month FROM `MonthlyMaxPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 3 MONTH);

SELECT MAX(`MaxPrice`) INTO avg6Month FROM `MonthlyMaxPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 6 MONTH);

SELECT MAX(`MaxPrice`) INTO avgAllTime FROM `MonthlyMaxPrices` WHERE `CoinID` = Coin_ID;

Set avgPrice = ((avg6Month + avg3Month) /2);
Set reducePct = ((avgPrice/100)*nPct);
Return avgPrice-reducePct;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`stevenj1979`@`localhost` FUNCTION `AvgMinPrice`(`Coin_ID` INT, `nPct` DECIMAL(20,14)) RETURNS decimal(20,14)
    READS SQL DATA
BEGIN
Declare avgPrice DEC(20,14);
Declare avg6Month DEC(20,14);
Declare avg3Month DEC(20,14);
Declare avgAllTime DEC(20,14);
Declare addPct DEC(20,14);
Set avgPrice = 0.0;
Set avg6Month = 0.0;
Set avg3Month = 0.0;
Set avgAllTime = 0.0;
Set addPct = 0.0;

SELECT MIN(`MinPrice`) INTO avg3Month FROM `MonthlyMinPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 3 MONTH);

SELECT MIN(`MinPrice`) INTO avg6Month FROM `MonthlyMinPrices` WHERE `CoinID` = Coin_ID and DATE(CONCAT(`Year`,"-",`Month`,"-01")) > date_sub(CURRENT_DATE(),INTERVAL 6 MONTH);

SELECT MIN(`MinPrice`) INTO avgAllTime FROM `MonthlyMinPrices` WHERE `CoinID` = Coin_ID;

Set avgPrice = ((avg6Month+avg3Month)/2);
Set addPct = ((avgPrice/100)*nPct);
Return avgPrice+addPct;

END$$
DELIMITER ;
