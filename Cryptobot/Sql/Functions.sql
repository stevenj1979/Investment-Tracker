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

SELECT `3MonthPrice` INTO avg3Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'High';

SELECT `6MonthPrice` INTO avg6Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'High';

Set avgPrice = ((avg6Month + avg3Month) /2);

Return avgPrice;

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

SELECT `3MonthPrice` INTO avg3Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'Low';

SELECT `6MonthPrice` INTO avg6Month FROM `AvgHighLow` WHERE `CoinID` = Coin_ID and `HighLow` = 'Low';

Set avgPrice = ((avg6Month+avg3Month)/2);

Return avgPrice;

END$$
DELIMITER ;
